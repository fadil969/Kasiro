const { chromium } = require('playwright');
(async () => {
    const b = await chromium.launch();
    const p = await (await b.newContext({ viewport: { width: 1100, height: 700 } })).newPage();
    await p.goto('http://127.0.0.1:8130/admin/dashboard', { waitUntil: 'networkidle' });
    await p.waitForTimeout(400);
    await p.screenshot({ path: 'storage/app/ui-audit/dash-small.jpg', quality: 60, type: 'jpeg' });
    await b.close();
    console.log('OK');
})();
