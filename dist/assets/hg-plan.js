/* 02 · Tu Medellín en X días
   Asistente táctil (contador de días, fichas, chips, deslizador de ritmo, fechas) → itinerario por reglas:
   horarios por franja, días de cierre, brunch del segundo domingo, salidas largas solo con 3+ días,
   cercanía para la noche, favoritos del mapa. Resultado editable: arrastrar, cambiar, quitar, añadir;
   kilómetros y pasos en vivo; copiar, WhatsApp, enlace y reserva con fechas. */
(function () {
  'use strict';
  var HG = window.HG; if (!HG) return;

  var SLOTS = { 1: ['manana', 'almuerzo', 'cena'], 2: ['manana', 'almuerzo', 'tarde', 'atardecer', 'cena'], 3: ['manana', 'almuerzo', 'tarde', 'atardecer', 'cena', 'noche'] };
  // Qué categorías y momentos sirven para cada franja
  var FIT = {
    manana: { cats: ['cafe', 'cultura', 'parques', 'compras', 'bienestar', 'salida'], mom: ['manana', 'desayuno', 'dia-completo'] },
    almuerzo: { cats: ['comer'], mom: ['almuerzo', 'brunch'] },
    tarde: { cats: ['cultura', 'parques', 'compras', 'cafe', 'bienestar', 'haven', 'comer'], mom: ['tarde'] },
    atardecer: { cats: ['noche', 'parques', 'haven'], mom: ['atardecer'] },
    cena: { cats: ['comer', 'haven'], mom: ['cena'] },
    noche: { cats: ['noche', 'haven'], mom: ['noche', 'madrugada'] }
  };
  // Imperdibles de la ciudad (orden de prioridad que siguen las mejores guías)
  var ANCHOR = { 'comuna-13-graffitour': 9, 'plaza-botero-museo-antioquia': 8, 'metrocable-parque-arvi': 7, 'guatape-piedra-penol': 7, 'pueblito-paisa-nutibara': 5, 'jardin-botanico': 5, 'museo-casa-de-la-memoria': 5, 'mamm': 4, 'parque-explora': 4, 'laureles-la-70': 4, 'santa-elena-silleteros': 4, 'tour-cafe-finca': 4, 'museo-el-castillo': 3, 'parapente-san-felix': 3 };
  var ICON_SLOT = { manana: 'sun', almuerzo: 'comer', tarde: 'leaf', atardecer: 'sun', cena: 'comer', noche: 'moon', dia: 'salida' };

  HG.mods.plan = function (root) {
    var D = HG.data(), cfg = D.cfg, H = D.hotel;
    var form = root.querySelector('.hg-wizard');
    var steps = [].slice.call(root.querySelectorAll('.hg-step'));
    var bar = root.querySelector('.hg-wizard__progress span');
    var countEl = root.querySelector('.hg-wizard__count');
    var btnBack = root.querySelector('[data-nav="-1"]'), btnNext = root.querySelector('[data-nav="1"]'), btnBuild = root.querySelector('[data-build]');
    var result = root.querySelector('.hg-result');
    var titleVar = root.querySelector('[data-hg-var="n"]');
    var cur = 0, model = null, opts = null;

    /* ======================= Asistente ======================= */
    function go(i, back) {
      cur = Math.max(0, Math.min(steps.length - 1, i));
      steps.forEach(function (s, k) { s.classList.toggle('is-on', k === cur); s.classList.toggle('is-back', !!back); });
      bar.style.width = ((cur + 1) / steps.length * 100) + '%';
      countEl.textContent = HG.t('plan_step', { n: cur + 1, t: steps.length });
      btnBack.style.visibility = cur ? 'visible' : 'hidden';
      btnNext.hidden = cur === steps.length - 1; btnBuild.hidden = cur !== steps.length - 1;
      var f = steps[cur].querySelector('input:checked, button, input'); if (f && i !== 0) setTimeout(function () { try { f.focus({ preventScroll: true }); } catch (e) {} }, 60);
    }
    btnBack.addEventListener('click', function () { go(cur - 1, true); HG.vibe(4); });
    btnNext.addEventListener('click', function () { go(cur + 1); HG.vibe(4); });

    // Contador de días
    var daysIn = form.querySelector('input[name="dias"]'), daysOut = form.querySelector('.hg-stepper__val b'), daybar = [].slice.call(form.querySelectorAll('.hg-daybar i'));
    function setDays(n) {
      n = Math.max(1, Math.min(7, n)); daysIn.value = n; daysOut.textContent = n;
      daysOut.classList.remove('bump'); void daysOut.offsetWidth; daysOut.classList.add('bump');
      daybar.forEach(function (b, k) { b.classList.toggle('on', k < n); });
      if (titleVar) titleVar.textContent = n;
    }
    [].forEach.call(form.querySelectorAll('.hg-stepper__btn'), function (b) {
      b.addEventListener('click', function () { setDays(Number(daysIn.value) + Number(b.dataset.delta)); HG.vibe(6); });
    });
    // Deslizar sobre el número también cambia los días (gesto)
    var sx = null;
    daysOut.parentNode.addEventListener('pointerdown', function (e) { sx = e.clientX; });
    daysOut.parentNode.addEventListener('pointerup', function (e) { if (sx == null) return; var d = e.clientX - sx; if (Math.abs(d) > 30) setDays(Number(daysIn.value) + (d > 0 ? 1 : -1)); sx = null; });
    setDays(Number(daysIn.value));

    // Iconos de "con quién"
    [].forEach.call(form.querySelectorAll('.hg-tile__ico'), function (i) {
      var w = (i.className.match(/hg-ico-(\w+)/) || [])[1]; i.innerHTML = HG.icon(w, 22); i.style.display = 'grid'; i.style.placeItems = 'center';
    });
    [].forEach.call(form.querySelectorAll('.hg-tile input'), function (r) {
      r.addEventListener('change', function () { HG.vibe(6); setTimeout(function () { if (cur === 1) go(2); }, 260); });
    });
    [].forEach.call(form.querySelectorAll('.hg-chip input'), function (c) { c.addEventListener('change', function () { HG.vibe(5); }); });

    // Ritmo
    var pace = form.querySelector('input[name="ritmo"]'), paceLabels = [].slice.call(form.querySelectorAll('.hg-pace__labels span'));
    function paceUI() { HG.fillRange(pace); paceLabels.forEach(function (s, k) { s.classList.toggle('on', k === pace.value - 1); }); }
    pace.addEventListener('input', function () { paceUI(); HG.vibe(4); }); paceUI();

    var dateIn = form.querySelector('input[name="llegada"]'); dateIn.min = HG.iso(new Date());

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      opts = read(); model = build(opts); show(true);
      HG.track('hg_plan_built', { dias: opts.dias, con: opts.con, intereses: opts.ints.join('|'), ritmo: opts.ritmo, con_fechas: opts.llegada ? 1 : 0 });
    });
    function read() {
      var ints = [].map.call(form.querySelectorAll('input[name="int"]:checked'), function (i) { return i.value; });
      var con = (form.querySelector('input[name="con"]:checked') || {}).value || 'pareja';
      return { dias: Number(daysIn.value), con: con, ints: ints.length ? ints : ['comer'], ritmo: Number(pace.value), llegada: dateIn.value || '' };
    }

    /* ======================= Motor de itinerario ======================= */
    function dateFor(o, d) { return o.llegada ? HG.addDays(o.llegada, d) : ''; }
    function dowOf(iso) { return iso ? new Date(iso + 'T12:00:00').getDay() : null; }
    function isOpen(l, dow, iso) {
      if (dow == null) return l.id !== 'haven-brunch-dominical'; // sin fechas no programamos el brunch mensual
      if (l.dias && l.dias.split(',').indexOf(String(dow)) === -1) return false;
      if (l.id === 'haven-brunch-dominical') { var dom = Number(iso.slice(8, 10)); return dow === 0 && dom >= 8 && dom <= 14; }
      return true;
    }
    function fits(l, slot) {
      var f = FIT[slot]; if (f.cats.indexOf(l.cat) === -1) return false;
      if (l.cat === 'salida') return slot === 'manana';
      return l.mom.some(function (m) { return f.mom.indexOf(m) > -1; });
    }
    function score(l, slot, ctx) {
      var o = ctx.o, sc = 0;
      var interest = o.ints.indexOf(l.cat) > -1 || (l.cat === 'haven' && (o.ints.indexOf('bienestar') > -1 && l.sub === 'spa' || o.ints.indexOf('noche') > -1 && l.sub !== 'spa'));
      if (interest) sc += 4;
      if (l.cat === 'comer' && o.ints.indexOf('comer') > -1) sc += (l.sub === 'autor' ? 2 : 0) + (l.precio >= 3 ? 1 : 0);
      if (l.ideal.indexOf(o.con) > -1) sc += 2;
      if (ctx.favs.indexOf(l.id) > -1) sc += 7;
      if (l.dest) sc += 1;
      var far = l.min > 25;
      if (ctx.type === 'explore' && slot === 'manana' && (ANCHOR[l.id] || (far && (l.cat === 'cultura' || l.cat === 'parques')))) sc += 3 + (ANCHOR[l.id] || 0) * 0.6;
      if (l.cat === 'salida') sc += (o.dias >= 3 ? 2 : -99) + (o.ritmo === 1 ? -2 : 0) + (o.ints.indexOf('parques') > -1 ? 2 : 0) + (l.id === 'tour-cafe-finca' && o.ints.indexOf('cafe') > -1 ? 4 : 0);
      if (l.id === 'parapente-san-felix' && o.con !== 'amigos' && o.ritmo < 3) sc -= 4;
      if (/atardecer|cena|noche/.test(slot)) sc -= l.min / 5; else sc -= l.min / 18;
      if (far && slot !== 'manana' && slot !== 'almuerzo' && slot !== 'tarde') sc -= 6;
      if (o.con === 'familia' && l.cat === 'noche') sc -= 6;
      if (o.con === 'negocios' && l.cat === 'salida') sc -= 4;
      if (l.estado === 'verificar') sc -= 1;
      return sc + ((hash(l.id + ctx.seed) % 100) / 100) * 1.4; // variedad estable
    }
    function hash(s) { var h = 0; for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) | 0; return Math.abs(h); }

    function candidates(slot, ctx, day) {
      var dow = dowOf(day.date);
      return D.lugares.filter(function (l) {
        if (ctx.used[l.id] || !fits(l, slot) || !isOpen(l, dow, day.date)) return false;
        if (ctx.type !== 'explore' && l.min > 25) return false;     // llegada y salida: cerca del hotel
        if (l.cat === 'salida' && ctx.type !== 'explore') return false;
        if (day.cats[l.cat] && l.cat !== 'comer' && l.cat !== 'haven') return false; // variedad dentro del día
        return true;
      }).map(function (l) { return { l: l, s: score(l, slot, ctx) }; }).sort(function (a, b) { return b.s - a.s; });
    }

    function build(o, seed) {
      var ctx = { o: o, used: {}, favs: HG.favs.get(), seed: seed || '' };
      var days = [];
      var slots = SLOTS[o.ritmo] || SLOTS[2];
      var salidaUsed = false;
      for (var d = 0; d < o.dias; d++) {
        var day = { date: dateFor(o, d), stops: [], cats: {} };
        ctx.type = d === 0 ? 'arrival' : (d === o.dias - 1 && o.dias > 2 ? 'last' : 'explore');
        if (o.dias === 2 && d === 1) ctx.type = 'explore';
        var skip = {};
        slots.forEach(function (slot) {
          if (skip[slot]) return;
          var pick = null;
          // Reglas fijas Haven
          if (d === 0 && (slot === 'atardecer' || (slot === 'cena' && slots.indexOf('atardecer') === -1))) pick = D.byId['haven-360-rooftop'];
          if (d === 0 && slot === 'tarde' && (o.ritmo === 1 || o.ints.indexOf('bienestar') > -1)) pick = D.byId['haven-wellness'];
          if (slot === 'almuerzo' && D.byId['haven-brunch-dominical'] && isOpen(D.byId['haven-brunch-dominical'], dowOf(day.date), day.date) && !ctx.used['haven-brunch-dominical']) pick = D.byId['haven-brunch-dominical'];
          if (pick && ctx.used[pick.id]) pick = null;
          if (!pick) {
            var c = candidates(slot, ctx, day);
            if (salidaUsed) c = c.filter(function (x) { return x.l.cat !== 'salida'; });
            pick = c.length ? c[0].l : null;
          }
          if (!pick) return;
          var stop = { id: pick.id, slot: slot };
          if (pick.cat === 'salida') {
            stop.slot = 'dia'; salidaUsed = true;
            ['almuerzo', 'tarde'].forEach(function (x) { skip[x] = true; });
            if ((pick.dur || 0) >= 9) skip.atardecer = true;
          } else if (slot === 'manana' && (pick.dur || 0) >= 4) skip.tarde = true;
          ctx.used[pick.id] = 1; day.cats[pick.cat] = 1; day.stops.push(stop);
        });
        // Noche con amigos o interés en vida nocturna, aunque el ritmo no incluya "noche"
        if (slots.indexOf('noche') === -1 && (o.con === 'amigos' || o.ints.indexOf('noche') > -1) && d < o.dias - 1) {
          var n = candidates('noche', ctx, day)[0]; if (n) { ctx.used[n.l.id] = 1; day.stops.push({ id: n.l.id, slot: 'noche' }); }
        }
        days.push(day);
      }
      // Swing Bar: una noche del viaje si no salió
      if (!ctx.used['haven-swing-bar'] && o.dias > 1) {
        var last = days[o.dias - 1]; if (last.stops.length < 6) last.stops.push({ id: 'haven-swing-bar', slot: 'noche' });
      }
      return { days: days, favsUsed: ctx.favs.filter(function (id) { return ctx.used[id]; }).length, seed: ctx.seed };
    }

    // Alternativa para una parada (mismo tipo de franja)
    function alternative(dayIdx, stopIdx) {
      var day = model.days[dayIdx], st = day.stops[stopIdx];
      var used = {}; model.days.forEach(function (d) { d.stops.forEach(function (s) { used[s.id] = 1; }); });
      var ctx = { o: opts, used: used, favs: HG.favs.get(), seed: String(Math.random()), type: dayIdx === 0 ? 'arrival' : 'explore' };
      var tmp = { date: day.date, cats: {} };
      day.stops.forEach(function (s, k) { if (k !== stopIdx && D.byId[s.id]) tmp.cats[D.byId[s.id].cat] = 1; });
      var slot = st.slot === 'dia' ? 'manana' : st.slot;
      var c = candidates(slot, ctx, tmp);
      if (!c.length) return null;
      var top = c.slice(0, Math.min(5, c.length));
      return top[Math.floor(Math.random() * top.length)].l.id;
    }

    /* ======================= Métricas ======================= */
    function legs(day) {
      var pts = [{ lat: H.lat, lng: H.lng, home: 1 }].concat(day.stops.map(function (s) { return D.byId[s.id]; }).filter(Boolean)).concat([{ lat: H.lat, lng: H.lng, home: 1 }]);
      var walkM = 0, list = [];
      for (var i = 1; i < pts.length; i++) {
        var m = HG.streetM(pts[i - 1], pts[i]);
        var isWalk = m <= 2200;
        if (isWalk) walkM += m;
        list.push({ m: m, walk: isWalk, min: isWalk ? Math.max(1, Math.round(m / (cfg.walk || 78))) : Math.round(m * 1.06 / 1000 / 22 * 60 + 4), fromHaven: !!pts[i - 1].home });
      }
      return { walkM: walkM, steps: Math.round(walkM / 1000 * (cfg.stepsKm || 1350) / 10) * 10, list: list };
    }

    /* ======================= Pintar resultado ======================= */
    var fmtDate = function (iso) {
      if (!iso) return '';
      return new Date(iso + 'T12:00:00').toLocaleDateString(HG.lang() === 'en' ? 'en-US' : 'es-CO', { weekday: 'short', day: 'numeric', month: 'short' });
    };
    function show(scroll) {
      result.hidden = false;
      var totalM = 0, totalS = 0, nStops = 0;
      var daysHtml = model.days.map(function (day, di) {
        var lg = legs(day); totalM += lg.walkM; totalS += lg.steps; nStops += day.stops.length;
        var items = day.stops.map(function (s, si) {
          var l = D.byId[s.id]; if (!l) return '';
          var leg = lg.list[si];
          var legTxt = leg ? (leg.walk ? HG.t('plan_leg_walk', { m: leg.min }) : HG.t('plan_leg_car', { m: leg.min })) + ' ' + HG.t(leg.fromHaven ? 'plan_from_haven' : 'plan_from_prev') : '';
          return '<li class="hg-stop' + (s.fresh ? ' is-new' : '') + '" data-id="' + HG.esc(l.id) + '" data-slot="' + s.slot + '" style="--pc:' + HG.catColor(l.cat) + '">' +
            '<span class="hg-stop__node" title="' + HG.esc(HG.t('plan_drag')) + '" aria-hidden="true">' + HG.icon(l.cat === 'haven' ? 'haven' : (ICON_SLOT[s.slot] === 'comer' ? 'comer' : l.cat), 16) + '</span>' +
            '<div class="hg-stop__body"><span class="hg-stop__slot">' + HG.esc(HG.t('slot_' + s.slot)) + (l.dur && s.slot !== 'almuerzo' && s.slot !== 'cena' ? ' · ' + String(l.dur).replace('.', HG.lang() === 'en' ? '.' : ',') + ' h' : '') + '</span>' +
            '<button type="button" class="hg-stop__name" data-focus>' + HG.esc(l.n) + '</button>' +
            '<p class="hg-stop__desc">' + HG.esc(l.desc) + '</p>' +
            '<span class="hg-stop__leg">' + HG.icon(leg && !leg.walk ? 'car' : 'walk', 14) + HG.esc(legTxt) + '</span></div>' +
            '<div class="hg-stop__tools"><button type="button" class="hg-ibtn" data-swap aria-label="' + HG.esc(HG.t('plan_swap')) + '" title="' + HG.esc(HG.t('plan_swap')) + '">' + HG.icon('swap', 16) + '</button>' +
            '<button type="button" class="hg-ibtn" data-remove aria-label="' + HG.esc(HG.t('plan_remove')) + '" title="' + HG.esc(HG.t('plan_remove')) + '">' + HG.icon('x', 16) + '</button></div></li>';
        }).join('');
        return '<article class="hg-day" data-day="' + di + '"><div class="hg-day__head"><h3>' + HG.esc(HG.t('plan_day', { n: di + 1 })) + (day.date ? '<small>' + HG.esc(fmtDate(day.date)) + '</small>' : '') + '</h3>' +
          '<div class="hg-day__meter"><span><b data-km>' + HG.num(lg.walkM / 1000, 1) + '</b> ' + HG.esc(HG.t('plan_km')) + '</span><span><b>' + HG.num(lg.steps) + '</b> ' + HG.esc(HG.t('plan_steps')) + '</span></div></div>' +
          '<ol class="hg-stops">' + items + '</ol><button type="button" class="hg-day__add" data-add>+ ' + HG.esc(HG.t('plan_add_slot')) + '</button></article>';
      }).join('');
      model.days.forEach(function (d) { d.stops.forEach(function (s) { delete s.fresh; }); });

      // Eventos de la ciudad en esas fechas
      var evHtml = '';
      if (opts.llegada) {
        var months = {}; for (var i = 0; i < opts.dias; i++) months[Number(HG.addDays(opts.llegada, i).slice(5, 7))] = 1;
        var evs = D.eventos.filter(function (e) { return months[e.mes]; });
        if (evs.length) evHtml = '<p class="hg-lede" style="margin:18px 0 0">' + HG.icon('cal', 16) + ' <b style="color:var(--hg-champagne)">' + HG.esc(HG.t('plan_events')) + ':</b> ' + evs.map(function (e) { return HG.esc(e.n) + (e.dur && HG.lang() !== 'en' ? ' <small style="color:var(--hg-faint)">(' + HG.esc(e.dur) + ')</small>' : ''); }).join(' · ') + '</p>';
      }
      var favNote = model.favsUsed ? '<p class="hg-lede" style="margin:10px 0 0;font-size:14px">' + HG.icon('heart', 14) + ' ' + HG.esc(HG.t('plan_favs_used', { n: model.favsUsed })) + '</p>' : '';

      var inDate = opts.llegada || HG.tomorrow();
      result.innerHTML =
        '<div class="hg-summary"><div><p class="hg-eyebrow">' + HG.esc(HG.t('plan_total')) + '</p>' + evHtml + favNote + '</div>' +
        '<div class="hg-summary__nums"><div><b data-n="days">0</b><span>' + HG.esc(HG.t('plan_daysw')) + '</span></div><div><b data-n="stops">0</b><span>' + HG.esc(HG.t('plan_stops')) + '</span></div><div><b data-n="km">0</b><span>' + HG.esc(HG.t('plan_km')) + '</span></div><div><b data-n="steps">0</b><span>' + HG.esc(HG.t('plan_steps')) + '</span></div></div></div>' +
        '<div class="hg-days">' + daysHtml + '</div>' +
        '<div class="hg-share">' +
        '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-copy>' + HG.icon('copy', 16) + ' ' + HG.esc(HG.t('plan_copy')) + '</button>' +
        '<a class="hg-btn hg-btn--ghost hg-btn--sm" data-wa target="_blank" rel="noopener">' + HG.icon('wa', 16) + ' WhatsApp</a>' +
        '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-link>' + HG.icon('link', 16) + ' ' + HG.esc(HG.t('plan_link')) + '</button>' +
        '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-reset>' + HG.esc(HG.t('plan_rebuild')) + '</button>' +
        '<div class="hg-share__book"><input type="date" aria-label="' + HG.esc(HG.t('plan_checkin')) + '" value="' + inDate + '" min="' + HG.iso(new Date()) + '">' +
        '<button type="button" class="hg-btn hg-btn--gold" data-book>' + HG.esc(HG.t('plan_book')) + '<span class="hg-btn__arrow" aria-hidden="true">' + HG.icon('plus', 16).replace('M12 5v14M5 12h14', 'M5 12h14M13 6l6 6-6 6') + '</span></button></div></div>';

      HG.countTo(result.querySelector('[data-n="days"]'), opts.dias);
      HG.countTo(result.querySelector('[data-n="stops"]'), nStops);
      HG.countTo(result.querySelector('[data-n="km"]'), totalM / 1000, 1, 900);
      HG.countTo(result.querySelector('[data-n="steps"]'), Math.round(totalS / 10) * 10, 0, 1100);
      result.querySelector('[data-wa]').href = HG.waShare(text());
      wire();
      saveHash();
      if (scroll) { form.hidden = true; result.scrollIntoView({ behavior: HG.reduce ? 'auto' : 'smooth', block: 'start' }); }
    }

    function text() {
      var lines = [HG.t('wa_intro') + ' · ' + opts.dias + ' ' + HG.t('plan_daysw')];
      model.days.forEach(function (d, i) {
        lines.push(''); lines.push(HG.t('plan_day', { n: i + 1 }) + (d.date ? ' · ' + fmtDate(d.date) : ''));
        d.stops.forEach(function (s) { var l = D.byId[s.id]; if (l) lines.push('• ' + HG.t('slot_' + s.slot) + ' — ' + l.n + (l.min <= 25 ? ' (' + l.min + ' min ' + HG.t('map_walk') + ')' : '')); });
      });
      lines.push(''); lines.push(HG.t('plan_made_with') + ' ' + location.href.split('#')[0] + '#' + hashValue());
      return lines.join('\n');
    }
    function hashValue() {
      var o = { d: opts.dias, c: opts.con, i: opts.ints, r: opts.ritmo, l: opts.llegada, p: model.days.map(function (d) { return d.stops.map(function (s) { return s.id + '~' + s.slot; }); }) };
      try { return 'hgplan=' + btoa(unescape(encodeURIComponent(JSON.stringify(o)))).replace(/=+$/, ''); } catch (e) { return ''; }
    }
    function saveHash() { try { history.replaceState(null, '', '#' + hashValue()); } catch (e) {} }
    function loadHash() {
      var m = location.hash.match(/hgplan=([A-Za-z0-9+/]+)/); if (!m) return false;
      try {
        var o = JSON.parse(decodeURIComponent(escape(atob(m[1]))));
        opts = { dias: o.d, con: o.c, ints: o.i, ritmo: o.r, llegada: o.l || '' };
        model = { days: o.p.map(function (stops, i) { return { date: dateFor(opts, i), stops: stops.map(function (x) { var p = x.split('~'); return { id: p[0], slot: p[1] }; }).filter(function (s) { return D.byId[s.id]; }) }; }), favsUsed: 0 };
        setDays(opts.dias); show(false); form.hidden = true; return true;
      } catch (e) { return false; }
    }

    /* ======================= Interacción del resultado ======================= */
    function wire() {
      result.querySelector('[data-copy]').onclick = function () { HG.copy(text()); HG.track('hg_plan_share', { canal: 'copiar' }); };
      result.querySelector('[data-wa]').onclick = function () { HG.track('hg_plan_share', { canal: 'whatsapp' }); };
      result.querySelector('[data-link]').onclick = function () { HG.copy(location.href.split('#')[0] + '#' + hashValue()); HG.track('hg_plan_share', { canal: 'enlace' }); };
      result.querySelector('[data-reset]').onclick = function () { result.hidden = true; form.hidden = false; go(0, true); root.scrollIntoView({ behavior: HG.reduce ? 'auto' : 'smooth', block: 'start' }); try { history.replaceState(null, '', location.pathname + location.search); } catch (e) {} };
      result.querySelector('[data-book]').onclick = function () { var v = result.querySelector('.hg-share__book input').value || HG.tomorrow(); HG.book(v, opts.dias, 'planificador'); };

      [].forEach.call(result.querySelectorAll('.hg-day'), function (dayEl) {
        var di = Number(dayEl.dataset.day);
        dayEl.querySelector('[data-add]').onclick = function () {
          var day = model.days[di], tmp = { id: '', slot: 'tarde' };
          day.stops.push(tmp);
          var alt = alternative(di, day.stops.length - 1);
          if (!alt) { day.stops.pop(); return; }
          tmp.id = alt; tmp.fresh = 1; HG.vibe(8); show(false);
        };
        [].forEach.call(dayEl.querySelectorAll('.hg-stop'), function (li, si) {
          li.querySelector('[data-swap]').onclick = function () {
            var alt = alternative(di, si); if (!alt) return;
            model.days[di].stops[si] = { id: alt, slot: model.days[di].stops[si].slot === 'dia' && D.byId[alt].cat !== 'salida' ? 'manana' : (D.byId[alt].cat === 'salida' ? 'dia' : model.days[di].stops[si].slot), fresh: 1 };
            HG.vibe(8); HG.track('hg_plan_swap', { lugar: alt }); show(false);
          };
          li.querySelector('[data-remove]').onclick = function () {
            li.style.opacity = 0; li.style.transform = 'translateX(30px)';
            setTimeout(function () { model.days[di].stops.splice(si, 1); show(false); }, 220); HG.vibe(6);
          };
          li.querySelector('[data-focus]').onclick = function () {
            var id = li.dataset.id;
            if (document.querySelector('[data-hg="mapa"]')) document.dispatchEvent(new CustomEvent('hg:focus', { detail: { id: id } }));
            else { var l = D.byId[id]; window.open(l.min > 25 ? l.rd : l.rw, '_blank', 'noopener'); }
          };
          dragify(li);
        });
      });
    }

    /* Arrastrar y soltar (ratón y táctil) desde el círculo de cada parada */
    function dragify(li) {
      var handle = li.querySelector('.hg-stop__node');
      handle.addEventListener('pointerdown', function (e) {
        e.preventDefault();
        var r = li.getBoundingClientRect(), offY = e.clientY - r.top, offX = e.clientX - r.left;
        var ghost = li.cloneNode(true); ghost.classList.add('is-drag'); ghost.style.width = r.width + 'px'; ghost.style.left = r.left + 'px'; ghost.style.top = r.top + 'px';
        var wrap = HG.h('div', { class: 'hg hg-ghost' }); wrap.append(ghost); document.body.append(wrap); li.classList.add('is-ghost'); HG.vibe(12);
        try { handle.setPointerCapture(e.pointerId); } catch (x) {}
        var raf = null, lastY = e.clientY;
        function move(ev) {
          ghost.style.left = (ev.clientX - offX) + 'px'; ghost.style.top = (ev.clientY - offY) + 'px'; lastY = ev.clientY;
          var under = document.elementFromPoint(ev.clientX, ev.clientY);
          var target = under && under.closest('.hg-stop:not(.is-drag)'), list = under && under.closest('.hg-stops');
          if (target && target !== li) {
            var tr = target.getBoundingClientRect();
            target.parentNode.insertBefore(li, ev.clientY < tr.top + tr.height / 2 ? target : target.nextSibling);
          } else if (list && !target) list.append(li);
          // Desplazar la página si llega al borde
          cancelAnimationFrame(raf);
          (function edge() { var v = lastY < 80 ? -12 : (lastY > innerHeight - 80 ? 12 : 0); if (v) { scrollBy(0, v); raf = requestAnimationFrame(edge); } })();
        }
        function end() {
          cancelAnimationFrame(raf);
          handle.removeEventListener('pointermove', move); handle.removeEventListener('pointerup', end); handle.removeEventListener('pointercancel', end);
          wrap.remove(); li.classList.remove('is-ghost'); HG.vibe(6);
          // Reconstruir el modelo desde el orden visible
          model.days.forEach(function (d, di) {
            var ol = result.querySelector('.hg-day[data-day="' + di + '"] .hg-stops');
            d.stops = [].map.call(ol.querySelectorAll('.hg-stop'), function (x) { return { id: x.dataset.id, slot: x.dataset.slot }; });
          });
          HG.track('hg_plan_reorder', {}); show(false);
        }
        handle.addEventListener('pointermove', move); handle.addEventListener('pointerup', end); handle.addEventListener('pointercancel', end);
      });
    }

    /* Inicio */
    go(0);
    if (!loadHash()) {
      var aud = (location.search.match(/[?&]con=(\w+)/) || [])[1];
      if (aud) { var r = form.querySelector('input[name="con"][value="' + aud + '"]'); if (r) r.checked = true; }
    }
  };
})();
