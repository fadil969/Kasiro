/**
 * Kasiro — Custom Select Enhancer
 * Mengubah seluruh <select> native menjadi dropdown bergaya Kasiro
 * (paper/rule/primary, DM Sans, dark-mode aware) TANPA mengubah perilaku:
 * - nilai tetap dikirim lewat form (select native disembunyikan, bukan dihapus)
 * - event input/change di-dispatch → onchange="this.form.submit()" & x-model tetap jalan
 * - panel daftar dipindah ke <body> (fixed) agar tidak terpotong modal/tabel
 * - polling ringan 250ms menyinkron UI saat nilai berubah dari Alpine (modal edit)
 */
(function () {
    'use strict';

    var CSS = [
        '.ks-wrap{position:relative;display:inline-block;vertical-align:middle}',
        '.ks-wrap.ks-block{display:block;width:100%}',
        '.ks-native{position:absolute!important;width:1px;height:1px;opacity:0;pointer-events:none;overflow:hidden;clip-path:inset(50%);border:0}',
        '.ks-btn{display:inline-flex;align-items:center;justify-content:space-between;gap:.5rem;cursor:pointer;user-select:none;font:inherit;white-space:nowrap;max-width:100%}',
        '.ks-btn:disabled{opacity:.55;cursor:not-allowed}',
        '.ks-btn .ks-label{overflow:hidden;text-overflow:ellipsis;flex:1 1 auto;min-width:0;text-align:left}',
        '.ks-btn.ks-ph .ks-label{color:rgb(var(--n-inkmuted)/.85)}',
        '.ks-btn .ks-chev{flex:none;width:1rem;height:1rem;color:rgb(var(--n-inkmuted));transition:transform .15s ease}',
        '.ks-wrap.ks-on .ks-chev{transform:rotate(180deg)}',
        '.ks-menu{position:fixed;z-index:9999;box-sizing:border-box;',
        'background:rgb(var(--n-paper));border:1px solid rgb(var(--n-rule));border-radius:.75rem;padding:.25rem;',
        'box-shadow:0 8px 24px -8px rgb(var(--n-shadow)/.35);max-height:15rem;overflow:auto;',
        'opacity:0;transform:scale(.97) translateY(-2px);transform-origin:top left;',
        'pointer-events:none;transition:opacity .12s ease,transform .12s ease}',
        '.ks-menu.ks-open{opacity:1;transform:none;pointer-events:auto}',
        '.ks-menu::-webkit-scrollbar{width:8px}',
        '.ks-menu::-webkit-scrollbar-thumb{background:rgb(var(--n-rule));border-radius:9999px}',
        '.ks-opt{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.5rem .75rem;',
        'border-radius:.5rem;font-size:.8125rem;line-height:1.25rem;color:rgb(var(--n-inksoft));cursor:pointer;white-space:nowrap}',
        '.ks-opt:hover,.ks-opt.ks-active{background:rgb(var(--n-cream2));color:rgb(var(--n-ink))}',
        '.ks-opt.ks-sel{background:rgb(var(--p-50));color:rgb(var(--p-700));font-weight:600}',
        '.ks-opt.ks-sel:hover,.ks-opt.ks-sel.ks-active{background:rgb(var(--p-100));color:rgb(var(--p-700))}',
        '.ks-opt .ks-check{flex:none;width:.875rem;height:.875rem;opacity:0}',
        '.ks-opt.ks-sel .ks-check{opacity:1}'
    ].join('\n');

    var style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    var current = null; // { wrap, btn, menu, close }

    function closeMenu() {
        if (!current) return;
        current.close();
        current = null;
    }

    document.addEventListener('pointerdown', function (e) {
        if (current && !current.wrap.contains(e.target) && !current.menu.contains(e.target)) closeMenu();
    }, true);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && current) {
            var btn = current.btn;
            closeMenu();
            if (btn) btn.focus();
        }
    }, true);
    window.addEventListener('resize', closeMenu);
    window.addEventListener('scroll', function (e) {
        // Biarkan scroll di dalam daftar opsi sendiri; tutup saat halaman/scroller induk bergerak
        if (current && current.menu && e.target && current.menu.contains(e.target)) return;
        closeMenu();
    }, true);

    function enhance(select) {
        if (select.dataset.ksDone) return;
        select.dataset.ksDone = '1';

        var wrap = document.createElement('div');
        wrap.className = 'ks-wrap' + (/(^|\s)w-full(\s|$)/.test(select.className) ? ' ks-block' : '');
        // Simpan kelas tampilan SEBELUM ks-native dipasang —
        // jika tidak, tombol dropdown ikut tersembunyi (opacity:0; pointer-events:none)
        var lookClass = select.className;
        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        select.classList.add('ks-native');

        // Tombol pewaris kelas tampilan select asli → ukuran & focus ring identik
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ks-btn ' + lookClass;
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        if (select.disabled) btn.disabled = true;
        btn.innerHTML = '<span class="ks-label"></span>' +
            '<svg class="ks-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>';
        wrap.appendChild(btn);
        var label = btn.querySelector('.ks-label');

        var optEls = [];
        Array.prototype.forEach.call(select.options, function (o, i) {
            var d = document.createElement('div');
            d.className = 'ks-opt';
            d.setAttribute('role', 'option');
            d.innerHTML = '<span></span><svg class="ks-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
                '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
            d.firstChild.textContent = o.text;
            d.addEventListener('click', function (e) { e.stopPropagation(); choose(i); });
            optEls.push(d);
        });

        var menu = null;
        var activeIdx = -1;

        function paint() {
            var i = select.selectedIndex;
            var v = i >= 0 ? select.options[i].text : '';
            if (label.textContent !== v) label.textContent = v || '\u00a0';
            btn.classList.toggle('ks-ph', i >= 0 && select.options[i].value === '');
            optEls.forEach(function (d, k) {
                var sel = k === i;
                d.classList.toggle('ks-sel', sel);
                d.setAttribute('aria-selected', sel ? 'true' : 'false');
            });
        }

        function choose(i) {
            if (select.selectedIndex !== i) {
                select.selectedIndex = i;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            paint();
            closeMenu();
            btn.focus();
        }

        function setActive(i) {
            optEls.forEach(function (d, k) { d.classList.toggle('ks-active', k === i); });
            activeIdx = i;
            if (optEls[i]) optEls[i].scrollIntoView({ block: 'nearest' });
        }

        function position() {
            var r = btn.getBoundingClientRect();
            menu.style.left = r.left + 'px';
            menu.style.minWidth = r.width + 'px';
            var h = menu.offsetHeight;
            var top = r.bottom + 4;
            if (top + h > window.innerHeight - 8 && r.top - h - 4 > 8) top = Math.max(8, r.top - h - 4);
            menu.style.top = top + 'px';
        }

        function open() {
            closeMenu();
            menu = document.createElement('div');
            menu.className = 'ks-menu';
            menu.setAttribute('role', 'listbox');
            optEls.forEach(function (d) { menu.appendChild(d); });
            document.body.appendChild(menu);
            position();
            menu.classList.add('ks-open');
            wrap.classList.add('ks-on');
            btn.setAttribute('aria-expanded', 'true');
            current = {
                wrap: wrap, btn: btn, menu: menu,
                close: function () {
                    menu.classList.remove('ks-open');
                    var m = menu;
                    setTimeout(function () { if (m.parentNode && !m.classList.contains('ks-open')) m.remove(); }, 120);
                    wrap.classList.remove('ks-on');
                    btn.setAttribute('aria-expanded', 'false');
                }
            };
            setActive(select.selectedIndex >= 0 ? select.selectedIndex : 0);
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (current && current.wrap === wrap) { closeMenu(); } else { open(); }
        });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (!(current && current.wrap === wrap)) { open(); }
                else if (e.key === 'ArrowDown') { setActive(Math.min(activeIdx + 1, optEls.length - 1)); }
                else if (e.key === 'ArrowUp') { setActive(Math.max(activeIdx - 1, 0)); }
                else if (activeIdx >= 0) { choose(activeIdx); }
            }
        });
        select.addEventListener('change', paint);
        paint();

        // Sinkron saat nilai diubah programatik (x-model saat buka modal edit / reset form)
        var lastVal, lastIdx;
        setInterval(function () {
            if (select.value !== lastVal || select.selectedIndex !== lastIdx) {
                lastVal = select.value;
                lastIdx = select.selectedIndex;
                paint();
            }
        }, 250);
    }

    function enhanceAll() {
        document.querySelectorAll('select:not([data-ks-done])').forEach(enhance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceAll);
    } else {
        enhanceAll();
    }
    // Backstop: elemen select yang dirender dinamis belakangan
    new MutationObserver(function (muts) {
        muts.forEach(function (m) {
            Array.prototype.forEach.call(m.addedNodes, function (n) {
                if (n.nodeType !== 1) return;
                if (n.tagName === 'SELECT') enhance(n);
                if (n.querySelectorAll) n.querySelectorAll('select').forEach(enhance);
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
