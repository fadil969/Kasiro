// Uji interaksi: POS add-to-cart, modal menu, filter riwayat
const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8130';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await ctx.newPage();
    const fails = [];
    const t = (name, ok, detail = '') => { console.log(`${ok ? 'PASS' : 'FAIL'} ${name}${detail ? ' — ' + detail : ''}`); if (!ok) fails.push(name); };

    // Identifikasi 1 shadow yang konsisten
    await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'networkidle' });
    const shadowEl = await page.evaluate(() => {
        let out = null;
        document.querySelectorAll('body *').forEach(el => {
            const bs = getComputedStyle(el).boxShadow;
            if (bs && bs !== 'none' && !out) out = el.tagName + '.' + (el.className || '').toString().slice(0, 60);
        });
        return out;
    });
    console.log('INFO shadow tunggal berasal dari:', shadowEl);

    // ===== POS: tambah item, qty, total =====
    await page.goto(`${BASE}/kasir/transaksi`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: /Nasi Goreng/ }).first().click();
    await page.waitForTimeout(250);
    const totalAfterOne = await page.locator('span.font-mono.text-2xl').innerText();
    t('POS: klik menu -> nota terisi', /15\.000/.test(totalAfterOne), 'total=' + totalAfterOne);

    await page.getByRole('button', { name: /Es Teh/ }).first().click();
    await page.waitForTimeout(200);
    const totalAfterTwo = await page.locator('span.font-mono.text-2xl').innerText();
    t('POS: item kedua masuk', /20\.000/.test(totalAfterTwo), 'total=' + totalAfterTwo);

    // Kembalian
    await page.locator('input[type="number"]').fill('50000');
    await page.waitForTimeout(200);
    const kembalian = await page.locator('text=Kembalian').locator('..').locator('span').last().innerText();
    t('POS: kembalian dihitung', /30\.000/.test(kembalian), 'kembalian=' + kembalian);

    // Bayar -> modal sukses
    await page.getByRole('button', { name: 'Bayar Sekarang' }).click();
    await page.waitForTimeout(300);
    const modalVisible = await page.locator('text=Transaksi tercatat').isVisible();
    t('POS: modal sukses muncul', modalVisible);
    await page.getByRole('button', { name: 'Nota Baru' }).click();
    await page.waitForTimeout(250);
    const totalReset = await page.locator('span.font-mono.text-2xl').innerText();
    t('POS: nota baru mengosongkan keranjang', /Rp 0/.test(totalReset), 'total=' + totalReset);

    // Shortcut "/" fokus search
    await page.keyboard.press('/');
    const focused = await page.evaluate(() => document.activeElement && document.activeElement.tagName === 'INPUT');
    t('POS: shortcut "/" fokus ke pencarian', focused);

    // ===== Menu: buka modal tambah, simpan =====
    await page.goto(`${BASE}/admin/menu`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: 'Tambah Menu' }).click();
    await page.waitForTimeout(250);
    t('Menu: modal tambah muncul', await page.locator('text=Nama Menu').first().isVisible());
    await page.locator('input[placeholder="Contoh: Nasi Goreng"]').fill('Mie Ayam Bakso');
    await page.locator('.fixed select').first().selectOption('Makanan');
    await page.locator('input[placeholder="15000"]').fill('12000');
    await page.getByRole('button', { name: 'Simpan', exact: true }).click();
    await page.waitForTimeout(300);
    t('Menu: item baru masuk tabel', await page.locator('text=Mie Ayam Bakso').first().isVisible());

    // ===== Riwayat: filter pencarian =====
    await page.goto(`${BASE}/admin/riwayat`, { waitUntil: 'networkidle' });
    await page.locator('input[placeholder="Cari ID / kasir…"]').fill('Budi');
    await page.waitForTimeout(300);
    const rows = await page.locator('tbody tr').count();
    t('Riwayat: filter kasir bekerja', rows === 5, rows + ' baris (harus 5)');

    await browser.close();
    console.log(fails.length === 0 ? '\n=== SEMUA UJI INTERAKSI LULUS ===' : `\n=== ${fails.length} GAGAL: ${fails.join(', ')} ===`);
})();
