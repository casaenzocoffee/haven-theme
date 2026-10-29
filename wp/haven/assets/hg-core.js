/* Haven Guía · núcleo compartido (sin dependencias).
   Expone window.HG con datos, textos, medición, utilidades y registro de módulos. */
(function () {
  'use strict';
  document.documentElement.classList.add('hg-js');

  var dataEl = null;
  var D = null;
  function data() {
    if (D) return D;
    dataEl = dataEl || document.getElementById('haven-guia-data');
    try { D = JSON.parse(dataEl ? dataEl.textContent : '{}'); } catch (e) { D = {}; }
    D.lugares = D.lugares || []; D.suites = D.suites || []; D.t = D.t || {}; D.cfg = D.cfg || {}; D.eventos = D.eventos || [];
    D.byId = {};
    D.lugares.forEach(function (l) { D.byId[l.id] = l; });
    return D;
  }

  var HG = window.HG = window.HG || {};
  HG.mods = HG.mods || {};
  HG.data = data;

  /* Textos con variables: HG.t('map_count', {n: 3, m: 10}) */
  HG.t = function (k, vars) {
    var s = (data().t[k] || k) + '';
    if (vars) Object.keys(vars).forEach(function (v) { s = s.split('{' + v + '}').join(vars[v]); });
    return s;
  };
  HG.lang = function () { return data().lang || 'es'; };
  HG.num = function (n, d) {
    return Number(n).toLocaleString(HG.lang() === 'en' ? 'en-US' : 'es-CO', { minimumFractionDigits: d || 0, maximumFractionDigits: d || 0 });
  };
  HG.reduce = false; // animaciones siempre activas (decisión de marca)

  /* Medición: GTM (dataLayer) + píxel de Meta (PixelYourSite define fbq) */
  HG.track = function (event, params) {
    params = params || {};
    window.dataLayer = window.dataLayer || [];
    var o = { event: event }; Object.keys(params).forEach(function (k) { o['hg_' + k] = params[k]; });
    window.dataLayer.push(o);
    try { if (typeof window.fbq === 'function') window.fbq('trackCustom', event, params); } catch (e) {}
  };

  /* Fechas */
  HG.iso = function (d) { var z = new Date(d.getTime() - d.getTimezoneOffset() * 60000); return z.toISOString().slice(0, 10); };
  HG.addDays = function (iso, n) { var d = new Date(iso + 'T12:00:00'); d.setDate(d.getDate() + n); return HG.iso(d); };
  HG.tomorrow = function () { var d = new Date(); d.setDate(d.getDate() + 1); return HG.iso(d); };

  /* Motor de reservas (Backhotelite): base/AAAA-MM-DD/AAAA-MM-DD/ */
  HG.bookUrl = function (inIso, nights) {
    var c = data().cfg; inIso = inIso || HG.tomorrow(); nights = Math.max(1, nights || 1);
    return (c.book || '') + inIso + '/' + HG.addDays(inIso, nights) + '/';
  };
  HG.book = function (inIso, nights, from) {
    HG.track('hg_book', { origen: from || '', llegada: inIso || '', noches: nights || 1 });
    var a = document.createElement('a'); a.href = HG.bookUrl(inIso, nights); a.target = '_blank'; a.rel = 'noopener'; document.body.append(a); a.click(); a.remove();
  };
  HG.waShare = function (text) { return 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text); };

  /* Distancias */
  HG.dist = function (a, b, c, d) {
    var R = 6371000, r = Math.PI / 180, dl = (d - b) * r, dp = (c - a) * r;
    var x = Math.sin(dp / 2) * Math.sin(dp / 2) + Math.cos(a * r) * Math.cos(c * r) * Math.sin(dl / 2) * Math.sin(dl / 2);
    return 2 * R * Math.asin(Math.min(1, Math.sqrt(x)));
  };
  /* Metros por calle entre dos lugares (estimado con el factor calibrado) */
  HG.streetM = function (p, q) { return HG.dist(p.lat, p.lng, q.lat, q.lng) * (data().cfg.detour || 1.26); };

  /* DOM */
  HG.h = function (tag, attrs, kids) {
    var el = document.createElement(tag);
    if (attrs) Object.keys(attrs).forEach(function (k) {
      var v = attrs[k];
      if (v === null || v === undefined || v === false) return;
      if (k === 'class') el.className = v;
      else if (k === 'html') el.innerHTML = v;
      else if (k === 'text') el.textContent = v;
      else if (k.slice(0, 2) === 'on') el.addEventListener(k.slice(2), v);
      else el.setAttribute(k, v === true ? '' : v);
    });
    (kids || []).forEach(function (c) { if (c != null) el.append(c.nodeType ? c : document.createTextNode(c)); });
    return el;
  };
  HG.esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  HG.toast = function (msg) {
    var t = HG.h('div', { class: 'hg-toast', role: 'status', text: msg });
    document.body.append(t); setTimeout(function () { t.remove(); }, 1900);
  };
  HG.copy = function (text) {
    var done = function () { HG.toast(HG.t('plan_copied')); };
    if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text).then(done, function () { fallback(); });
    fallback();
    function fallback() { var ta = HG.h('textarea', { style: 'position:fixed;opacity:0' }); ta.value = text; document.body.append(ta); ta.select(); try { document.execCommand('copy'); done(); } catch (e) {} ta.remove(); }
  };
  HG.vibe = function (ms) { try { if (navigator.vibrate) navigator.vibrate(ms || 8); } catch (e) {} };

  /* Contador animado */
  HG.countTo = function (el, to, dec, dur) {
    var from = parseFloat(el.dataset.v || '0') || 0; el.dataset.v = to;
    if (HG.reduce || from === to) { el.textContent = HG.num(to, dec); return; }
    var t0 = performance.now(); dur = dur || 700;
    (function step(t) {
      var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
      el.textContent = HG.num(from + (to - from) * e, dec);
      if (p < 1) requestAnimationFrame(step);
    })(t0);
  };

  /* Relleno del deslizador (pista dorada) */
  HG.fillRange = function (r) {
    var p = (r.value - r.min) / (r.max - r.min) * 100; r.style.setProperty('--fill', p + '%');
  };

  /* Favoritos compartidos entre mapa y planificador (solo en este navegador) */
  HG.favs = {
    key: 'hg_favs',
    get: function () { try { return JSON.parse(localStorage.getItem(this.key) || '[]'); } catch (e) { return []; } },
    has: function (id) { return this.get().indexOf(id) > -1; },
    toggle: function (id) {
      var f = this.get(), i = f.indexOf(id);
      if (i > -1) f.splice(i, 1); else f.push(id);
      try { localStorage.setItem(this.key, JSON.stringify(f)); } catch (e) {}
      document.dispatchEvent(new CustomEvent('hg:favs', { detail: f }));
      return i === -1;
    }
  };

  /* Iconos (SVG en línea, sin librerías) */
  var P = {
    comer: 'M7 3v8a2 2 0 0 0 2 2v8M11 3v8M9 3v8M17 21V3c-2 0-3 2-3 6s1 5 3 5',
    cafe: 'M4 8h13v5a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8ZM17 10h1.5a2.5 2.5 0 0 1 0 5H17M8 3c0 1-1 1-1 2s1 1 1 2M12 3c0 1-1 1-1 2s1 1 1 2',
    noche: 'M8 21h8M12 15v6M5 4h14l-7 11L5 4ZM7.5 8h9',
    compras: 'M5 8h14l-1 13H6L5 8ZM9 8V6a3 3 0 0 1 6 0v2',
    cultura: 'M3 21h18M5 21V10M9 21V10M15 21V10M19 21V10M2 10h20L12 3 2 10Z',
    parques: 'M12 21v-6M12 15c-4 0-6-2.5-6-5.5S9 3 12 3s6 3.5 6 6.5S16 15 12 15Z',
    bienestar: 'M12 21s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6-8 11-8 11Z',
    salida: 'M3 20l6-10 4 6 3-4 5 8H3ZM15 7a2 2 0 1 0 0-.1',
    haven: 'M12 3l2.5 5.5L20 9.5l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-1L12 3Z',
    walk: 'M13 4a1.5 1.5 0 1 0 0-.1M9 21l2-6 3 3v3M10 9l3-1 2 4 3 1M10 9l-2 4M11 15l1-5',
    car: 'M5 16V11l2-5h10l2 5v5M3 16h18v3H3zM7 19v2M17 19v2M7 13h.01M17 13h.01',
    swap: 'M4 7h13l-3-3M20 17H7l3 3',
    x: 'M6 6l12 12M18 6 6 18',
    grip: 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01',
    pin: 'M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11ZM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    heart: 'M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.5-7 10-7 10Z',
    share: 'M4 12v7h16v-7M12 3v12M7 8l5-5 5 5',
    link: 'M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1',
    wa: 'M4 20l1.3-3.9A8 8 0 1 1 8 19L4 20Z',
    copy: 'M8 8h11v11H8zM5 16V5h11',
    plus: 'M12 5v14M5 12h14',
    jacuzzi: 'M3 12h18v3a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5v-3ZM6 12V6a2 2 0 0 1 4 0',
    balcon: 'M4 20V10l8-6 8 6v10M4 14h16',
    cocina: 'M4 10h16v10H4zM8 10V5h8v5M10 15h4',
    sala: 'M3 18v-6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v6M3 15h18M6 9V6h12v3',
    area: 'M4 4h16v16H4zM4 9h5V4M20 15h-5v5',
    users: 'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3 20a6 6 0 0 1 12 0M16 5a3 3 0 0 1 0 6M18 20a6 6 0 0 0-3-5',
    bed: 'M3 18V8M3 14h18v4M21 14v-3a3 3 0 0 0-3-3h-7v6M7 11h.01',
    pareja: 'M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2 20a6 6 0 0 1 12 0M10 20a6 6 0 0 1 12 0',
    amigos: 'M12 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM5 12a2.5 2.5 0 1 0 0-5M19 12a2.5 2.5 0 1 0 0-5M6 20a6 6 0 0 1 12 0M2 19a4 4 0 0 1 4-4M22 19a4 4 0 0 0-4-4',
    familia: 'M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM17 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM2 20a6 6 0 0 1 12 0M14 20a3.5 3.5 0 0 1 7 0',
    negocios: 'M4 8h16v11H4zM9 8V5h6v3M4 13h16',
    solo: 'M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0',
    star: 'M12 3l2.5 5.5L20 9.5l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-1L12 3Z',
    moon: 'M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z',
    leaf: 'M5 19C5 10 10 5 20 4c-1 10-6 15-15 15ZM5 19l7-7',
    brief: 'M4 8h16v11H4zM9 8V5h6v3',
    glass: 'M7 3h10l-1 7a4 4 0 0 1-8 0L7 3ZM12 14v6M8 21h8',
    sun: 'M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5 19 19M5 19l1.5-1.5M17.5 6.5 19 5',
    view: 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
    cal: 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4'
  };
  HG.icon = function (name, size) {
    return '<svg viewBox="0 0 24 24" width="' + (size || 18) + '" height="' + (size || 18) + '" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + (P[name] || P.pin) + '"/></svg>';
  };
  HG.catColor = function (cat) {
    return ({ comer: '#E8A87C', cafe: '#C49A6C', noche: '#A99BF5', compras: '#F7DFAE', cultura: '#86C3C9', parques: '#97C08A', bienestar: '#E9B9CB', salida: '#D8C7A0', haven: '#CDB371' })[cat] || '#CDB371';
  };

  /* Índice de capítulos: marca el activo con el scroll */
  function chapters() {
    var nav = document.querySelector('.hg-chapters'); if (!nav || !('IntersectionObserver' in window)) return;
    var links = [].slice.call(nav.querySelectorAll('a'));
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        links.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + e.target.id); });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    links.forEach(function (a) { var s = document.querySelector(a.getAttribute('href')); if (s) io.observe(s); });
  }

  /* Arranque: cada [data-hg] se activa cuando se acerca a la pantalla (no carga trabajo de más) */
  /* En la portada los datos (mapa) vienen en un archivo aparte y se piden solo cuando el bloque se acerca a la pantalla */
  var pending = null;
  function ensure(cb) {
    var el = document.getElementById('haven-guia-data'), src = el && el.getAttribute('data-src');
    if (D || !src || el.textContent.length > 2) return cb();
    if (!pending) {
      pending = [];
      fetch(src, { credentials: 'same-origin' }).then(function (r) { return r.text(); })
        .then(function (t) { el.textContent = t; }, function () {})
        .then(function () { var q = pending; pending = []; q.forEach(function (f) { f(); }); });
    }
    pending.push(cb);
  }
  function boot() {
    var d0 = document.getElementById('haven-guia-data'); if (!(d0 && d0.getAttribute('data-src'))) data();
    chapters();
    var roots = [].slice.call(document.querySelectorAll('[data-hg]'));
    var start = function (el) {
      if (el.__hg) return; el.__hg = 1;
      ensure(function () {
        var m = HG.mods[el.getAttribute('data-hg')];
        if (m) { try { m(el); } catch (e) { if (window.console) console.error('[Haven Guía]', e); } }
      });
    };
    if (!('IntersectionObserver' in window)) return roots.forEach(start);
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { io.unobserve(e.target); start(e.target); } });
    }, { rootMargin: '400px 0px' });
    roots.forEach(function (r) { io.observe(r); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else setTimeout(boot, 0);
})();
