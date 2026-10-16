(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  // Menu mobile do site
  $$('[data-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.getElementById(btn.getAttribute('data-toggle'));
      if (!target) return;
      var open = target.classList.toggle('hidden') === false;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
      var o = $('[data-icon-open]', btn), c = $('[data-icon-close]', btn);
      if (o && c) { o.classList.toggle('hidden', open); c.classList.toggle('hidden', !open); }
    });
  });

  // Drawer do painel (mobile)
  var drawer = document.getElementById('admin-drawer');
  if (drawer) {
    var setDrawer = function (open) {
      drawer.classList.toggle('hidden', !open);
      document.body.style.overflow = open ? 'hidden' : '';
      $$('[data-drawer-open]').forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
    };
    $$('[data-drawer-open]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(true); }); });
    $$('[data-drawer-close]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); }); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setDrawer(false); });
  }

  // Confirmação de ações destrutivas
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Evita envio duplicado: desabilita o botão após o envio
  $$('form').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (e.defaultPrevented) return;
      setTimeout(function () {
        $$('[data-loading]', f).forEach(function (b) {
          b.disabled = true;
          b.classList.add('opacity-60', 'cursor-not-allowed');
        });
      }, 0);
    });
  });
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) $$('[data-loading]').forEach(function (b) { b.disabled = false; b.classList.remove('opacity-60', 'cursor-not-allowed'); });
  });

  // Banner de cookies (LGPD)
  var KEY = 'dc_cookie_consent';
  var banner = document.getElementById('cookie-banner');
  if (banner) {
    var has = false;
    try { has = !!localStorage.getItem(KEY); } catch (err) { has = false; }
    if (!has) banner.classList.remove('hidden');
    $$('[data-cookie-choice]', banner).forEach(function (b) {
      b.addEventListener('click', function () {
        var v = b.getAttribute('data-cookie-choice');
        try { localStorage.setItem(KEY, JSON.stringify({ value: v, at: new Date().toISOString() })); } catch (err) {}
        document.cookie = KEY + '=' + v + '; Max-Age=' + (60 * 60 * 24 * 180) + '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
        banner.classList.add('hidden');
      });
    });
    $$('[data-cookie-prefs]').forEach(function (b) { b.addEventListener('click', function () { banner.classList.remove('hidden'); }); });
  }

  // Compartilhamento
  var share = $('[data-share]');
  if (share) {
    var url = share.getAttribute('data-url'), title = share.getAttribute('data-title');
    var label = $('[data-share-copy-label]', share);
    var copy = function () {
      var done = function () { if (label) { label.textContent = 'Link copiado!'; setTimeout(function () { label.textContent = 'Copiar link'; }, 2500); } };
      if (navigator.clipboard && navigator.clipboard.writeText) return navigator.clipboard.writeText(url).then(done, function () {});
      var t = document.createElement('textarea'); t.value = url; document.body.appendChild(t); t.select();
      try { document.execCommand('copy'); done(); } catch (err) {}
      document.body.removeChild(t); return Promise.resolve();
    };
    var cb = $('[data-share-copy]', share);
    if (cb) cb.addEventListener('click', copy);
    // O Instagram não tem URL de compartilhamento na web: usa o menu nativo ou copia o link.
    var ig = $('[data-share-instagram]', share);
    if (ig) ig.addEventListener('click', function () {
      if (navigator.share) { navigator.share({ title: title, url: url }).catch(function () {}); return; }
      copy().then(function () { window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer'); });
    });
  }
})();
