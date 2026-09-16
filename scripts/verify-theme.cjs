// Verifikasi tema: light/dark values, toggle, regresi interaksi
const { chromium } = require('playwright-core');
const BASE = 'http://127.0.0.1:8130';
const EXE = process.env.LOCALAPPDATA + '\\ms-playwright\\chromium-1243\\chrome-win64\\chrome.exe';

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: EXE });
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await ctx.newPage();
    const fails = [];
    const t = (name, ok, detail = '') => { console.log(`${ok ? 'PASS' : 'FAIL'} ${name}${detail ? ' — ' + detail : ''}`); if (!ok) fails.push(name); };

    await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(400);

    // LIGHT default
    let s = await page.evaluate(() => ({
        body: getComputedStyle(document.body).backgroundColor,
        card: getComputedStyle(document.querySelector('.bg-paper')).backgroundColor,
        btn: getComputedStyle(document.querySelector('.bg-primary-600')).backgroundColor,
        sidebar: getComputedStyle(document.querySelector('aside')).backgroundColor,
        htmlDark: document.documentElement.classList.contains('dark'),
    }));
    t('Light: bg #F8FAFC', s.body === 'rgb(248, 250, 252)', s.body);
    t('Light: card #FFFFFF', s.card === 'rgb(255, 255, 255)', s.card);
    t('Light: primary #2563EB', s.btn === 'rgb(37, 99, 235)', s.btn);
    t('Light: sidebar putih', s.sidebar === 'rgb(255, 255, 255)', s.sidebar);
    t('Light: html.tidak-dark', !s.htmlDark);

    // Toggle -> DARK
    await page.locator('button[title="Ganti tema"]').click();
    await page.waitForTimeout(400);
    s = await page.evaluate(() => ({
        body: getComputedStyle(document.body).backgroundColor,
        card: getComputedStyle(document.querySelector('.bg-paper')).backgroundColor,
        btn: getComputedStyle(document.querySelector('.bg-primary-600')).backgroundColor,
        sidebar: getComputedStyle(document.querySelector('aside')).backgroundColor,
        ink: getComputedStyle(document.body).color,
        htmlDark: document.documentElement.classList.contains('dark'),
        stored: localStorage.getItem('kasiro-theme'),
    }));
    t('Dark: bg #0F172A', s.body === 'rgb(15, 23, 42)', s.body);
    t('Dark: card #1E293B', s.card === 'rgb(30, 41, 59)', s.card);
    t('Dark: primary #3B82F6', s.btn === 'rgb(59, 130, 246)', s.btn);
    t('Dark: sidebar #1E293B', s.sidebar === 'rgb(30, 41, 59)', s.sidebar);
    t('Dark: teks #F8FAFC', s.ink === 'rgb(248, 250, 252)', s.ink);
    t('Dark: tersimpan di localStorage', s.stored === 'dark' && s.htmlDark);

    // Toggle balik -> LIGHT
    await page.locator('button[title="Ganti tema"]').click();
    await page.waitForTimeout(400);
    s = await page.evaluate(() => ({
        body: getComputedStyle(document.body).backgroundColor,
        stored: localStorage.getItem('kasiro-theme'),
    }));
    t('Balik ke Light', s.body === 'rgb(248, 250, 252)' && s.stored === 'light', JSON.stringify(s));

    // Dark persist setelah reload
    await page.locator('button[title="Ganti tema"]').click(); // -> dark
    await page.waitForTimeout(300);
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(400);
    s = await page.evaluate(() => ({
        body: getComputedStyle(document.body).backgroundColor,
    }));
    t('Dark persist setelah reload', s.body === 'rgb(15, 23, 42)', s.body);

    // Cek konsistensi dark di halaman lain (masih dark)
    await page.goto(`${BASE}/kasir/transaksi`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(400);
    s = await page.evaluate(() => ({
        body: getComputedStyle(document.body).backgroundColor,
        htmlDark: document.documentElement.classList.contains('dark'),
    }));
    t('Dark konsisten di POS', s.body === 'rgb(15, 23, 42)' && s.htmlDark, s.body);
    await page.locator('button[title="Ganti tema"]').click(); // balik light utk kebersihan
    await page.waitForTimeout(300);

    await ctx.close();
    await browser.close();
    console.log(fails.length === 0 ? '\n=== SEMUA UJI TEMA LULUS ===' : `\n=== ${fails.length} GAGAL: ${fails.join(', ')} ===`);
})();
