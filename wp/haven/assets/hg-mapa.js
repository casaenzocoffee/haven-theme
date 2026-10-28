/* 01 · A pie desde Haven
   Radar en SVG propio (liviano, con la estética Haven) + mapa de calles opcional (Leaflet, carga bajo demanda).
   Interacción: deslizador de minutos, anillo dorado que se arrastra con el dedo, filtros, arrastrar / pellizcar / rueda+Ctrl,
   ficha por lugar con ruta en Google Maps, favoritos que pasan al planificador. */
(function () {
  'use strict';
  var HG = window.HG; if (!HG) return;
  var NS = 'http://www.w3.org/2000/svg';
  function s(tag, attrs) { var el = document.createElementNS(NS, tag); if (attrs) for (var k in attrs) el.setAttribute(k, attrs[k]); return el; }

  HG.mods.mapa = function (root) {
    var D = HG.data(), H = D.hotel, cfg = D.cfg;
    var walk = cfg.walk || 78, detour = cfg.detour || 1.26;
    var HIDE = { 'haven-brunch-dominical': 1, 'haven-wellness': 1 }; // siguen en el planificador, no en el mapa ni en la lista
    var all = D.lugares.filter(function (l) { return l.cat !== 'salida' && !HIDE[l.id]; });
    var havenItems = all.filter(function (l) { return l.cat === 'haven'; });
    var places = all.filter(function (l) { return l.cat !== 'haven'; });
    var radarPlaces = places.filter(function (l) { return l.min <= 32; });

    var st = { cat: root.dataset.cat || '', zona: root.dataset.zona || '', min: 10, view: 'radar', sel: null, k: 1, tx: 0, ty: 0 };
    var stage = root.querySelector('.hg-map__stage');
    var canvas = root.querySelector('.hg-map__canvas');
    var card = root.querySelector('.hg-card');
    var range = root.querySelector('.hg-range__input');
    var out = root.querySelector('.hg-range__val');
    var countEl = root.querySelector('.hg-map__count');
    var hint = root.querySelector('.hg-map__hint');
    var listItems = [].slice.call(root.querySelectorAll('.hg-item'));

    /* ---------- Proyección: metros alrededor de Haven ---------- */
    var kx = 111320 * Math.cos(H.lat * Math.PI / 180), ky = 110540;
    function xy(l) { return [(l.lng - H.lng) * kx, -(l.lat - H.lat) * ky]; }
    function rMin(m) { return m * walk / detour; } // radio (línea recta) equivalente a m minutos a pie
    var R = rMin(26), VB = R * 2, K0 = R / rMin(16);

    /* ---------- SVG ---------- */
    var svg = s('svg', { viewBox: (-R) + ' ' + (-R) + ' ' + VB + ' ' + VB, preserveAspectRatio: 'xMidYMid meet', role: 'img', 'aria-hidden': 'true' });
    var world = s('g', { class: 'hg-world' });
    var gGrid = s('g'), gRings = s('g'), gLand = s('g'), gRoute = s('g'), gPins = s('g'), gTop = s('g');
    world.append(gGrid, gRings, gLand, gRoute, gPins, gTop);
    svg.append(world);
    canvas.append(svg);

    var upp = 1; // unidades SVG por píxel
    function measure() { var r = canvas.getBoundingClientRect(); upp = VB / Math.max(1, Math.min(r.width, r.height)); }

    // Rayos y anillos fijos
    for (var a = 0; a < 12; a++) {
      var ang = a * Math.PI / 6;
      gGrid.append(s('line', { class: 'hg-grid-line', x1: 0, y1: 0, x2: Math.cos(ang) * R, y2: Math.sin(ang) * R }));
    }
    (cfg.rings || [5, 10, 15]).concat([20, 25]).forEach(function (m) {
      gRings.append(s('circle', { class: 'hg-ring', r: rMin(m), cx: 0, cy: 0 }));
      var lab = s('text', { class: 'hg-ring-label', x: 0, y: -rMin(m) - 6, 'text-anchor': 'middle', 'data-fs': 10 });
      lab.textContent = m + ' MIN'; gRings.append(lab);
    });
    var north = s('text', { class: 'hg-north', x: 0, y: -R + 30, 'text-anchor': 'middle', 'data-fs': 10 }); north.textContent = 'N';
    gGrid.append(north);

    // Anillo vivo + manija para arrastrar
    var live = s('circle', { class: 'hg-live', cx: 0, cy: 0, r: rMin(st.min) });
    var liveLab = s('text', { class: 'hg-live-label', 'text-anchor': 'start', 'data-fs': 15 });
    var knob = s('g', { class: 'hg-knob', style: 'cursor:grab' });
    var knobC = s('circle', { r: 10, fill: '#F7DFAE', stroke: '#CDB371', 'stroke-width': 4, 'vector-effect': 'non-scaling-stroke' });
    var knobHalo = s('circle', { r: 22, fill: 'rgba(205,179,113,.18)' });
    knob.append(knobHalo, knobC);
    gRings.append(live);

    // Zonas (nombre en el centro de sus lugares)
    var zones = {};
    radarPlaces.forEach(function (l) { if (!l.zona || /haven|otra|loma/i.test(l.zona)) return; (zones[l.zona] = zones[l.zona] || []).push(xy(l)); });
    Object.keys(zones).forEach(function (z) {
      var pts = zones[z]; if (pts.length < 2) return;
      var cx = pts.reduce(function (t, p) { return t + p[0]; }, 0) / pts.length, cy = pts.reduce(function (t, p) { return t + p[1]; }, 0) / pts.length;
      var t = s('text', { class: 'hg-landmark', x: cx, y: cy - 40, 'text-anchor': 'middle', 'data-fs': 13 }); t.textContent = z; gLand.append(t);
    });

    // Hotel
    var hotel = s('g', { class: 'hg-hotel', style: 'cursor:pointer' });
    var hPulse = s('circle', { class: 'pulse', r: 14 });
    var hCore = s('circle', { class: 'core', r: 9 });
    var hStar = s('path', { d: 'M0,-5 L1.5,-1.5 L5,-1.2 L2.3,1.2 L3.1,5 L0,3 L-3.1,5 L-2.3,1.2 L-5,-1.2 L-1.5,-1.5Z', fill: '#191311' });
    var hLab = s('text', { y: 30, 'text-anchor': 'middle', 'font-size': 12 }); hLab.textContent = 'HAVEN';
    hotel.append(hPulse, hCore, hStar, hLab); gTop.append(hotel, liveLab, knob);

    // Pines (con un pequeño desplazamiento si dos lugares coinciden)
    var pins = {}, placed = [];
    radarPlaces.forEach(function (l) {
      var p = xy(l), tries = 0;
      while (tries < 8 && placed.some(function (q) { return Math.hypot(q[0] - p[0], q[1] - p[1]) < 22; })) {
        var an = tries * 2.4; p = [p[0] + Math.cos(an) * 24, p[1] + Math.sin(an) * 24]; tries++;
      }
      placed.push(p);
      var g = s('g', { class: 'hg-pin', 'data-id': l.id, 'data-cat': l.cat, style: '--pc:' + HG.catColor(l.cat), tabindex: '-1' });
      var halo = s('circle', { class: 'halo', r: 18 });
      var dot = s('circle', { class: 'dot', r: 7 });
      var txt = s('text', { x: 12, y: 4, 'font-size': 11 }); txt.textContent = l.n;
      g.append(halo, dot, txt);
      g.__p = p; g.__l = l;
      gPins.append(g); pins[l.id] = g;
    });

    /* ---------- Transformación (arrastrar / zoom) ---------- */
    function apply() {
      world.setAttribute('transform', 'translate(' + st.tx + ',' + st.ty + ') scale(' + st.k + ')');
      var u = upp / st.k; // tamaño constante en pantalla
      Object.keys(pins).forEach(function (id) {
        var g = pins[id], p = g.__p;
        g.setAttribute('transform', 'translate(' + p[0] + ',' + p[1] + ') scale(' + u + ')');
      });
      hotel.setAttribute('transform', 'scale(' + u + ')');
      [].forEach.call(svg.querySelectorAll('[data-fs]'), function (t) { t.style.fontSize = (t.dataset.fs * u) + 'px'; });
      knob.setAttribute('data-u', u);
      placeKnob();
      root.classList.toggle('is-zoomed', st.k > K0 * 1.5);
      if (st.sel) drawRoute(st.sel);
    }
    function zoomAt(k2, px, py) {
      k2 = Math.max(0.8, Math.min(6, k2));
      if (px == null) { px = 0; py = 0; }
      st.tx = px - (px - st.tx) * (k2 / st.k);
      st.ty = py - (py - st.ty) * (k2 / st.k);
      st.k = k2; clampPan(); apply();
    }
    function clampPan() {
      var lim = R * st.k * 0.9;
      st.tx = Math.max(-lim, Math.min(lim, st.tx)); st.ty = Math.max(-lim, Math.min(lim, st.ty));
    }
    function toSvg(cx, cy) {
      var pt = svg.createSVGPoint(); pt.x = cx; pt.y = cy;
      var m = svg.getScreenCTM(); return m ? pt.matrixTransform(m.inverse()) : { x: 0, y: 0 };
    }
    function toWorld(cx, cy) { var p = toSvg(cx, cy); return { x: (p.x - st.tx) / st.k, y: (p.y - st.ty) / st.k }; }
    function reset() {
      var k0 = st.k, x0 = st.tx, y0 = st.ty, t0 = performance.now();
      (function step(t) {
        var p = Math.min(1, (t - t0) / 500), e = 1 - Math.pow(1 - p, 3);
        st.k = k0 + (K0 - k0) * e; st.tx = x0 * (1 - e); st.ty = y0 * (1 - e); apply();
        if (p < 1) requestAnimationFrame(step);
      })(t0);
    }
    function flyTo(p, k2) {
      var k0 = st.k, x0 = st.tx, y0 = st.ty, t0 = performance.now(); k2 = Math.max(k0, k2 || 2.2);
      var x1 = -p[0] * k2, y1 = -p[1] * k2;
      (function step(t) {
        var q = Math.min(1, (t - t0) / 650), e = 1 - Math.pow(1 - q, 3);
        st.k = k0 + (k2 - k0) * e; st.tx = x0 + (x1 - x0) * e; st.ty = y0 + (y1 - y0) * e; apply();
        if (q < 1) requestAnimationFrame(step);
      })(t0);
    }

    // Punteros: ratón arrastra; táctil: un dedo horizontal arrastra, dos dedos pellizcan
    var ptrs = {}, last = null, pinch = null, moved = 0, knobDrag = false;
    canvas.addEventListener('pointerdown', function (e) {
      if (e.target.closest('.hg-knob')) return;
      ptrs[e.pointerId] = { x: e.clientX, y: e.clientY };
      moved = 0; root.classList.add('is-touched');
      var ids = Object.keys(ptrs);
      if (ids.length === 1) { last = { x: e.clientX, y: e.clientY }; }
      if (ids.length === 2) {
        var a = ptrs[ids[0]], b = ptrs[ids[1]];
        pinch = { d: Math.hypot(a.x - b.x, a.y - b.y), k: st.k };
      }
      try { canvas.setPointerCapture(e.pointerId); } catch (x) {}
    });
    canvas.addEventListener('pointermove', function (e) {
      if (!ptrs[e.pointerId]) return;
      ptrs[e.pointerId] = { x: e.clientX, y: e.clientY };
      var ids = Object.keys(ptrs);
      if (ids.length === 2 && pinch) {
        var a = ptrs[ids[0]], b = ptrs[ids[1]];
        var d = Math.hypot(a.x - b.x, a.y - b.y), mid = toSvg((a.x + b.x) / 2, (a.y + b.y) / 2);
        zoomAt(pinch.k * d / pinch.d, mid.x, mid.y); moved = 99; return;
      }
      if (ids.length === 1 && last) {
        var p0 = toSvg(last.x, last.y), p1 = toSvg(e.clientX, e.clientY);
        st.tx += p1.x - p0.x; st.ty += p1.y - p0.y; clampPan(); apply();
        moved += Math.abs(e.clientX - last.x) + Math.abs(e.clientY - last.y);
        last = { x: e.clientX, y: e.clientY };
        if (moved > 6) canvas.classList.add('is-dragging');
      }
    });
    function up(e) {
      delete ptrs[e.pointerId];
      if (Object.keys(ptrs).length < 2) pinch = null;
      if (!Object.keys(ptrs).length) { last = null; canvas.classList.remove('is-dragging'); }
    }
    canvas.addEventListener('pointerup', up); canvas.addEventListener('pointercancel', up);
    canvas.addEventListener('wheel', function (e) {
      if (!e.ctrlKey && !e.metaKey) { flash(HG.lang() === 'en' ? 'Hold Ctrl (⌘) + scroll to zoom' : 'Mantén Ctrl (⌘) + rueda para acercar'); return; }
      e.preventDefault();
      var p = toSvg(e.clientX, e.clientY); zoomAt(st.k * Math.exp(-e.deltaY * 0.0022), p.x, p.y);
    }, { passive: false });
    var flashT;
    function flash(msg) {
      if (!hint) return; hint.textContent = msg; hint.classList.add('is-alert');
      clearTimeout(flashT); flashT = setTimeout(function () { hint.classList.remove('is-alert'); hint.textContent = HG.t('map_drag_hint'); }, 1600);
    }
    canvas.addEventListener('keydown', function (e) {
      var step = 60 / st.k;
      if (e.key === 'ArrowLeft') st.tx += step * st.k; else if (e.key === 'ArrowRight') st.tx -= step * st.k;
      else if (e.key === 'ArrowUp') st.ty += step * st.k; else if (e.key === 'ArrowDown') st.ty -= step * st.k;
      else if (e.key === '+' || e.key === '=') return zoomAt(st.k * 1.3);
      else if (e.key === '-') return zoomAt(st.k / 1.3);
      else if (e.key === 'Escape') return closeCard();
      else return;
      e.preventDefault(); clampPan(); apply();
    });

    // Clic en pines / hotel (si no hubo arrastre)
    canvas.addEventListener('click', function (e) {
      if (moved > 6) return;
      var g = e.target.closest('.hg-pin');
      if (g) return select(g.dataset.id, false);
      if (e.target.closest('.hg-hotel')) return openHotel();
    });
    // Resaltar al pasar el mouse
    canvas.addEventListener('pointerover', function (e) { var g = e.target.closest('.hg-pin'); if (g) hot(g.dataset.id, true); });
    canvas.addEventListener('pointerout', function (e) { var g = e.target.closest('.hg-pin'); if (g) hot(g.dataset.id, false); });

    /* ---------- Anillo arrastrable ---------- */
    var knobAng = -Math.PI / 4;
    function placeKnob() {
      var r = Number(live.getAttribute('r')), u = Number(knob.getAttribute('data-u') || 1);
      knob.setAttribute('transform', 'translate(' + Math.cos(knobAng) * r + ',' + Math.sin(knobAng) * r + ') scale(' + u + ')');
      liveLab.setAttribute('x', Math.cos(knobAng) * r + 18 * u);
      liveLab.setAttribute('y', Math.sin(knobAng) * r - 14 * u);
      liveLab.textContent = st.min + ' min';
    }
    function knobMove(e) {
      if (!knobDrag) return; e.preventDefault();
      var p = toWorld(e.clientX, e.clientY), d = Math.hypot(p.x, p.y);
      knobAng = Math.atan2(p.y, p.x);
      var m = Math.round(Math.max(3, Math.min(25, d * detour / walk)));
      if (m !== st.min) { setMin(m, true); HG.vibe(4); } else placeKnob();
    }
    function knobUp() {
      if (!knobDrag) return; knobDrag = false; knob.style.cursor = 'grab';
      window.removeEventListener('pointermove', knobMove); window.removeEventListener('pointerup', knobUp); window.removeEventListener('pointercancel', knobUp);
      trackMin();
    }
    knob.style.touchAction = 'none';
    knob.addEventListener('pointerdown', function (e) {
      e.stopPropagation(); e.preventDefault(); knobDrag = true; knob.style.cursor = 'grabbing';
      window.addEventListener('pointermove', knobMove, { passive: false }); window.addEventListener('pointerup', knobUp); window.addEventListener('pointercancel', knobUp);
      root.classList.add('is-touched'); HG.vibe(6);
    });

    var curR = rMin(st.min), anim;
    function animateRing(to) {
      cancelAnimationFrame(anim);
      var from = curR, t0 = performance.now();
      (function step(t) {
        var p = Math.min(1, (t - t0) / (HG.reduce ? 1 : 380)), e = 1 - Math.pow(1 - p, 3);
        curR = from + (to - from) * e; live.setAttribute('r', curR); placeKnob(); if (leafLive) leafLive.setRadius(curR * detour / detour);
        if (p < 1) anim = requestAnimationFrame(step);
      })(t0);
    }

    /* ---------- Estado → vista ---------- */
    function inCat(l) { return (!st.cat || st.cat === l.cat || (st.cat === 'haven' && l.cat === 'haven')) && (!st.zona || l.zona.toLowerCase().indexOf(st.zona.toLowerCase()) > -1); }
    function render() {
      var n = 0;
      places.forEach(function (l) {
        var vis = inCat(l), inside = l.min <= st.min;
        if (vis && inside) n++;
        var g = pins[l.id];
        if (g) { g.classList.toggle('is-hidden', !vis); g.classList.toggle('is-out', vis && !inside); }
        if (leafMarkers[l.id]) leafMarkers[l.id].setStyle({ opacity: vis ? (inside ? 1 : .25) : 0, fillOpacity: vis ? (inside ? .95 : .2) : 0 });
      });
      if (st.cat === 'haven') n = havenItems.length;
      listItems.forEach(function (li) {
        var l = D.byId[li.dataset.id]; if (!l) return;
        var vis = inCat(l);
        li.classList.toggle('is-hidden', !vis);
        li.classList.toggle('is-out', vis && l.min > st.min && l.cat !== 'haven');
      });
      collapse();
      countEl.innerHTML = (n === 1 ? HG.t('map_count_one', { m: st.min }) : HG.t('map_count', { n: '<b>' + n + '</b>', m: st.min }));
    }
    function setMin(m, fromKnob) {
      st.min = m; range.value = m; HG.fillRange(range);
      out.textContent = m + ' min';
      animateRing(rMin(m)); render();
      if (!fromKnob) trackMin();
    }
    var tmT;
    function trackMin() { clearTimeout(tmT); tmT = setTimeout(function () { HG.track('hg_map_minutes', { minutos: st.min }); }, 800); }

    range.addEventListener('input', function () { setMin(Number(range.value)); HG.vibe(3); });
    [].forEach.call(root.querySelectorAll('.hg-map__controls .hg-chip'), function (b) {
      b.addEventListener('click', function () {
        [].forEach.call(root.querySelectorAll('.hg-map__controls .hg-chip'), function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        st.cat = b.dataset.cat; render(); HG.vibe(5);
        HG.track('hg_map_filter', { categoria: st.cat || 'todo' });
        if (st.cat === 'haven') openHotel();
      });
    });
    [].forEach.call(root.querySelectorAll('.hg-zoom button'), function (b) {
      b.addEventListener('click', function () { var z = Number(b.dataset.zoom); if (!z) { reset(); if (leaf) leaf.setView([H.lat, H.lng], 16); return; } if (st.view === 'streets' && leaf) { leaf.setZoom(leaf.getZoom() + z); return; } zoomAt(st.k * (z > 0 ? 1.4 : 1 / 1.4)); });
    });

    /* ---------- Lista ---------- */
    listItems.forEach(function (li) {
      var l = D.byId[li.dataset.id]; if (!l) return;
      li.style.setProperty('--pc', HG.catColor(l.cat));
      var btn = li.querySelector('.hg-item__btn');
      btn.addEventListener('click', function () { select(l.id, true); });
      btn.addEventListener('mouseenter', function () { hot(l.id, true); });
      btn.addEventListener('mouseleave', function () { hot(l.id, false); });
    });
    var LIST_MAX = 12, listEl = root.querySelector('.hg-map__list'), moreBtn = HG.h('button', { type: 'button', class: 'hg-more' });
    listEl.after(moreBtn);
    function collapse() {
      var vis = listItems.filter(function (li) { return !li.classList.contains('is-hidden'); });
      vis.forEach(function (li, k) { li.classList.toggle('is-more', k >= LIST_MAX); });
      listItems.forEach(function (li) { if (li.classList.contains('is-hidden')) li.classList.remove('is-more'); });
      var extra = vis.length - LIST_MAX;
      moreBtn.hidden = extra <= 0;
      moreBtn.textContent = listEl.classList.contains('is-open') ? (HG.lang() === 'en' ? 'Show less' : 'Ver menos') : (HG.lang() === 'en' ? 'Show ' + extra + ' more' : 'Ver ' + extra + ' más');
    }
    moreBtn.addEventListener('click', function () { listEl.classList.toggle('is-open'); collapse(); });
    function hot(id, on) {
      var g = pins[id]; if (g) g.classList.toggle('is-hot', on);
      var li = root.querySelector('.hg-item[data-id="' + id + '"]'); if (li) li.classList.toggle('is-hot', on);
    }

    /* ---------- Ficha ---------- */
    function km(m) { return m >= 1000 ? HG.num(m / 1000, 1) + ' km' : HG.num(Math.round(m / 10) * 10) + ' m'; }
    function select(id, fromList) {
      var l = D.byId[id]; if (!l) return;
      if (st.sel && pins[st.sel]) pins[st.sel].classList.remove('is-sel');
      st.sel = id;
      if (pins[id]) { pins[id].classList.add('is-sel'); if (fromList && st.view === 'radar') flyTo(pins[id].__p, 2.4); }
      if (leaf && st.view === 'streets' && fromList) leaf.flyTo([l.lat, l.lng], 17, { duration: .6 });
      drawRoute(id);
      var far = l.min > 25;
      var stats = l.cat === 'haven' ? '<div><b>0</b><span>min</span></div><div><b>' + HG.esc(HG.t('plan_at_haven')) + '</b><span>Calle 10A</span></div><div><b>' + (l.precio ? '$'.repeat(l.precio) : '—') + '</b><span>' + HG.esc(HG.t('price')) + '</span></div>' : far
        ? '<div><b>' + (l.carro || '—') + '</b><span>min ' + HG.esc(HG.t('map_car')) + '</span></div><div><b>' + km(l.m / detour * 1.35) + '</b><span>' + HG.esc(HG.t('map_from_haven')) + '</span></div><div><b>' + (l.precio ? '$'.repeat(l.precio) : '—') + '</b><span>' + HG.esc(HG.t('price')) + '</span></div>'
        : '<div><b>' + l.min + '</b><span>min ' + HG.esc(HG.t('map_walk')) + '</span></div><div><b>' + km(l.m) + '</b><span>' + HG.esc(HG.t('map_from_haven')) + '</span></div><div><b>' + HG.num(l.pasos) + '</b><span>' + HG.esc(HG.t('map_steps')) + '</span></div>';
      var fav = HG.favs.has(l.id);
      card.style.setProperty('--pc', HG.catColor(l.cat));
      card.innerHTML =
        (l.img ? '<div class="hg-card__img" style="background-image:url(\'' + encodeURI(l.img) + '\')"></div>' : '') +
        '<button type="button" class="hg-card__close" aria-label="' + HG.esc(HG.t('map_close')) + '">' + HG.icon('x', 16) + '</button>' +
        '<div class="hg-card__cat">' + HG.esc(HG.t('cat_' + l.cat)) + (l.sub ? ' · ' + HG.esc(l.sub.replace(/-/g, ' ')) : '') + '</div>' +
        '<h3>' + HG.esc(l.n) + '</h3><p class="hg-card__zone">' + HG.esc([l.zona, l.dir].filter(Boolean).join(' · ')) + '</p>' +
        '<div class="hg-card__stats">' + stats + '</div>' +
        (l.desc ? '<p class="desc">' + HG.esc(l.desc) + '</p>' : '') +
        (l.tip ? '<p class="tip"><b>' + HG.esc(HG.t('map_concierge')) + '</b>' + HG.esc(l.tip) + '</p>' : '') +
        '<div class="hg-card__actions">' +
        '<a class="hg-btn hg-btn--gold hg-btn--sm" data-route href="' + HG.esc(far ? l.rd : l.rw) + '" target="_blank" rel="noopener">' + HG.icon(far ? 'car' : 'walk', 16) + ' ' + HG.esc(HG.t('map_route')) + '</a>' +
        '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-fav aria-pressed="' + fav + '">' + HG.icon('heart', 16) + ' <span>' + HG.esc(HG.t(fav ? 'map_added' : 'map_add_plan')) + '</span></button>' +
        (l.web ? '<a class="hg-btn hg-btn--ghost hg-btn--sm" href="' + HG.esc(l.web) + '" target="_blank" rel="noopener">Web ↗</a>' : '') +
        (l.ig ? '<a class="hg-btn hg-btn--ghost hg-btn--sm" href="https://instagram.com/' + HG.esc(l.ig) + '" target="_blank" rel="noopener">@' + HG.esc(l.ig) + '</a>' : '') +
        '</div><p class="hg-card__est">' + HG.esc(HG.t(l.medido ? 'map_measured' : 'map_estimated')) + ' · Google Maps ↗</p>';
      card.hidden = false;
      card.querySelector('.hg-card__close').onclick = closeCard;
      card.querySelector('[data-route]').onclick = function () { HG.track('hg_route', { lugar: l.id, modo: far ? 'carro' : 'pie' }); };
      var fb = card.querySelector('[data-fav]');
      fb.onclick = function () {
        var on = HG.favs.toggle(l.id); fb.setAttribute('aria-pressed', on); fb.querySelector('span').textContent = HG.t(on ? 'map_added' : 'map_add_plan');
        HG.vibe(10); HG.track('hg_map_fav', { lugar: l.id, activo: on ? 1 : 0 });
      };
      HG.track('hg_map_place', { lugar: l.id, categoria: l.cat });
      if (fromList && window.innerWidth < 900) stage.scrollIntoView({ behavior: HG.reduce ? 'auto' : 'smooth', block: 'center' });
    }
    function openHotel() {
      closeRoute();
      card.style.setProperty('--pc', '#CDB371');
      card.innerHTML = '<button type="button" class="hg-card__close" aria-label="' + HG.esc(HG.t('map_close')) + '">' + HG.icon('x', 16) + '</button>' +
        '<div class="hg-card__cat">Haven</div><h3>' + HG.esc(H.n) + '</h3><p class="hg-card__zone">' + HG.esc(H.dir) + '</p><div style="display:grid;gap:10px;margin-top:16px">' +
        havenItems.map(function (l) {
          return '<button type="button" class="hg-item__btn" data-h="' + HG.esc(l.id) + '" style="border-top:1px solid rgba(205,179,113,.12)"><span class="hg-item__dot" style="background:#CDB371"></span><span class="hg-item__main"><strong class="hg-item__name">' + HG.esc(l.n.replace(' · Haven', '')) + '</strong><span class="hg-item__meta">' + HG.esc(l.desc).slice(0, 80) + '…</span></span></button>';
        }).join('') + '</div>';
      card.hidden = false;
      card.querySelector('.hg-card__close').onclick = closeCard;
      [].forEach.call(card.querySelectorAll('[data-h]'), function (b) { b.onclick = function () { select(b.dataset.h); }; });
    }
    function closeCard() { card.hidden = true; if (st.sel && pins[st.sel]) pins[st.sel].classList.remove('is-sel'); st.sel = null; closeRoute(); }
    function closeRoute() { gRoute.innerHTML = ''; if (leafRoute) { leafRoute.remove(); leafRoute = null; } }
    function drawRoute(id) {
      gRoute.innerHTML = '';
      var g = pins[id], l = D.byId[id]; if (!l) return;
      if (leaf) { if (leafRoute) leafRoute.remove(); leafRoute = window.L.polyline([[H.lat, H.lng], [l.lat, l.lng]], { color: '#F7DFAE', weight: 2, dashArray: '4 6' }).addTo(leaf); }
      if (!g) return;
      var p = g.__p, mx = p[0] / 2 - p[1] * 0.18, my = p[1] / 2 + p[0] * 0.18;
      gRoute.append(s('path', { class: 'hg-route', d: 'M0,0 Q' + mx + ',' + my + ' ' + p[0] + ',' + p[1] }));
    }

    /* ---------- Mapa de calles (Leaflet bajo demanda) ---------- */
    var leaf = null, leafMarkers = {}, leafLive = null, leafRoute = null, leafBox = null;
    function loadLeaflet(cb) {
      if (window.L) return cb();
      var c = document.createElement('link'); c.rel = 'stylesheet'; c.href = cfg.leaflet.css; document.head.append(c);
      var j = document.createElement('script'); j.src = cfg.leaflet.js; j.onload = cb; j.onerror = function () { flash('—'); }; document.head.append(j);
    }
    function streets() {
      leafBox = HG.h('div', { class: 'hg-map__streets' }); stage.insertBefore(leafBox, stage.firstChild);
      var L = window.L;
      leaf = L.map(leafBox, { zoomControl: false, attributionControl: true, scrollWheelZoom: 'center', tap: true }).setView([H.lat, H.lng], 16);
      L.tileLayer(cfg.tiles, { attribution: cfg.attr, subdomains: 'abcd', maxZoom: 19, detectRetina: true }).addTo(leaf);
      (cfg.rings || [5, 10, 15]).forEach(function (m) { L.circle([H.lat, H.lng], { radius: rMin(m), color: '#CDB371', weight: 1, opacity: .35, dashArray: '2 6', fill: false, interactive: false }).addTo(leaf); });
      leafLive = L.circle([H.lat, H.lng], { radius: curR, color: '#CDB371', weight: 1.5, fillColor: '#CDB371', fillOpacity: .06, interactive: false }).addTo(leaf);
      L.marker([H.lat, H.lng], { icon: L.divIcon({ className: '', html: '<div style="width:22px;height:22px;border-radius:50%;background:#CDB371;box-shadow:0 0 0 6px rgba(205,179,113,.25),0 0 20px #CDB371"></div>', iconSize: [22, 22], iconAnchor: [11, 11] }) }).addTo(leaf).on('click', openHotel);
      places.forEach(function (l) {
        var mk = L.circleMarker([l.lat, l.lng], { radius: 7, color: '#191311', weight: 2, fillColor: HG.catColor(l.cat), fillOpacity: .95 }).addTo(leaf);
        mk.bindTooltip(l.n, { direction: 'top', offset: [0, -6] });
        mk.on('click', function () { select(l.id); });
        leafMarkers[l.id] = mk;
      });
      render();
    }
    [].forEach.call(root.querySelectorAll('.hg-seg button'), function (b) {
      b.addEventListener('click', function () {
        var v = b.dataset.view; if (v === st.view) return;
        [].forEach.call(root.querySelectorAll('.hg-seg button'), function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        st.view = v; HG.track('hg_map_view', { vista: v });
        if (v === 'streets') {
          canvas.style.display = 'none';
          if (leaf) { leafBox.style.display = ''; leaf.invalidateSize(); if (st.sel) drawRoute(st.sel); }
          else loadLeaflet(streets);
        } else { canvas.style.display = ''; if (leafBox) leafBox.style.display = 'none'; }
      });
    });

    /* ---------- Desde el planificador: "ver en el mapa" ---------- */
    document.addEventListener('hg:focus', function (e) {
      var id = e.detail && e.detail.id; if (!D.byId[id]) return;
      root.scrollIntoView({ behavior: HG.reduce ? 'auto' : 'smooth', block: 'start' });
      setTimeout(function () { if (D.byId[id].min > st.min && D.byId[id].min <= 25) setMin(Math.min(25, D.byId[id].min)); select(id, true); }, 450);
    });

    /* ---------- Inicio ---------- */
    st.k = K0; measure(); apply(); setMin(st.min); HG.fillRange(range);
    if (st.cat) [].forEach.call(root.querySelectorAll('.hg-map__controls .hg-chip'), function (x) { x.setAttribute('aria-pressed', x.dataset.cat === st.cat ? 'true' : 'false'); });
    window.addEventListener('resize', function () { measure(); apply(); if (leaf) leaf.invalidateSize(); });
    // Entrada: el anillo crece de 0 a 10 min
    if (!HG.reduce) { curR = 0; live.setAttribute('r', 0); setTimeout(function () { animateRing(rMin(st.min)); }, 250); }
  };
})();
