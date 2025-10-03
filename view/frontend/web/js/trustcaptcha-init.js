(function () {
    var TAG_NAME = 'trustcaptcha-component';
    var TOKEN_INPUT_NAME = 'tc-verification-token';
    var WIDGET_SCRIPT_URL = 'https://cdn.trustcomponent.com/trustcaptcha/2.0.x/trustcaptcha.umd.min.js';

    function ready(fn) { if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
    function onPageShow(fn) { window.addEventListener('pageshow', fn); }
    function attr(el, n, v) { if (v === undefined || v === null || v === '') return; if (v === true) { el.setAttribute(n, ''); return; } el.setAttribute(n, String(v)); }

    function patchShadow(el) {
        try {
            if (!el || !el.shadowRoot || el.__tcPatched) return;
            el.__tcPatched = true;
            var style = document.createElement('style');
            style.textContent =
                'svg,img,canvas,video{max-width:100%;max-height:100%;height:auto;display:block}' +
                '.w-6.h-6,.w-8.h-8,.w-10.h-10{overflow:hidden}' +
                '.w-6.h-6>* , .w-8.h-8>* , .w-10.h-10>* {width:100%;height:100%}';
            el.shadowRoot.appendChild(style);
            var mo = new MutationObserver(function () { compensateFlow(el); });
            mo.observe(el.shadowRoot, { childList: true, subtree: true });
        } catch (e) {}
    }
    function patchWhenReady(el) {
        if (el.shadowRoot) { patchShadow(el); return; }
        var obs = new MutationObserver(function () {
            if (el.shadowRoot) { try { patchShadow(el); obs.disconnect(); } catch (e) {} }
        });
        obs.observe(el, { attributes: true, attributeFilter: ['class'] });
    }

    function scaleTo16px(el) {
        try {
            var root = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
            var f = 16 / root;
            if (f < 0.8) f = 1;
            el.style.setProperty('--tc-scale', f);
            el.classList.add('tc-scale');
        } catch (e) {}
    }
    function compensateFlow(el) {
        try {
            var scale = parseFloat(getComputedStyle(el).getPropertyValue('--tc-scale')) || 1;
            if (scale === 1) { el.style.marginBottom = ''; return; }
            var visual = el.getBoundingClientRect().height;
            var flow = el.offsetHeight;
            var extra = Math.max(0, Math.round(visual - flow));
            el.style.marginBottom = extra + 'px';
        } catch (e) {}
    }

    function initExisting() {
        try {
            document.querySelectorAll(TAG_NAME).forEach(function (el) {
                scaleTo16px(el);
                patchWhenReady(el);
                requestAnimationFrame(function () { compensateFlow(el); });
            });
        } catch (e) {}
    }

    function findSubmitAnchor(form) {
        var toolbar = form.querySelector('.actions-toolbar'); if (toolbar) return { node: toolbar, place: 'before' };
        var btn = form.querySelector('button[type="submit"], .action.submit, .action.primary'); if (btn) return { node: btn, place: 'before' };
        var last = form.querySelector('.field:last-child, fieldset:last-child'); if (last) return { node: last, place: 'after' };
        return { node: form, place: 'append' };
    }
    function insertAfter(node, newEl) { if (!node || !node.parentNode) return; if (node.nextSibling) node.parentNode.insertBefore(newEl, node.nextSibling); else node.parentNode.appendChild(newEl); }

    function insertWidgetIntoForm(form, cfg) {
        if (!form || form.querySelector(TAG_NAME)) return false;
        var el = document.createElement(TAG_NAME);

        attr(el, 'sitekey', cfg.siteKey);
        attr(el, 'license', cfg.licenseKey);
        attr(el, 'width', cfg.width);
        attr(el, 'language', cfg.language);
        attr(el, 'theme', cfg.theme);
        attr(el, 'privacy-url', cfg.privacyUrl);
        attr(el, 'mode', cfg.mode);
        attr(el, 'invisible-hint', cfg.invisibleHint);
        if (cfg.autostart === false) attr(el, 'autostart', 'false');
        else if (cfg.autostart === true) attr(el, 'autostart', true);
        if (cfg.hideBranding) attr(el, 'hide-branding', true);
        if (cfg.invisible) attr(el, 'invisible', true);
        if (cfg.customTranslations) { try { attr(el, 'custom-translations', JSON.stringify(cfg.customTranslations)); } catch (e) {} }
        if (cfg.customDesign) { try { attr(el, 'custom-design', JSON.stringify(cfg.customDesign)); } catch (e) {} }
        if (TOKEN_INPUT_NAME) attr(el, 'token-field-name', TOKEN_INPUT_NAME);

        scaleTo16px(el);
        patchWhenReady(el);
        requestAnimationFrame(function () { compensateFlow(el); });

        var anchor = findSubmitAnchor(form);
        if (anchor.place === 'before' && anchor.node.parentNode) anchor.node.parentNode.insertBefore(el, anchor.node);
        else if (anchor.place === 'after') insertAfter(anchor.node, el);
        else anchor.node.appendChild(el);
        return true;
    }

    function insertBySelector(selectorList, cfg) {
        var form =
            document.querySelector(selectorList) ||
            document.querySelector(selectorList.replace(/createpost/gi, 'createPost')) ||
            document.querySelector(selectorList.replace(/forgotpasswordpost/gi, 'forgotPasswordPost')) ||
            document.querySelector(selectorList.replace(/loginpost/gi, 'loginPost'));
        return insertWidgetIntoForm(form, cfg);
    }

    function loadScriptOnce(src, done) {
        if (!src) { done && done(); return; }
        var already = document.querySelector('script[data-trustcaptcha="1"]');
        if (already) { done && done(); return; }
        var hadAMD = typeof window.define === 'function' && window.define.amd;
        var oldDefine = window.define;
        if (hadAMD) { try { window.define = undefined; } catch (e) {} }
        var s = document.createElement('script');
        s.src = src; s.async = false; s.defer = false;
        s.setAttribute('data-trustcaptcha', '1');
        s.onload = function () { if (hadAMD) window.define = oldDefine; done && done(); };
        s.onerror = function () { if (hadAMD) window.define = oldDefine; done && done(); };
        document.head.appendChild(s);
    }

    function initOnDom(cfg) {
        if (cfg.integrationMode === 'native') return;

        var selectors = Object.assign({
            login:          'form#login-form, form.form-login, form[action*="/customer/account/loginpost" i], form[action*="/customer/account/loginPost"]',
            register:       'form#create-account-form, form.form-create-account, form#form-validate[action*="account/create" i], form[action*="/customer/account/createpost" i], form[action*="/customer/account/createPost"]',
            forgot:         'form#form-validate[action*="forgot" i], form[action*="/customer/account/forgotpasswordpost" i], form[action*="/customer/account/forgotPasswordPost"]',
            contact:        'form#contact-form, form[action*="/contact/index/post" i]',
            review:         'form#review-form, form[action*="/review/product/post" i]',
            sendfriend:     'form#sendfriend-form, form[action*="/sendfriend/product/sendmail" i]',
            wishlist_share: 'form#wishlist-share-form, form[action*="/wishlist/index/share" i], form[action*="/wishlist/index/sharepost" i]',
            order_lookup:   'form#oar-widget-orders-and-returns-form, form[action*="/sales/guest/formPost" i]'
        }, (cfg.customSelectors || {}));

        function tryInit() {
            var any = false;
            if (cfg.forms.login)          any = insertBySelector(selectors.login, cfg)          || any;
            if (cfg.forms.register)       any = insertBySelector(selectors.register, cfg)       || any;
            if (cfg.forms.forgot)         any = insertBySelector(selectors.forgot, cfg)         || any;
            if (cfg.forms.contact)        any = insertBySelector(selectors.contact, cfg)        || any;
            if (cfg.forms.review)         any = insertBySelector(selectors.review, cfg)         || any;
            if (cfg.forms.sendfriend)     any = insertBySelector(selectors.sendfriend, cfg)     || any;
            if (cfg.forms.wishlist_share) any = insertBySelector(selectors.wishlist_share, cfg) || any;
            if (cfg.forms.order_lookup)   any = insertBySelector(selectors.order_lookup, cfg)   || any;
            return any;
        }

        tryInit();
        var mo = new MutationObserver(function () { tryInit(); });
        mo.observe(document.documentElement, { childList: true, subtree: true });
        if (window.customElements && customElements.whenDefined) {
            customElements.whenDefined(TAG_NAME).then(function () { tryInit(); }).catch(function () {});
        }
    }

    function normalizeAllExistingLater() {
        if (window.customElements && customElements.whenDefined) {
            customElements.whenDefined(TAG_NAME).then(initExisting).catch(function () {});
        }
        onPageShow(initExisting);
        window.addEventListener('resize', function () {
            document.querySelectorAll(TAG_NAME).forEach(function (el) {
                scaleTo16px(el); compensateFlow(el);
            });
        });
    }

    function boot() {
        var cfg = (window.trustCaptchaConfig || {});
        if (!cfg.enabled || !cfg.siteKey) return;

        loadScriptOnce(WIDGET_SCRIPT_URL, function () {
            initExisting();
            normalizeAllExistingLater();
            initOnDom(cfg);
        });
    }

    ready(boot);
    onPageShow(boot);
})();
