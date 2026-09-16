// Verifikasi headless KASIRO — audit token, mono, shadow, overflow, konsol error
// Jalankan: node scripts/verify-ui.cjs
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE = 'http://127.0.0.1:8130';
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
    document.querySelectorAll('*').forEach(el => {
        const cls = el.getAttribute('class') || '';
        const m = cls.match(re);
        if (m) badTokens.push(m[0]);
    });
    let shadowCount = 0;
    document.querySelectorAll('body *').forEach(el => {
        const bs = getComputedStyle(el).boxShadow;
        if (bs && bs !== 'none') shadowCount++;
    });
    const bodyFont = getComputedStyle(document.body).fontFamily;
    const monoEls = document.querySelectorAll('.font-mono').length;
    const overflowX = document.documentElement.scrollWidth - document.documentElement.clientWidth;
    let sidebarBg = null;
    const aside = document.querySelector('aside');
    if (aside) sidebarBg = getComputedStyle(aside).backgroundColor;
    return { badTokens: [...new Set(badTokens)], shadowCount, bodyFont, monoEls, overflowX, sidebarBg };
};

(async () => {
    const browser = await chromium.launch({ headless: true });
    const shotsDir = path.join(__dirname, '..', 'storage', 'app', 'ui-audit');
    fs.mkdirSync(shotsDir, { recursive: true });
    const report = {};
    let consoleErrors = [];

    for (const vp of VIEWPORTS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(`${vp.name}:${page.url()} :: ${msg.text().slice(0, 200)}`); });
        page.on('pageerror', err => consoleErrors.push(`${vp.name}:${page.url()} :: PAGEERROR ${String(err).slice(0, 200)}`));

        for (const route of routes) {
            try {
                const resp = await page.goto(`${BASE}/${route}`, { waitUntil: 'networkidle', timeout: 30000 });
                await page.waitForTimeout(400);
                const audit = await page.evaluate(AUDIT_JS);
                await page.screenshot({ path: path.join(shotsDir, `${vp.name}-${route.replace(/\//g, '_')}.png`), fullPage: false });
                report[`${vp.name}/${route}`] = { status: resp.status(), ...audit };
            } catch (e) {
                report[`${vp.name}/${route}`] = { status: 'NAV_FAIL', error: String(e).slice(0, 160) };
            }
        }
        await ctx.close();
    }

    await browser.close();

    // Ringkasan
    let problems = 0;
    for (const [key, r] of Object.entries(report)) {
        const flags = [];
        if (r.status !== 200) flags.push('status!=' + r.status);
        if (r.badTokens && r.badTokens.length) flags.push('token-default:' + r.badTokens.join(','));
        if (r.shadowCount > 24) flags.push('shadow=' + r.shadowCount);
        if (r.overflowX > 1) flags.push('overflowX=' + r.overflowX);
        if (flags.length) { problems++; console.log(`FLAG ${key}: ${flags.join(' | ')}`); }
        else console.log(`OK   ${key}: status=200 shadow=${r.shadowCount} mono=${r.monoEls} overflowX=${r.overflowX}`);
    }
    console.log(`\n=== ${problems === 0 ? 'SEMUA BERSIH' : problems + ' halaman ber-flag'} ===`);
    if (consoleErrors.length) {
        console.log('Console errors:');
        consoleErrors.slice(0, 10).forEach(e => console.log('  ' + e));
    } else {
        console.log('Console errors: tidak ada');
    }
    fs.writeFileSync(path.join(shotsDir, 'report.json'), JSON.stringify(report, null, 2));
})();
