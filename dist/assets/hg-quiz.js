/* 03 · ¿Qué suite va contigo?
   Cinco preguntas táctiles (tarjetas con foto, iconos, selección múltiple, dos deslizadores con número gigante)
   → afinidad por suite con los datos reales (m², huéspedes, jacuzzi, balcón, cocina, sala, nivel).
   Resultado: tarjeta 3D que se inclina, galería deslizable, anillo de afinidad, reserva con noches, compartir. */
(function () {
  'use strict';
  var HG = window.HG; if (!HG) return;
  var UP = '/wp-content/uploads/';

  function Q(lang) {
    var es = lang !== 'en';
    return [
      { id: 'motivo', type: 'cards', title: es ? '¿Qué te trae a Medellín?' : 'What brings you to Medellín?', sub: es ? 'Elige una' : 'Pick one', opts: [
        { v: 'celebrar', ico: 'glass', img: UP + '2026/06/presidential-suite-room-jacuzzi-lounge-area-1-768x512.webp', t: es ? 'Celebrar algo' : 'A celebration', d: es ? 'Aniversario, cumpleaños, una sorpresa' : 'Anniversary, birthday, a surprise' },
        { v: 'descansar', ico: 'leaf', img: UP + '2026/07/masaje-relajante-683x1024.webp', t: es ? 'Desconectar' : 'Unplug', d: es ? 'Dormir bien y bajar el ritmo' : 'Sleep well and slow down' },
        { v: 'trabajo', ico: 'brief', img: UP + '2026/07/swing-bar-768x511.webp', t: es ? 'Trabajo' : 'Work', d: es ? 'Reuniones y algo de ciudad' : 'Meetings and a bit of the city' },
        { v: 'fiesta', ico: 'moon', img: UP + '2026/06/hotel-haven-rooftop-360-with-friends-3-768x512.webp', t: es ? 'Salir y celebrar' : 'Go out', d: es ? 'Provenza, Lleras y rooftop' : 'Provenza, Lleras and the rooftop' }
      ] },
      { id: 'grupo', type: 'cards', compact: 1, title: es ? '¿Cuántos vienen?' : 'How many of you?', sub: es ? 'Elige una' : 'Pick one', opts: [
        { v: 1, ico: 'solo', t: es ? 'Solo yo' : 'Just me' },
        { v: 2, ico: 'pareja', t: es ? 'Dos' : 'Two' },
        { v: 4, ico: 'amigos', t: es ? '3 a 4' : '3 to 4' },
        { v: 6, ico: 'familia', t: es ? '5 o más' : '5 or more' }
      ] },
      { id: 'must', type: 'multi', compact: 1, max: 2, title: es ? '¿Qué no puede faltar?' : 'What is a must?', sub: es ? 'Hasta dos' : 'Up to two', opts: [
        { v: 'jac', ico: 'jacuzzi', t: es ? 'Jacuzzi' : 'Jacuzzi' },
        { v: 'bal', ico: 'balcon', t: es ? 'Balcón' : 'Balcony' },
        { v: 'coc', ico: 'cocina', t: es ? 'Cocina' : 'Kitchen' },
        { v: 'sala', ico: 'sala', t: es ? 'Sala para recibir' : 'Living room' },
        { v: 'm2', ico: 'area', t: es ? 'Mucho espacio' : 'Lots of space' }
      ] },
      { id: 'noches', type: 'range', min: 1, max: 10, val: 3, title: es ? '¿Cuántas noches?' : 'How many nights?', unit: es ? ['noche', 'noches'] : ['night', 'nights'],
        caption: function (v) { return v >= 5 ? (es ? 'Una cocina y una sala hacen la diferencia.' : 'A kitchen and a living room make the difference.') : v >= 3 ? (es ? 'El tiempo justo para vivir El Poblado.' : 'Just enough to live El Poblado.') : (es ? 'Corto e intenso.' : 'Short and sweet.'); } },
      { id: 'nivel', type: 'range', min: 1, max: 4, val: 2, glass: 1, title: es ? '¿Qué tanto quieres consentirte?' : 'How much do you want to indulge?', ticks: es ? ['Lo esencial', 'Un gusto', 'Mucho', 'Todo'] : ['Essentials', 'A treat', 'A lot', 'Everything'],
        caption: function (v) { return (es ? ['Lo esencial, bien hecho.', 'Un gusto que se nota.', 'Espacio, jacuzzi y vista.', 'Todo, sin pensarlo dos veces.'] : ['Essentials, done right.', 'A treat you will notice.', 'Space, jacuzzi and a view.', 'Everything, no second thoughts.'])[v - 1]; } }
    ];
  }

  function scoreSuite(s, a) {
    var sc = 50;
    if (a.grupo > s.pax) sc -= 80;
    else sc -= Math.max(0, s.pax - a.grupo - 2) * 3; // demasiado grande para pocos
    switch (a.motivo) {
      case 'celebrar': sc += (s.jac ? 16 : -6) + s.nivel * 5 + (s.bal ? 4 : 0); break;
      case 'descansar': sc += (s.jac ? 10 : 0) + (s.bal ? 6 : 0) + (s.sala ? 3 : 0); break;
      case 'trabajo': sc += (s.sala ? 9 : -4) + (s.coc ? 4 : 0) - Math.abs(s.nivel - 2) * 3; break;
      case 'fiesta': sc += (s.sala ? 10 : 0) + Math.min(s.pax, 8) * 1.5 + (s.bal ? 6 : 0) + (s.jac ? 6 : 0); break;
    }
    (a.must || []).forEach(function (m) {
      if (m === 'm2') sc += Math.min(20, s.m2 / 7) - 4; else sc += s[m] ? 16 : -14;
    });
    if (a.noches >= 4) sc += (s.coc ? 12 : -3) + (s.sala ? 5 : 0);
    sc -= Math.abs(s.nivel - a.nivel) * 13;
    sc += s.m2 / 40;
    return sc;
  }

  HG.mods.quiz = function (root) {
    var D = HG.data(), stage = root.querySelector('.hg-quiz__stage');
    var qs = Q(HG.lang()), idx = 0, ans = { must: [] };
    var suites = (D.suites || []).filter(function (s) { return s && s.n; });
    if (!suites.length) return;

    root.querySelector('.hg-quiz__start').addEventListener('click', function () { HG.track('hg_quiz_start', {}); ask(0); });

    function dots() { return '<div class="hg-q__dots" aria-hidden="true">' + qs.map(function (_, k) { return '<i class="' + (k === idx ? 'on' : k < idx ? 'done' : '') + '"></i>'; }).join('') + '</div>'; }
    function ask(i, back) {
      idx = i; var q = qs[i];
      var old = stage.querySelector('.hg-q');
      var paint = function () {
        var html = '<div class="hg-q is-entering" role="group" aria-label="' + HG.esc(q.title) + '"><div class="hg-q__top">' + dots() +
          (i ? '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-back>' + HG.esc(HG.t('plan_back')) + '</button>' : '') + '</div>' +
          '<h3>' + HG.esc(q.title) + '</h3>' + (q.sub ? '<p class="hg-q__sub">' + HG.esc(q.sub) + '</p>' : '');
        if (q.type === 'cards' || q.type === 'multi') {
          html += '<div class="hg-q__opts' + (q.compact ? ' hg-q__opts--compact' : '') + '">' + q.opts.map(function (o, k) {
            var on = q.type === 'multi' ? ans.must.indexOf(o.v) > -1 : ans[q.id] === o.v;
            return '<button type="button" class="hg-opt' + (o.img ? '' : ' noimg') + '" data-v="' + o.v + '" aria-pressed="' + on + '">' +
              (o.img ? '<img src="' + HG.esc(o.img) + '" alt="" loading="lazy" onerror="this.remove();this.parentNode&&this.parentNode.classList.add(\'noimg\')">' : '') +
              '<span class="hg-opt__ico">' + HG.icon(o.ico, 22) + '</span><span class="hg-opt__key">' + (k + 1) + '</span>' +
              '<span class="hg-opt__txt"><b>' + HG.esc(o.t) + '</b>' + (o.d ? '<span>' + HG.esc(o.d) + '</span>' : '') + '</span></button>';
          }).join('') + '</div>';
          if (q.type === 'multi') html += '<div class="hg-q__next"><button type="button" class="hg-btn hg-btn--gold" data-next>' + HG.esc(HG.t('plan_next')) + '<span class="hg-btn__arrow">' + HG.icon('plus', 16).replace('M12 5v14M5 12h14', 'M5 12h14M13 6l6 6-6 6') + '</span></button></div>';
        } else {
          var v = ans[q.id] || q.val;
          html += '<div class="hg-q__big"><b data-big>' + v + '</b><span data-cap>' + HG.esc(q.caption(v)) + '</span>' +
            (q.glass ? '<svg class="hg-glass" viewBox="0 0 64 110" aria-hidden="true"><defs><clipPath id="hgcl"><path d="M8 6h48l-5 48a19 19 0 0 1-38 0Z"/></clipPath></defs><g clip-path="url(#hgcl)"><rect class="liquid" x="0" y="0" width="64" height="70" fill="#CDB371" style="transform:translateY(' + (60 - v * 13) + 'px)"/></g><path d="M8 6h48l-5 48a19 19 0 0 1-38 0ZM32 73v28M18 104h28" fill="none" stroke="#F7DFAE" stroke-width="2"/></svg>' : '') + '</div>' +
            '<div class="hg-q__slider"><input type="range" min="' + q.min + '" max="' + q.max + '" step="1" value="' + v + '" aria-label="' + HG.esc(q.title) + '">' +
            '<div class="hg-q__ticks">' + (q.ticks ? q.ticks.map(function (t) { return '<span>' + HG.esc(t) + '</span>'; }).join('') : '<span>' + q.min + '</span><span>' + q.max + '+</span>') + '</div></div>' +
            '<div class="hg-q__next"><button type="button" class="hg-btn hg-btn--gold" data-next>' + HG.esc(i === qs.length - 1 ? HG.t('quiz_result') : HG.t('plan_next')) + '<span class="hg-btn__arrow">' + HG.icon('plus', 16).replace('M12 5v14M5 12h14', 'M5 12h14M13 6l6 6-6 6') + '</span></button></div>';
        }
        stage.innerHTML = html + '</div>';
        wire(q);
      };
      if (old && !HG.reduce) { old.classList.add('is-leaving'); setTimeout(paint, 300); } else paint();
    }

    function wire(q) {
      var el = stage.querySelector('.hg-q');
      var back = el.querySelector('[data-back]'); if (back) back.onclick = function () { ask(idx - 1, true); };
      var next = el.querySelector('[data-next]');
      if (q.type === 'cards' || q.type === 'multi') {
        var optEls = [].slice.call(el.querySelectorAll('.hg-opt'));
        optEls.forEach(function (b) {
          tilt(b, 8);
          b.onclick = function () {
            var v = isNaN(b.dataset.v) ? b.dataset.v : Number(b.dataset.v);
            b.classList.remove('is-picked'); void b.offsetWidth; b.classList.add('is-picked'); HG.vibe(8);
            if (q.type === 'multi') {
              var k = ans.must.indexOf(v);
              if (k > -1) ans.must.splice(k, 1); else { ans.must.push(v); if (ans.must.length > q.max) ans.must.shift(); }
              optEls.forEach(function (x) { x.setAttribute('aria-pressed', ans.must.indexOf(x.dataset.v) > -1); });
            } else {
              ans[q.id] = v; optEls.forEach(function (x) { x.setAttribute('aria-pressed', x === b); });
              setTimeout(function () { advance(); }, 320);
            }
          };
        });
        el.onkeydown = function (e) { var n = Number(e.key); if (n >= 1 && n <= optEls.length) optEls[n - 1].click(); };
        el.tabIndex = -1; try { el.focus({ preventScroll: true }); } catch (x) {}
      } else {
        var r = el.querySelector('input[type=range]'), big = el.querySelector('[data-big]'), cap = el.querySelector('[data-cap]'), liq = el.querySelector('.liquid');
        var upd = function () {
          var v = Number(r.value); ans[q.id] = v; HG.fillRange(r);
          big.textContent = v; cap.textContent = q.caption(v);
          if (liq) liq.style.transform = 'translateY(' + (60 - v * 13) + 'px)';
        };
        r.addEventListener('input', function () { upd(); HG.vibe(4); }); upd();
      }
      if (next) next.onclick = advance;
    }
    function advance() { if (idx < qs.length - 1) ask(idx + 1); else reveal(); }

    function reveal() {
      var a = { motivo: ans.motivo || 'descansar', grupo: ans.grupo || 2, must: ans.must, noches: ans.noches || 3, nivel: ans.nivel || 2 };
      var ranked = suites.map(function (s) { return { s: s, sc: scoreSuite(s, a) }; }).sort(function (x, y) { return y.sc - x.sc; });
      var best = ranked[0], hi = best.sc, lo = ranked[ranked.length - 1].sc;
      var pct = function (sc) { return Math.round(62 + 35 * (sc - lo) / Math.max(1, hi - lo)); };
      var second = ranked.filter(function (r) { return r.s.cat !== best.s.cat && r.sc > hi - 60; })[0] || ranked[1];
      var s = best.s, p = Math.min(97, pct(best.sc) + 1);
      var imgs = (s.img && s.img.length ? s.img : []).slice(0, 5);
      var catName = { penthouse: 'Penthouse', presidencial: HG.lang() === 'en' ? 'Presidential' : 'Presidencial', 'suite-lujo': HG.lang() === 'en' ? 'Luxury suite' : 'Suite de lujo', habitacion: HG.lang() === 'en' ? 'Room' : 'Habitación' }[s.cat] || '';
      var spec = function (ok, ico, label) { return ok ? '<div>' + HG.icon(ico, 18) + HG.esc(label) + '</div>' : ''; };
      var C = 2 * Math.PI * 46;

      stage.innerHTML = '<div class="hg-res">' +
        '<div class="hg-res__card" tabindex="0" aria-label="' + HG.esc(s.n) + '"><div class="hg-res__slides">' + (imgs.length ? imgs.map(function (u) { return '<img src="' + HG.esc(u) + '" alt="' + HG.esc(s.n) + '" loading="lazy" onerror="this.style.visibility=\'hidden\'">'; }).join('') : '') + '</div>' +
        '<div class="hg-res__grad"></div>' + (imgs.length > 1 ? '<div class="hg-res__dots">' + imgs.map(function (_, k) { return '<button type="button" aria-label="' + (k + 1) + '" aria-current="' + (k === 0) + '"></button>'; }).join('') + '</div>' : '') +
        '<div class="hg-res__name"><small>' + HG.esc(HG.t('quiz_result') + ' · ' + catName) + '</small><h3>' + HG.esc(s.n) + '</h3></div><div class="hg-confetti"></div></div>' +
        '<div><div class="hg-match"><svg viewBox="0 0 110 110" aria-hidden="true"><circle class="track" cx="55" cy="55" r="46"/><circle class="bar" cx="55" cy="55" r="46" stroke-dasharray="' + C + '" stroke-dashoffset="' + C + '"/></svg><div><b data-pct>0</b><b>%</b><span>' + HG.esc(HG.t('quiz_match')) + '</span></div></div>' +
        '<p class="hg-res__desc">' + HG.esc(s.desc || '') + '</p>' +
        '<div class="hg-specs">' + spec(s.m2, 'area', s.m2 + ' ' + HG.t('m2')) + spec(s.pax, 'users', s.pax + ' ' + HG.t('guests')) + spec(s.camas, 'bed', s.camas) + spec(s.jac, 'jacuzzi', HG.t('jacuzzi')) + spec(s.bal, 'balcon', HG.t('balcony')) + spec(s.coc, 'cocina', HG.t('kitchen')) + spec(s.sala, 'sala', HG.t('living')) + spec(s.vista, 'view', s.vista) + '</div>' +
        '<div class="hg-res__actions"><button type="button" class="hg-btn hg-btn--gold" data-book>' + HG.esc(HG.t('quiz_book')) + ' · ' + a.noches + ' ' + HG.esc(HG.t('nights')) + '<span class="hg-btn__arrow">' + HG.icon('plus', 16).replace('M12 5v14M5 12h14', 'M5 12h14M13 6l6 6-6 6') + '</span></button>' +
        (s.url ? '<a class="hg-btn hg-btn--ghost" href="' + HG.esc(s.url) + '">' + HG.esc(HG.t('quiz_see')) + '</a>' : '') +
        '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-share>' + HG.icon('share', 16) + ' ' + HG.esc(HG.t('quiz_share')) + '</button>' +
        '<button type="button" class="hg-btn hg-btn--ghost hg-btn--sm" data-again>' + HG.esc(HG.t('quiz_again')) + '</button></div>' +
        (second ? '<div class="hg-res__also">' + (second.s.img && second.s.img[0] ? '<img src="' + HG.esc(second.s.img[0]) + '" alt="" loading="lazy" onerror="this.style.visibility=\'hidden\'">' : '') + '<div><small>' + HG.esc(HG.t('quiz_also')) + '</small><a href="' + HG.esc(second.s.url || '#') + '">' + HG.esc(second.s.n) + '</a><em>' + pct(second.sc) + '%</em></div></div>' : '') +
        '</div></div>';

      // Animaciones de entrada
      var bar = stage.querySelector('.bar'), pEl = stage.querySelector('[data-pct]');
      requestAnimationFrame(function () { bar.style.strokeDashoffset = C * (1 - p / 100); });
      HG.countTo(pEl, p, 0, 1500);
      confetti(stage.querySelector('.hg-confetti'));
      HG.vibe([10, 40, 10]);

      // Galería deslizable + inclinación 3D
      var card = stage.querySelector('.hg-res__card'), slides = stage.querySelector('.hg-res__slides'), dotEls = [].slice.call(stage.querySelectorAll('.hg-res__dots button')), cur = 0;
      var goSlide = function (k) { cur = (k + imgs.length) % imgs.length; slides.style.transform = 'translateX(' + (-100 * cur) + '%)'; dotEls.forEach(function (d, j) { d.setAttribute('aria-current', j === cur); }); };
      dotEls.forEach(function (d, k) { d.onclick = function (e) { e.stopPropagation(); goSlide(k); }; });
      var sx = null;
      card.addEventListener('pointerdown', function (e) { sx = e.clientX; });
      card.addEventListener('pointerup', function (e) { if (sx == null || imgs.length < 2) return; var d = e.clientX - sx; if (Math.abs(d) > 30) goSlide(cur + (d < 0 ? 1 : -1)); else if (e.pointerType === 'mouse') goSlide(cur + 1); sx = null; });
      card.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight') goSlide(cur + 1); if (e.key === 'ArrowLeft') goSlide(cur - 1); });
      tilt(card, 10);

      stage.querySelector('[data-book]').onclick = function () { HG.book(HG.tomorrow(), a.noches, 'quiz_' + s.id); };
      stage.querySelector('[data-again]').onclick = function () { ans = { must: [] }; ask(0, true); };
      stage.querySelector('[data-share]').onclick = function () {
        var txt = HG.t('quiz_share_text', { s: s.n }), url = location.href.split('#')[0];
        HG.track('hg_quiz_share', { suite: s.id });
        if (navigator.share) navigator.share({ title: 'Haven', text: txt, url: url }).catch(function () {}); else HG.copy(txt + ' ' + url);
      };
      HG.track('hg_quiz_result', { suite: s.id, afinidad: p, motivo: a.motivo, grupo: a.grupo, noches: a.noches, nivel: a.nivel });
      stage.scrollIntoView({ behavior: HG.reduce ? 'auto' : 'smooth', block: 'center' });
    }

    function tilt(el, deg) {
      if (HG.reduce || !matchMedia('(hover: hover)').matches) return;
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        el.style.transform = 'perspective(900px) rotateY(' + ((x - .5) * deg) + 'deg) rotateX(' + ((.5 - y) * deg * .8) + 'deg)';
        el.style.setProperty('--mx', x * 100 + '%'); el.style.setProperty('--my', y * 100 + '%');
      });
      el.addEventListener('pointerleave', function () { el.style.transform = ''; });
    }
    function confetti(box) {
      if (HG.reduce || !box) return;
      var cols = ['#CDB371', '#F7DFAE', '#FDF5E5', '#a8873f'];
      for (var i = 0; i < 46; i++) {
        var c = document.createElement('i');
        c.style.left = Math.random() * 100 + '%'; c.style.background = cols[i % 4];
        c.style.animationDuration = (1.4 + Math.random() * 1.6) + 's'; c.style.animationDelay = (Math.random() * .4) + 's';
        c.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
        box.append(c);
      }
      setTimeout(function () { box.innerHTML = ''; }, 3600);
    }
  };
})();
