// Uji: input angka tidak boleh menghasilkan nilai minus
const { chromium } = require('playwright-core');
const BASE = 'http://127.0.0.1:8130';
const EXE = process.env.LOCALAPPDATA + '\\ms-playwright\\chromium-1243\\chrome-win64\\chrome.exe';

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: EXE });
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await ctx.newPage();
    const fails = [];
    const t = (name, ok, detail = '') => { console.log(`${ok ? 'PASS' : 'FAIL'} ${name}${detail ? ' — ' + detail : ''}`); if (!ok) fails.push(name); };

    // ===== Menu: harga jual & modal =====
    await page.goto(`${BASE}/admin/menu`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: 'Tambah Menu' }).click();
    await page.waitForTimeout(250);
    await page.locator('input[placeholder="Contoh: Nasi Goreng"]').fill('Test Minus');
    await page.locator('.fixed select').first().selectOption('Makanan');

    const priceInput = page.locator('input[placeholder="15000"]');
    const costInput = page.locator('input[placeholder="8000"]');

    // ketik minus di depan angka
    await priceInput.fill('');
    await priceInput.type('-5000');
    await costInput.fill('');
    await costInput.type('-3000');
    await page.waitForTimeout(300);
    let pv = await priceInput.inputValue();
    let cv = await costInput.inputValue();
    t('Menu: ketik -5000 -> minus diblokir (jadi 5000)', pv === '5000', pv);
    t('Menu: ketik -3000 -> minus diblokir (jadi 3000)', cv === '3000', cv);

    // paste nilai minus
    await priceInput.fill('');
    await priceInput.evaluate(el => {
        el.value = '';
        el.dispatchEvent(new Event('paste'));
        const dt = new DataTransfer(); dt.setData('text', '-4200');
        el.dispatchEvent(new ClipboardEvent('paste', { clipboardData: dt, bubbles: true }));
        el.value = '-4200';
        el.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await page.waitForTimeout(300);
    pv = await priceInput.inputValue();
    t('Menu: paste -4200 -> di-clamp (tidak minus)', !/^-/.test(pv), pv);

    // simpan: verifikasi data tersimpan tanpa minus
    await priceInput.fill('12000');
    await page.getByRole('button', { name: 'Simpan', exact: true }).click();
    await page.waitForTimeout(300);
    const rowText = await page.locator('tr', { hasText: 'Test Minus' }).first().innerText().catch(() => 'TIDAK ADA');
    t('Menu: simpan sukses, angka positif', /12\.000/.test(rowText) && !/−Rp\s*−|-Rp\s*-/.test(rowText), rowText.replace(/\s+/g, ' ').slice(0, 90));

    // ===== Pengeluaran =====
    await page.goto(`${BASE}/admin/pengeluaran`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: 'Catat Pengeluaran' }).click();
    await page.waitForTimeout(250);
    await page.locator('input[placeholder="Contoh: Pembelian gas elpiji"]').fill('Test Minus Pengeluaran');
    await page.locator('.fixed select').first().selectOption('Operasional');
    const amtInput = page.locator('input[placeholder="100000"]');
    await amtInput.fill('');
    await amtInput.type('-250000');
    await page.waitForTimeout(300);
    const av = await amtInput.inputValue();
    t('Pengeluaran: ketik -250000 -> minus diblokir (jadi 250000)', av === '250000', av);
    await page.getByRole('button', { name: 'Simpan', exact: true }).click();
    await page.waitForTimeout(300);
    const expRow = await page.locator('tr', { hasText: 'Test Minus Pengeluaran' }).first().innerText().catch(() => 'TIDAK ADA');
    t('Pengeluaran: tersimpan positif 250.000', /−Rp\s*250\.000/.test(expRow) && !/−Rp\s*−250|−250\.000/.test(expRow), expRow.replace(/\s+/g, ' ').slice(0, 90));

    // paste minus ke pengeluaran
    await page.getByRole('button', { name: 'Catat Pengeluaran' }).click();
    await page.waitForTimeout(250);
    await page.locator('input[placeholder="Contoh: Pembelian gas elpiji"]').fill('Test Paste Minus');
    await page.locator('.fixed select').first().selectOption('Lainnya');
    await amtInput.evaluate(el => {
        el.value = '-99000';
        el.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await page.waitForTimeout(300);
    const av2 = await amtInput.inputValue();
    t('Pengeluaran: paste -99000 -> di-clamp ke 0 (tidak minus)', !/^-/.test(av2), av2);
    await page.getByRole('button', { name: 'Batal' }).click();

    // ===== POS: uang diterima =====
    await page.goto(`${BASE}/kasir/transaksi`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: /Nasi Goreng/ }).first().click();
    await page.waitForTimeout(200);
    const cashInput = page.locator('input[type="number"]');
    await cashInput.fill('');
    await cashInput.type('-100000');
    await page.waitForTimeout(300);
    const cash = await cashInput.inputValue();
    t('POS: ketik -100000 -> minus diblokir (jadi 100000)', cash === '100000', cash);
    // kembalian valid
    const k = await page.locator('text=Kembalian').locator('..').locator('span').last().innerText().catch(() => '');
    t('POS: kembalian terhitung normal', /85\.000/.test(k), k);
    // paste minus
    await cashInput.evaluate(el => {
        el.value = '-50000';
        el.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await page.waitForTimeout(300);
    const cash2 = await cashInput.inputValue();
    t('POS: paste -50000 -> di-clamp ke 0', cash2 === '0', cash2);

    await ctx.close();
    await browser.close();
    console.log(fails.length === 0 ? '\n=== SEMUA UJI MINUS LULUS ===' : `\n=== ${fails.length} GAGAL: ${fails.join(', ')} ===`);
})();
