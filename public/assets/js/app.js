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

  // Confirmação em botões de envio (ex.: restaurar padrão)
  $$('[data-confirm-click]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      if (!window.confirm(b.getAttribute('data-confirm-click'))) e.preventDefault();
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

  // Pré-visualização de imagem antes de salvar (formulários do painel)
  $$('[data-image-field]').forEach(function (box) {
    var input = $('[data-image-input]', box), img = $('[data-image-preview]', box);
    if (!input || !img) return;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      if (file.size > 8 * 1024 * 1024) { window.alert('A imagem pode ter no máximo 8 MB.'); input.value = ''; return; }
      img.src = URL.createObjectURL(file);
      img.classList.remove('hidden');
    });
  });

  // Vídeo: confere o tamanho antes de enviar
  $$('[data-video-input]').forEach(function (input) {
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      var max = parseInt(input.getAttribute('data-max-bytes'), 10) || 0;
      if (file && max && file.size > max) {
        window.alert('O vídeo é maior que o limite de ' + Math.round(max / 1048576) + ' MB. Escolha um arquivo menor.');
        input.value = '';
      }
    });
  });

  // Feed da página inicial: rolagem infinita (sem JS, o botão "Carregar mais" funciona como link)
  var feed = $('[data-feed]');
  var more = $('[data-feed-more]');
  if (feed && more && 'IntersectionObserver' in window && window.fetch) {
    var loading = false;
    var next = parseInt(feed.getAttribute('data-feed-next'), 10) || 0;
    var link = $('[data-feed-link]', more);
    var loadMore = function () {
      if (loading || !next) return;
      loading = true;
      if (link) { link.textContent = 'Carregando...'; }
      fetch(feed.getAttribute('data-feed-url') + '?pagina=' + next, { credentials: 'same-origin' })
        .then(function (r) {
          var hasMore = r.headers.get('X-Feed-More') === '1';
          return r.text().then(function (html) { return { html: html, hasMore: hasMore }; });
        })
        .then(function (res) {
          feed.insertAdjacentHTML('beforeend', res.html);
          if (res.hasMore) { next += 1; if (link) link.textContent = 'Carregar mais'; }
          else { next = 0; more.remove(); observer.disconnect(); }
          loading = false;
          if (next) { observer.unobserve(more); observer.observe(more); }
        })
        .catch(function () { loading = false; if (link) link.textContent = 'Carregar mais'; });
    };
    var observer = new IntersectionObserver(function (entries) {
      if (entries.some(function (en) { return en.isIntersecting; })) loadMore();
    }, { rootMargin: '600px 0px' });
    observer.observe(more);
    if (link) link.addEventListener('click', function (e) { e.preventDefault(); loadMore(); });
  }

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
