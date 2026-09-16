// Verifikasi interaksi kasiro — playwright-core + chromium cache lokal
const { chromium } = require('playwright-core');
const path = require('path');
const fs = require('fs');

const BASE = 'http://127.0.0.1:8130';
const EXE = process.env.LOCALAPPDATA + '\\ms-playwright\\chromium-1243\\chrome-win64\\chrome.exe';
const routes = [
    'login',
    'admin/dashboard', 'admin/menu', 'admin/kategori',
    'admin/pengeluaran', 'admin/laporan', 'admin/riwayat', 'admin/kasir',
    'kasir/transaksi', 'kasir/riwayat', 'profile',
];
const VIEWPORTS = [
    { name: 'desktop', width: 1440, height: 900 },
    { name: 'mobile', width: 390, height: 844 },
];

const AUDIT_JS = () => {
    const re = /\b(blue|slate|emerald|red|amber|purple|gray|indigo|violet|teal|cyan|rose|orange|lime|green|yellow)-(50|100|200|300|400|500|600|700|800|900|950)\b/;
    const badTokens = [];
    document.querySelectorAll('[class]').forEach(el => {
        const m = (el.getAttribute('class') || '').match(re);
        if (m) badTokens.push(m[0]);
    });
    let shadowCount = 0;
    document.querySelectorAll('body *').forEach(el => {
        const bs = getComputedStyle(el).boxShadow;
        if (bs && bs !== 'none' && bs.replace(/0 0 #0000/g, '').trim()) shadowCount++;
    });
    const overflowX = document.documentElement.scrollWidth - document.documentElement.clientWidth;
    const bodyBg = getComputedStyle(document.body).backgroundColor;
    const aside = document.querySelector('aside');
    const sidebarBg = aside ? getComputedStyle(aside).backgroundColor : null;
    const text = document.body.innerText;
    const utang = /utang|piutang|cicilan|\bbon\b/i.test(text);
    return { badTokens: [...new Set(badTokens)], shadowCount, overflowX, bodyBg, sidebarBg, utang };
};

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: EXE });
    const shotsDir = path.join(__dirname, '..', 'storage', 'app', 'ui-audit2');
    fs.mkdirSync(shotsDir, { recursive: true });
    const report = {};
    const consoleErrors = [];
    let flags = 0;

    for (const vp of VIEWPORTS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(`${vp.name} ${page.url()} :: ${msg.text().slice(0, 140)}`); });
        page.on('pageerror', err => consoleErrors.push(`${vp.name} ${page.url()} :: PAGEERROR ${String(err).slice(0, 140)}`));

        for (const route of routes) {
            try {
                const resp = await page.goto(`${BASE}/${route}`, { waitUntil: 'networkidle', timeout: 30000 });
                await page.waitForTimeout(350);
                const audit = await page.evaluate(AUDIT_JS);
                await page.screenshot({ path: path.join(shotsDir, `${vp.name}-${route.replace(/\//g, '_')}.png`) });
                report[`${vp.name}/${route}`] = { status: resp.status(), ...audit };
                const bad = (resp.status() !== 200) || audit.badTokens.length || audit.utang || audit.overflowX > 1;
                if (bad) {
                    flags++;
                    console.log(`FLAG ${vp.name}/${route}: status=${resp.status()} tokens=${audit.badTokens.join(',')} utang=${audit.utang} overflowX=${audit.overflowX}`);
                }
            } catch (e) {
                flags++;
                report[`${vp.name}/${route}`] = { status: 'NAV_FAIL', error: String(e).slice(0, 140) };
                console.log(`FLAG ${vp.name}/${route}: NAV_FAIL ${String(e).slice(0, 80)}`);
            }
        }
        await ctx.close();
    }

    // ===== Interaksi =====
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await ctx.newPage();
    const fails = [];
    const t = (name, ok, detail = '') => { console.log(`${ok ? 'PASS' : 'FAIL'} ${name}${detail ? ' — ' + detail : ''}`); if (!ok) fails.push(name); };

    // POS penuh
    await page.goto(`${BASE}/kasir/transaksi`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: /Nasi Goreng/ }).first().click();
    await page.waitForTimeout(250);
    let v = await page.locator('span.font-mono.text-2xl').innerText();
    t('POS: klik menu -> total', /15\.000/.test(v), 'total=' + v);
    await page.locator('input[type="number"]').fill('50000');
    await page.waitForTimeout(200);
    const k = await page.locator('text=Kembalian').locator('..').locator('span').last().innerText();
    t('POS: kembalian', /35\.000/.test(k), k);
    await page.getByRole('button', { name: 'Bayar Sekarang' }).click();
    await page.waitForTimeout(300);
    t('POS: modal sukses', await page.locator('text=Transaksi tercatat').isVisible());
    await page.getByRole('button', { name: 'Nota Baru' }).click();
    await page.waitForTimeout(250);
    v = await page.locator('span.font-mono.text-2xl').innerText();
    t('POS: nota baru reset', /Rp 0/.test(v), v);
    await page.keyboard.press('/');
    t('POS: shortcut /', await page.evaluate(() => document.activeElement && document.activeElement.tagName === 'INPUT'));

    // Non Tunai metode tersedia, utang tidak ada
    const methods = await page.locator('button:has-text("Non Tunai")').count();
    t('POS: metode Non Tunai ada', methods > 0);
    t('POS: tidak ada tombol Utang', (await page.locator('button:has-text("Utang")').count()) === 0);

    // Menu add
    await page.goto(`${BASE}/admin/menu`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: 'Tambah Menu' }).click();
    await page.waitForTimeout(250);
    await page.locator('input[placeholder="Contoh: Nasi Goreng"]').fill('Mie Ayam Bakso');
    await page.locator('.fixed select').first().selectOption('Makanan');
    await page.locator('input[placeholder="15000"]').fill('12000');
    await page.getByRole('button', { name: 'Simpan', exact: true }).click();
    await page.waitForTimeout(300);
    t('Menu: tambah item', await page.locator('text=Mie Ayam Bakso').first().isVisible());

    // Riwayat filter
    await page.goto(`${BASE}/admin/riwayat`, { waitUntil: 'networkidle' });
    await page.locator('input[placeholder="Cari ID / kasir…"]').fill('Budi');
    await page.waitForTimeout(300);
    const rows = await page.locator('tbody tr').count();
    t('Riwayat: filter', rows === 5, rows + ' baris (harus 5)');

    // Sidebar admin: tidak ada link utang
    await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'networkidle' });
    const sidebarUtang = await page.locator('aside a:has-text("Utang")').count();
    t('Sidebar admin: tanpa Utang/Bon', sidebarUtang === 0);
    const adminLinks = await page.locator('aside a').count();
    t('Sidebar admin: 7 nav + keluar = 8 link', adminLinks === 8, adminLinks + ' link');
    const rwayatAdmin = await page.locator('aside a[href="/admin/riwayat"]').count();
    t('Sidebar admin: link Riwayat ada', rwayatAdmin === 1);
    const heroTrx = await page.locator('main a[href="/kasir/transaksi"]').count();
    t('Dashboard: quick action Transaksi Baru ada', heroTrx === 1);

    // Sidebar kasir
    await page.goto(`${BASE}/kasir/riwayat`, { waitUntil: 'networkidle' });
    const kasirUtang = await page.locator('aside a:has-text("Utang")').count();
    t('Sidebar kasir: tanpa Utang/Bon', kasirUtang === 0);
    const rwayatLink = await page.locator('aside a[href="/kasir/riwayat"]').count();
    t('Sidebar kasir: Riwayat Saya ada', rwayatLink === 1);

    // Login CTA color (indigo-600 #4F46E5)
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
    const btnColor = await page.evaluate(() => {
        const btn = document.querySelector('button[type="submit"]');
        return btn ? getComputedStyle(btn).backgroundColor : null;
    });
    t('Login: CTA primary-600 (#2563EB)', btnColor === 'rgb(37, 99, 235)', btnColor);
    await page.fill('#username', 'admin');
    await page.fill('#password', 'rahasia');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await page.waitForTimeout(400);
    t('Login: submit -> dashboard', page.url().includes('/admin/dashboard'), page.url());

    await ctx.close();
    await browser.close();

    console.log(`\n=== AUDIT: ${flags === 0 ? 'SEMUA BERSIH (24 titik)' : flags + ' flag'} ===`);
    console.log(`=== INTERAKSI: ${fails.length === 0 ? 'SEMUA LULUS' : fails.length + ' gagal: ' + fails.join(', ')} ===`);
    console.log('Console errors:', consoleErrors.length ? '' : 'tidak ada');
    consoleErrors.slice(0, 6).forEach(e => console.log('  ' + e));
    fs.writeFileSync(path.join(shotsDir, 'report.json'), JSON.stringify(report, null, 2));
})();
