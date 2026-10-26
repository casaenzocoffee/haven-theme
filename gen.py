#!/usr/bin/env python3
"""Genera las páginas internas de Haven a partir del contenido real extraído
(/home/claude/haven-content/*.json). Textos literales del sitio; solo cambia el diseño."""
import json, os, re, html

LANG = os.environ.get('HV_LANG', 'es')
C_ES = '/home/claude/haven-content/'
C = '/home/claude/haven-content-en/' if LANG == 'en' else C_ES
SRC = os.path.join(os.path.dirname(__file__), 'src')
WA = 'https://wa.me/573212097033?text='

def J(slug, base=None):
    with open((base or C) + slug + '.json', encoding='utf-8') as f:
        return json.load(f)

def flat(slug, base=None):
    return [b for s in J(slug, base)['sections'] for b in s['blocks']]

def P(u):
    """URL absoluta del sitio -> ruta /wp-content/..."""
    return re.sub(r'^https?://havenmedellin\.com', '', u or '')

def e(t):
    return html.escape(t or '', quote=True)

def clean(t):
    return re.sub(r'[​‌‍﻿]', '', t or '').replace('m 2', 'm²').strip()

ARROW = '<span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>'
OUT = '<span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M9 7h8v8"/></svg></span>'
STAMP = '''<svg class="arch-stamp" viewBox="0 0 120 120" aria-hidden="true"><defs><path id="cp" d="M60 60m-46 0a46 46 0 1 1 92 0a46 46 0 1 1-92 0"/></defs><text font-family="DM Sans" font-size="10.5" letter-spacing="4.6" fill="currentColor"><textPath href="#cp">HAVEN · A DESIGN HOTEL · MEDELLÍN · </textPath></text><circle cx="60" cy="60" r="4" fill="currentColor"/></svg>'''

def btn(text, href, kind='gold', track='', ext=False):
    t = f' data-track="{track}"' if track else ''
    x = ' target="_blank" rel="noopener"' if ext else ''
    return f'<a class="btn btn-{kind}" href="{e(href)}"{t}{x}>{e(text)} {OUT if ext else ARROW}</a>'

def hero(title, sub, img, crumbs, eyebrow='', tags=None, video=None):
    cr = ' <span aria-hidden="true">/</span> '.join(f'<a href="{h}">{e(t)}</a>' if h else f'<span>{e(t)}</span>' for t, h in crumbs)
    tg = ''.join(f'<span class="chip">{e(t)}</span>' for t in (tags or []))
    media = f'<img src="{img}" alt="" fetchpriority="high">'
    return f'''<section class="ph">
  <div class="ph-media">{media}</div>
  <div class="wrap">
    <nav class="crumbs" aria-label="Ruta">{cr}</nav>
    {f'<p class="eyebrow">{e(eyebrow)}</p>' if eyebrow else ''}
    <h1 class="split">{title}</h1>
    <div class="ph-row"><p class="ph-sub">{e(sub)}</p><div class="ph-tags">{tg}</div><a class="ph-cue" href="#c" aria-label="Bajar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M6 13l6 6 6-6"/></svg></a></div>
  </div>
</section>
'''

def editorial(eyebrow, title, paras, img, img2=None, flip=False, cream=False, cta=''):
    ps = ''.join(f'<p>{e(clean(p))}</p>' for p in paras)
    fl = f'<div class="arch-float"><img src="{img2}" alt="" loading="lazy"></div>' if img2 else ''
    return f'''<section class="ed{' cream' if cream else ''}" id="c">
  <div class="wrap ed-grid{' flip' if flip else ''}">
    <div data-reveal><p class="eyebrow">{e(eyebrow)}</p><h2>{title}</h2>{ps}{f'<div style="margin-top:34px;display:flex;gap:10px;flex-wrap:wrap">{cta}</div>' if cta else ''}</div>
    <div class="arch-wrap">{STAMP}<div class="arch"><img src="{img}" alt="" loading="lazy" data-speed=".08"></div>{fl}</div>
  </div>
</section>
'''

def hgallery(eyebrow, title, imgs, caps=None):
    items = ''.join(f'<figure class="hgal-item"><img src="{u}" alt="" loading="lazy">{f"<figcaption>{e(caps[i])}</figcaption>" if caps and i < len(caps) and caps[i] else ""}</figure>' for i, u in enumerate(imgs))
    return f'''<section class="hgal">
  <div class="hgal-pin">
    <div class="wrap hgal-head"><div><p class="eyebrow">{e(eyebrow)}</p><h2 style="margin-top:18px">{title}</h2></div><p class="hgal-count"><b>01</b>/ {len(imgs):02d}</p></div>
    <div class="hgal-track">{items}</div>
  </div>
</section>
'''

def bigq(eyebrow, text, cream=False):
    return f'<section class="bigq{" cream" if cream else ""}"><div class="wrap"><p class="eyebrow">{e(eyebrow)}</p><p>{e(text)}</p></div></section>\n'

def bookbar(label='Reserva directa · mejor tarifa'):
    return f'<div class="bookbar" aria-hidden="false"><span>{e(label)}</span><a class="btn btn-gold" href="#reservar" data-track="reservar_barra">Reservar {ARROW}</a></div>\n'

# ---------------------------------------------------------------- habitaciones
ROOMS = [  # slug real, categoría, filtro
    ('suite-emperor-901', 'Penthouse', 'penthouse'), ('suite-emperor-902', 'Penthouse', 'penthouse'),
    ('suite-presidential', 'Presidencial', 'presidencial'),
    ('suite-imperial-402', 'Suites de lujo', 'suites'), ('suite-excelsior-401', 'Suites de lujo', 'suites'),
    ('suite-excelsior-804', 'Suites de lujo', 'suites'), ('suite-excelsior-604', 'Suites de lujo', 'suites'),
    ('habitacion-ambassador', 'Habitaciones', 'habitaciones'), ('habitacion-ambassador-jr', 'Habitaciones', 'habitaciones'),
    ('habitacion-viscount-803', 'Habitaciones', 'habitaciones'), ('habitacion-viscount', 'Habitaciones', 'habitaciones'),
    ('habitacion-lord', 'Habitaciones', 'habitaciones'), ('habitacion-earl', 'Habitaciones', 'habitaciones'),
    ('habitacion-marques-plus', 'Habitaciones', 'habitaciones'), ('habitacion-marques', 'Habitaciones', 'habitaciones'),
    ('habitacion-duke', 'Habitaciones', 'habitaciones'), ('habitacion-consort', 'Habitaciones', 'habitaciones'),
]

def room_data(slug, base=None):
    bl = flat(slug, base)
    imgs, seen = [], set()
    for b in bl:
        if b['type'] == 'image' and b['src'] not in seen:
            seen.add(b['src']); imgs.append(P(b['src']))
    heads = [b['text'] for b in bl if b['type'] == 'heading']
    kicker = heads[0] if heads else ''
    name = heads[1] if len(heads) > 1 else heads[0]
    lists = [b['items'] for b in bl if b['type'] == 'list']
    specs = [clean(x) for x in lists[0]] if lists else []
    amen = [clean(x) for l in lists[1:] for x in l]
    paras = [clean(b['text']) for b in bl if b['type'] == 'paragraph']
    ADD = ('Además', 'In addition', 'Additionally', 'Also', 'Furthermore', 'Besides')
    desc = [p for p in paras if not p.startswith(ADD)]
    serv = [p for p in paras if p.startswith(ADD)]
    name = clean(name).replace('SUITE', 'Suite').replace('ROOM', 'Room')
    return dict(slug=slug, name=name, kicker=clean(kicker), imgs=imgs, specs=specs, amen=amen, desc=desc, serv=serv)

ICONS = {
    'aire': '<path d="M3 8h13a3 3 0 1 0-3-3M3 12h17a3 3 0 1 1-3 3M3 16h9"/>',
    'tv': '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8"/>',
    'baño': '<path d="M4 12h16v3a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5zM6 12V6a2 2 0 0 1 4 0"/>',
    'jacuzzi': '<path d="M3 12h18v3a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5v-3ZM6 12V6a2 2 0 0 1 4 0M14 8c0-1 1-1 1-2s-1-1-1-2M18 8c0-1 1-1 1-2s-1-1-1-2"/>',
    'cocina': '<path d="M4 10h16v10H4zM8 10V5h8v5M10 15h4"/>',
    'sala': '<path d="M3 18v-6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v6M3 15h18M6 9V6h12v3"/>',
    'bar': '<path d="M8 21h8M12 17v4M7 4h10l-1 7a4 4 0 0 1-8 0L7 4Z"/>',
    'vista': '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
    'balc': '<path d="M4 20V10l8-6 8 6v10M4 14h16M8 14v6M12 14v6M16 14v6"/>',
    'comedor': '<path d="M5 3v8a2 2 0 0 0 4 0V3M7 11v10M17 3c-2 2-2 6 0 8v10"/>',
    'default': '<path d="M12 3l2.6 5.6L20 9.5l-4 4 1 5.5-5-2.7-5 2.7 1-5.5-4-4 5.4-.9z"/>',
}
def icon(t):
    tl = t.lower()
    for k, v in [('air', 'aire'), ('bath', 'baño'), ('shower', 'baño'), ('kitchen', 'cocina'), ('living', 'sala'), ('lounge', 'sala'), ('view', 'vista'), ('dining', 'comedor'), ('aire', 'aire'), ('tv', 'tv'), ('jacuzzi', 'jacuzzi'), ('baño', 'baño'), ('ducha', 'baño'), ('cocina', 'cocina'), ('sala', 'sala'), ('estar', 'sala'),
                 ('bar', 'bar'), ('vista', 'vista'), ('balc', 'balc'), ('comedor', 'comedor'), ('relaj', 'jacuzzi')]:
        if k in tl:
            return f'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{ICONS[v]}</svg>'
    return f'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{ICONS["default"]}</svg>'

def spec_parts(specs):
    m2 = next((s for s in specs if 'm²' in s), '')
    per = next((s for s in specs if re.search(r'persona|person|people|guest', s, re.I)), '')
    beds = [s for s in specs if 'x' in s.lower() and 'm²' not in s]
    dup = next((s for s in specs if 'uplex' in s), '')
    return m2, per, beds, dup

COVERS = {}
def _norm(n):
    return re.sub(r'^(habitaci[oó]n|suite)\s+', '', clean(n).lower()).replace('é', 'e').strip()
def load_covers():
    for sl in ['todas-las-habitaciones', 'penthouse', 'presidencial', 'suites-de-lujo']:
        last = None
        for b in flat(sl, C_ES):
            if b['type'] == 'image': last = P(b['src'])
            elif b['type'] == 'heading' and last:
                COVERS.setdefault(_norm(b['text']), last)
ES_NAMES = {}
def cover(r):
    return COVERS.get(_norm(ES_NAMES.get(r['slug'], r['name']))) or (r['imgs'][0] if r['imgs'] else '')

def room_card(r, cat, f, hidden=False):
    m2, per, beds, dup = spec_parts(r['specs'])
    chips = ''.join(f'<span class="chip">{e(x)}</span>' for x in [m2, per] + beds + ([dup] if dup else []) if x)
    im1 = cover(r)
    im2 = next((u for u in r['imgs'] if u != im1), im1)
    desc = r['desc'][0] if r['desc'] else ''
    if len(desc) > 150:
        desc = desc[:150].rsplit(' ', 1)[0] + '…'
    return f'''<article class="card{' hide' if hidden else ''}" data-cat="{f}">
  <a class="card-media" href="{{{{R}}}}habitacion/{r['slug']}/" aria-label="{e(r['name'])}"><img src="{im1}" alt="{e(r['name'])}" loading="lazy"><img src="{im2}" alt="" loading="lazy"><span class="card-tag">{e(cat)}</span></a>
  <div class="card-body"><h3>{e(r['name'])}</h3><div class="chips">{chips}</div><p>{e(desc)}</p>
  <div class="card-actions"><a class="btn btn-ghost" href="{{{{R}}}}habitacion/{r['slug']}/">Ver {OUT}</a><a class="btn btn-gold" href="#reservar" data-track="reservar_{r['slug']}">Reservar {ARROW}</a></div></div>
</article>'''

def page_habitaciones(all_rooms):
    d = flat('todas-las-habitaciones')
    intro = clean(next(b['text'] for b in d if b['type'] == 'paragraph'))
    cards = '\n'.join(room_card(r, cat, f, f != 'penthouse') for r, cat, f in all_rooms)
    gal = []
    for r, _, _ in all_rooms:
        gal += r['imgs'][1:2]
    body = hero('Todas las <em>habitaciones</em>', 'Diecisiete espacios decorados y amoblados de forma individual, del penthouse dúplex a la habitación esencial.',
                '/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1.webp', [('Inicio', '{{R}}'), ('Habitaciones', '')], 'Haven · Medellín', ['17 habitaciones', 'Jacuzzi', 'Desayuno incluido'])
    body += f'''<section class="sec" id="c"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow">Descanso con estilo</p><h2 style="margin-top:20px">Elige tu <em>habitación</em></h2></div><p>{e(intro)}</p></div>
  <div class="filters" data-target="#rooms" role="group" aria-label="Filtrar">
    <button type="button" data-f="penthouse" aria-pressed="true">Penthouse</button><button type="button" data-f="presidencial" aria-pressed="false">Presidencial</button><button type="button" data-f="suites" aria-pressed="false">Suites de lujo</button><button type="button" data-f="habitaciones" aria-pressed="false">Habitaciones</button><button type="button" data-f="all" aria-pressed="false">Ver todas</button>
  </div>
  <div class="cards" id="rooms">
{cards}
  </div>
</div></section>
'''
    body += bigq('Haven · A Design Hotel', 'Cada espacio está decorado y amoblado de forma individual.')
    body += hgallery('Galería', 'Detalles que <em>se sienten</em>', gal[:10])
    body += bookbar()
    return body

def page_category(slug, title_html, cat, f, all_rooms, img):
    d = flat(slug)
    intro = clean(next(b['text'] for b in d if b['type'] == 'paragraph'))
    rooms = [x for x in all_rooms if x[2] == f]
    cards = '\n'.join(room_card(r, c, ff) for r, c, ff in rooms)
    body = hero(title_html, intro[:160].rsplit(' ', 1)[0] + '…', img, [('Inicio', '{{R}}'), ('Habitaciones', '{{R}}habitaciones/'), (cat, '')], 'Haven · Medellín')
    gal = [u for r, _, _ in rooms for u in r['imgs'][:4]]
    body += editorial(cat, title_html, [intro], gal[0] if gal else img, gal[2] if len(gal) > 2 else None, cream=True)
    body += f'<section class="sec"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">{e(cat)}</p><h2 style="margin-top:20px">Elige tu <em>espacio</em></h2></div></div><div class="cards">{cards}</div></div></section>\n'
    body += hgallery('Galería', f'{e(cat)} <em>en detalle</em>', gal[:10])
    body += bookbar()
    return body

def page_room(r, cat, f, all_rooms):
    m2, per, beds, dup = spec_parts(r['specs'])
    num = lambda s: re.match(r'(\d+)', s).group(1) if s and re.match(r'(\d+)', s) else ''
    specs = ''
    if m2: specs += f'<div><b data-n="{num(m2)}" data-u=" m²">{e(m2)}</b><span>Área</span></div>'
    if per: specs += f'<div><b data-n="{num(per)}">{num(per)}</b><span>Personas</span></div>'
    if beds: specs += f'<div><b>{e(" · ".join(beds))}</b><span>Camas</span></div>'
    if dup: specs += f'<div><b>{e(dup)}</b><span>Formato</span></div>'
    amen = ''.join(f'<li>{icon(a)}{e(a)}</li>' for a in r['amen'])
    desc = ''.join(f'<p>{e(p)}</p>' for p in r['desc'])
    serv = ''.join(f'<p>{e(p)}</p>' for p in r['serv'])
    mosaic = ''.join(f'<a href="{u}"><img src="{u}" alt="{e(r["name"])} — foto {i+1}" loading="lazy"></a>' for i, u in enumerate(r['imgs']))
    others = [x for x in all_rooms if x[0]['slug'] != r['slug'] and x[2] == f][:3] or [x for x in all_rooms if x[0]['slug'] != r['slug']][:3]
    ocards = '\n'.join(room_card(o, c, ff) for o, c, ff in others)
    body = hero(e(r['name']).replace('Suite ', 'Suite <em>').replace('Habitación ', 'Habitación <em>') + ('</em>' if ('Suite ' in r['name'] or 'Habitación ' in r['name']) else ''),
                r['desc'][0][:150].rsplit(' ', 1)[0] + '…' if r['desc'] else '', cover(r),
                [('Inicio', '{{R}}'), ('Habitaciones', '{{R}}habitaciones/'), (r['name'], '')], r['kicker'] or cat, [x for x in [m2, per] if x])
    body += f'''<section class="ed" id="c"><div class="wrap">
  <div class="ed-grid">
    <div data-reveal><p class="eyebrow">{e(cat)}</p><h2>{e(r['name'])}</h2>{desc}
      <div style="margin-top:34px;display:flex;gap:10px;flex-wrap:wrap">{btn('Reservar ahora', '#reservar', 'gold', 'reservar_' + r['slug'])}{btn('WhatsApp', WA + 'Hola, quiero información sobre ' + r['name'], 'ghost', 'whatsapp_' + r['slug'], True)}</div></div>
    <div class="arch-wrap">{STAMP}<div class="arch"><img src="{r['imgs'][1] if len(r['imgs'])>1 else r['imgs'][0]}" alt="" loading="lazy" data-speed=".08"></div>{f'<div class="arch-float"><img src="{r["imgs"][2]}" alt="" loading="lazy"></div>' if len(r['imgs'])>2 else ''}</div>
  </div>
  <div class="rd-specs" data-reveal>{specs}</div>
</div></section>
<section class="sec cream"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow">Comodidades</p><h2 style="margin-top:20px">Todo lo que <em style="color:#8a6f33">necesitas</em></h2></div>{serv}</div>
  <ul class="amen amen-cream" data-reveal>{amen}</ul>
  <div style="margin-top:34px">{btn('Conoce nuestros servicios', '{{R}}servicios/', 'ghost')}</div>
</div></section>
<section class="sec"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow">Galería · {len(r['imgs'])} fotos</p><h2 style="margin-top:20px">Recorre la <em>{'suite' if 'Suite' in r['name'] else 'habitación'}</em></h2></div><p>Toca cualquier foto para verla en grande.</p></div>
  <div class="mosaic">{mosaic}</div>
</div></section>
<section class="sec" style="padding-top:0"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow">También te puede gustar</p><h2 style="margin-top:20px">Otros <em>espacios</em></h2></div>{btn('Ver todas', '{{R}}habitaciones/', 'ghost')}</div>
  <div class="cards">{ocards}</div>
</div></section>
'''
    body += bookbar(r['name'] + ' · reserva directa')
    return body

# ------------------------------------------------------------- experiencias
def sections_of(slug):
    """Agrupa bloques en secciones: los encabezados seguidos abren una sección
    (si son 2+, el primero es el antetítulo y el resto el título)."""
    out, cur, last_head = [], None, False
    def new():
        return dict(eyebrow='', title='', heads=[], paras=[], btns=[], imgs=[], lists=[])
    for b in flat(slug):
        t = b['type']
        if t == 'heading':
            if cur is None or not last_head:
                if cur: out.append(cur)
                cur = new()
            cur['heads'].append(clean(b['text'])); last_head = True
            continue
        last_head = False
        if cur is None: cur = new()
        if t == 'paragraph': cur['paras'].append(clean(b['text']))
        elif t == 'image': cur['imgs'].append(P(b['src']))
        elif t in ('button', 'link'): cur['btns'].append((clean(b.get('text')), b.get('href') or ''))
        elif t == 'list': cur['lists'].append([clean(x) for x in b['items']])
    if cur: out.append(cur)
    for s in out:
        h = s['heads']
        if len(h) >= 2: s['eyebrow'], s['title'] = h[0], ' '.join(h[1:])
        elif h: s['title'] = h[0]
    # imágenes sueltas al final de una sección sin texto pasan a la anterior
    merged = []
    for s in out:
        if merged and not s['paras'] and not s['heads']:
            merged[-1]['imgs'] += s['imgs']; merged[-1]['btns'] += s['btns']
        else:
            merged.append(s)
    return merged

def fixhref(h, wa_text):
    if not h or h.endswith('#') or '#joinchat' in h:
        return WA + wa_text, True
    if '/en/gastronomy' in h:
        return '{{R}}gastronomia/', False
    if 'havenmedellin.com/es/' in h:
        return P(h).replace('/es/servicios/', '{{R}}servicios/'), False
    return h, h.startswith('http')

def page_experience(slug, h1, sub, hero_img, crumbs_name, gal_title, wa_text, key, after_first=''):
    secs = sections_of(slug)
    imgs_all = [u for s in secs for u in s['imgs']]
    body = hero(h1, sub, hero_img, [('Inicio', '{{R}}'), (crumbs_name, '')], 'Haven · Medellín')
    n = 0
    for s in secs:
        if not s['paras']:
            continue
        ims = s['imgs'] or imgs_all[n % len(imgs_all):][:2]
        cta = ''
        seenb=set()
        for t, h in s['btns'][:3]:
            if not t or t.lower() in seenb: continue
            seenb.add(t.lower())
            href, ext = fixhref(h, wa_text)
            cta += btn(t.capitalize() if t.islower() else t, href, 'gold' if not cta else 'ghost', f'{key}_{n}', ext)
        title = e(s['title'])
        words = title.split(' ')
        if len(words) > 1:
            title = ' '.join(words[:-1]) + f' <em>{words[-1]}</em>'
        body += editorial(s['eyebrow'] or crumbs_name, title, s['paras'], ims[0] if ims else hero_img, ims[1] if len(ims) > 1 else None, flip=n % 2 == 1, cream=n % 2 == 1, cta=cta)
        if n == 0 and after_first:
            body += after_first
        if n == 0 and len(imgs_all) >= 5:
            body += hgallery('Galería', gal_title, imgs_all[:10])
        n += 1
    return body


# ------------------------------------------------------------- Casa Enzo Coffee (café insignia)
CE_IMG = 'https://casaenzocoffee.com/cdn/shop/'
def casa_enzo():
    en = LANG == 'en'
    url = 'https://casaenzocoffee.com/en' if en else 'https://casaenzocoffee.com'
    if en:
        t = dict(eb='Our signature coffee', h='Casa Enzo <em>Coffee</em>',
                 p1='Colombian specialty coffee, grown by Colombian coffee farmers and roasted in Medellín. Our first lot, Primera Luz, is a Caturra hand-picked at 1,699 meters in the mountains of Santa Elena, above the city: an artisanal micro-lot with notes of green apple, yellow fruits and sugarcane.',
                 p2='It is the coffee we serve at Haven, and you can take it home: a bag of Casa Enzo is a gift that carries the taste of origin.',
                 facts=[('Origin', 'Santa Elena, Medellín'), ('Altitude', '1,699 m'), ('Variety', 'Caturra · light honey'), ('Lot', 'Artisanal micro-lot')],
                 where='Enjoy it at', places=['Swing Coffee Bar', 'Our breakfast', 'Every room', 'To take home as a gift'],
                 cta='See more', alt=['Casa Enzo Coffee bag, Primera Luz', 'Pouring coffee over a Chemex', 'Roasted coffee, green coffee and coffee cherries'])
    else:
        t = dict(eb='Nuestro café insignia', h='Casa Enzo <em>Coffee</em>',
                 p1='Café de especialidad colombiano, cultivado por caficultores colombianos y tostado en Medellín. Su primer lote, Primera Luz, es un Caturra cosechado a mano a 1.699 metros en las montañas de Santa Elena, sobre la ciudad: un microlote artesanal con notas de manzana verde, frutos amarillos y caña de azúcar.',
                 p2='Es el café que servimos en Haven, y también puedes llevártelo: una bolsa de Casa Enzo es un regalo con el sabor del origen.',
                 facts=[('Origen', 'Santa Elena, Medellín'), ('Altura', '1.699 msnm'), ('Variedad', 'Caturra · honey claro'), ('Lote', 'Microlote artesanal')],
                 where='Lo encuentras en', places=['Swing Coffee Bar', 'Nuestro desayuno', 'Todas las habitaciones', 'Para llevar de regalo'],
                 cta='Ver más', alt=['Bolsa de Casa Enzo Coffee, Primera Luz', 'Café filtrado en Chemex', 'Café tostado, café verde y cerezas de café'])
    imgs = [CE_IMG + 'files/WhatsApp_Image_2026-10-21_at_10_16_43_AM.jpg?v=1792596874&width=1000',
            CE_IMG + 'articles/hf_20261024_235910_6ef4ebc1-6fc7-4975-97b4-9f50157d0c07.png?v=1792886467&width=700',
            CE_IMG + 'articles/hf_20261024_235944_e3420dd7-0d73-444f-81ac-9a9f38e6d61e.png?v=1792886474&width=700']
    facts = ''.join(f'<div><span>{e(a)}</span><b>{e(b)}</b></div>' for a, b in t['facts'])
    places = ''.join(f'<li>{e(x)}</li>' for x in t['places'])
    return f'''<section class="ce" aria-labelledby="ce-title">
  <div class="wrap ce-grid">
    <div class="ce-media" aria-hidden="false">
      <figure class="ce-main"><img src="{imgs[0]}" alt="{e(t['alt'][0])}" loading="lazy" width="1000" height="1000"></figure>
      <figure class="ce-a"><img src="{imgs[1]}" alt="{e(t['alt'][1])}" loading="lazy" width="700" height="700"></figure>
      <figure class="ce-b"><img src="{imgs[2]}" alt="{e(t['alt'][2])}" loading="lazy" width="700" height="700"></figure>
    </div>
    <div class="ce-copy" data-reveal>
      <p class="eyebrow">{e(t['eb'])}</p>
      <h2 id="ce-title">{t['h']}</h2>
      <p>{e(t['p1'])}</p>
      <p>{e(t['p2'])}</p>
      <div class="ce-facts">{facts}</div>
      <p class="ce-where">{e(t['where'])}</p>
      <ul class="ce-places">{places}</ul>
      <div style="margin-top:30px">{btn(t['cta'], url, 'gold', 'casa_enzo_ver_mas', True)}</div>
    </div>
  </div>
</section>
'''

def page_gastronomia():
    secs = sections_of('gastronomia')
    dishes, imgs = '', []
    for s in secs:
        imgs += s['imgs']
    for s in secs:
        if s['paras']:
            dishes += f'<div class="dish"><h3>{e(s["title"])}</h3><span class="tag">{e(s["eyebrow"])}</span><p>{e(s["paras"][0])}</p></div>'
    body = hero('<em>Gastronomía</em>', 'Cocina local e internacional con vista a la ciudad, en el 360° Rooftop del piso 11.', '/wp-content/uploads/2026/07/burrata-pizza.webp', [('Inicio', '{{R}}'), ('Gastronomía', '')], 'Haven · 360° Rooftop')
    ps = [x['paras'][0] for x in secs if x['paras']][:2]
    body += editorial('Platos recomendados', 'Sabores que <em>inspiran</em>', ps, imgs[0], imgs[1] if len(imgs) > 1 else None,
                      cta=btn('Ver menú digital', 'https://360-rooftop.cluvi.co/', 'gold', 'menu_digital', True) + btn('Reservar mesa', 'https://fiweex.com/reservas_portal_bienvenida/aIJzgQ%3D%3D', 'ghost', 'reservar_mesa', True))
    body += f'<section class="sec cream"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">La carta</p><h2 style="margin-top:20px">Lo más <em style="color:#8a6f33">pedido</em></h2></div><p>Una selección de la carta del 360° Rooftop. El menú completo está en línea.</p></div><div class="menu-cols" data-reveal>{dishes}</div><div style="margin-top:40px;display:flex;gap:10px;flex-wrap:wrap">{btn("Ver platos recomendados","https://360-rooftop.cluvi.co/newmenu/19402/on_table/basic/categories/46104","gold","platos_recomendados",True)}{btn("Ver platos fuertes","https://360-rooftop.cluvi.co/newmenu/19402/on_table/basic/categories/46059","ghost","platos_fuertes",True)}</div></div></section>\n'
    body += hgallery('Galería', 'Para <em>compartir</em>', imgs + ['/wp-content/uploads/2026/06/hotel-haven-rooftop-360-gastronomy-2.webp', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-gastronomy-cocktails-1.webp', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-gastronomy-cocktails-2.webp'])
    body += bigq('360° Rooftop', 'Déjese llevar por un viaje culinario inolvidable mientras disfruta de las vistas.')
    return body

def page_contacto():
    form = f'''<form class="cform" action="mailto:frontdesk@serenahotelsco.com" method="post" enctype="text/plain" data-reveal>
  <label>Nombre<input name="nombre" type="text" placeholder="Coloque su nombre completo" required autocomplete="name"></label>
  <label>Celular<input name="celular" type="tel" placeholder="Coloque su número de celular" autocomplete="tel"></label>
  <label>E-mail<input name="email" type="email" placeholder="Correo electrónico" required autocomplete="email"></label>
  <label>Mensaje<textarea name="mensaje" rows="4" placeholder="Redacte un mensaje"></textarea></label>
  <button class="btn btn-gold" type="submit" data-track="contacto_enviar">Enviar solicitud {ARROW}</button>
</form>'''
    ic = lambda p: f'<span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{p}</svg></span>'
    boxes = f'''<div class="contact-grid" data-reveal>
  <div class="cbox">{ic('<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>')}<h3>Ubicación</h3><p>Calle 10A #36-29<br>El Poblado, Medellín, Colombia</p><a href="https://www.google.com/maps/dir/?api=1&amp;destination=Haven+Medell%C3%ADn+Calle+10A+36-29" target="_blank" rel="noopener" data-track="como_llegar_contacto">Cómo llegar ↗︎</a></div>
  <div class="cbox">{ic('<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>')}<h3>Recepción</h3><p><a href="tel:+573223064585" data-track="llamar_recepcion">(+57) 322 306 4585</a><br><a href="tel:+576046047327">(+57) (4) 604 7327</a><br><a href="mailto:frontdesk@serenahotelsco.com">frontdesk@serenahotelsco.com</a></p></div>
  <div class="cbox">{ic('<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>')}<h3>Reservas</h3><p><a href="tel:+573212097033" data-track="llamar_reservas">(+57) 321 209 7033</a><br><a href="mailto:booking@serenahotelsco.com">booking@serenahotelsco.com</a></p><a href="{WA}Hola, quiero hacer una reserva" target="_blank" rel="noopener" data-track="whatsapp_contacto">Escribir por WhatsApp ↗︎</a></div>
</div>'''
    body = hero('<em>Contacto</em>', 'Estamos aquí para resolver cualquier detalle, antes, durante o después de tu estancia.', '/wp-content/uploads/2026/06/hotel-haven-medellin.webp', [('Inicio', '{{R}}'), ('Contacto', '')], 'Haven · Medellín')
    body += f'<section class="sec" id="c"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">Hablemos</p><h2 style="margin-top:20px">Estamos para <em>ayudarte</em></h2></div><p>¿Tienes alguna duda o queja? ¡Estamos aquí para escucharte!</p></div>{boxes}</div></section>\n'
    body += f'<section class="ed cream"><div class="wrap ed-grid"><div><p class="eyebrow">Formulario</p><h2>Escríbenos</h2><p>Te respondemos en el menor tiempo posible.</p>{form}</div><div class="arch-wrap">{STAMP}<div class="arch"><img src="/wp-content/uploads/2026/06/medellin-haven-a-design-hotel.webp" alt="" loading="lazy" data-speed=".08"></div></div></div></section>\n'
    return body

def page_faq():
    d = J('preguntas-frecuentes')
    items = ''
    for q in d.get('faq_items', []):
        ans = q['answer'] if isinstance(q['answer'], list) else [q['answer']]
        a = ''.join(f'<p>{e(clean(x))}</p>' if isinstance(x, str) else '' for x in ans)
        items += f'<details><summary>{e(clean(q["question"]))}<i aria-hidden="true">+</i></summary><div class="a">{a}</div></details>'
    body = hero('Preguntas <em>frecuentes</em>', 'Toda la información para disfrutar al máximo de tu estancia en El Poblado.', '/wp-content/uploads/2026/06/preguntas-frecuentes.webp', [('Inicio', '{{R}}'), ('Preguntas frecuentes', '')], 'PQRS')
    body += f'<section class="sec" id="c"><div class="wrap" style="max-width:1000px"><div class="faq" data-reveal>{items}</div><div style="margin-top:50px;display:flex;gap:10px;flex-wrap:wrap">{btn("Escribir por WhatsApp", WA + "Hola, tengo una pregunta", "gold", "faq_whatsapp", True)}{btn("Escribir al correo","mailto:frontdesk@serenahotelsco.com","ghost","faq_correo")}</div></div></section>\n'
    # schema FAQPage
    faq = [{"@type": "Question", "name": clean(q['question']), "acceptedAnswer": {"@type": "Answer", "text": ' '.join(clean(x) for x in (q['answer'] if isinstance(q['answer'], list) else [q['answer']]) if isinstance(x, str))}} for q in d.get('faq_items', [])]
    return body, '<script type="application/ld+json">' + json.dumps({"@context": "https://schema.org", "@type": "FAQPage", "mainEntity": faq}, ensure_ascii=False) + '</script>\n'

def page_servicios():
    secs = sections_of('servicios')
    cards = ''
    pdfs = J('servicios').get('other_upload_refs_in_content', [])
    for s in secs:
        if not s['imgs'] or not s['title'] or s['title'] == 'Servicios':
            continue
        cards += f'<article class="card"><div class="card-media"><img src="{s["imgs"][0]}" alt="" loading="lazy"></div><div class="card-body"><h3>{e(s["title"])}</h3><p>{e(s["paras"][0] if s["paras"] else s.get("eyebrow",""))}</p><div class="card-actions">{btn("Solicitar", WA + "Hola, quiero información sobre " + s["title"], "ghost", "servicio", True)}</div></div></article>'
    intro = next((p for s in secs for p in s['paras']), '')
    body = hero('Nuestros <em>servicios</em>', intro[:150].rsplit(' ', 1)[0] + '…', '/wp-content/uploads/2026/06/servicios-hotel-haven.webp', [('Inicio', '{{R}}'), ('Servicios', '')], 'Haven · Medellín')
    body += f'<section class="sec" id="c"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">A tu medida</p><h2 style="margin-top:20px">Todo en un <em>solo lugar</em></h2></div><p>{e(intro)}</p></div><div class="cards">{cards}</div></div></section>\n'
    return body


def page_rewards():
    secs = sections_of('serena-rewards')
    intro = next(p for x in secs for p in x['paras'])
    plans = [x for x in secs if x['title'] in ('Silver', 'Gold', 'Diamond', 'Black')]
    cls = {'Silver': 'ts', 'Gold': 't3', 'Diamond': 'tdia', 'Black': 't4'}
    rows = ''
    for i, x in enumerate(plans):
        items = ''.join(f'<li>{e(it.replace("**", "*"))}</li>' for it in (x['lists'][0] if x['lists'] else []))
        nights = x['paras'][0] if x['paras'] else ''
        rows += f'''<div class="plan{' flip' if i % 2 else ''}">
  <div class="plan-card"><div class="tier {cls[x['title']]}" tabindex="0"><div class="row"><span class="brand">SERENA</span><span class="chipcard"></span></div><div><div class="tname">{e(x['title'])}</div></div><div class="row"><span>Rewards</span><span>Plan {i+1:02d}</span></div></div></div>
  <div class="plan-body" data-reveal><p class="eyebrow">Plan {e(x['title'])}</p><h3>{e(nights)}</h3><ul class="plan-list">{items}</ul></div>
</div>'''
    note = next((p for x in plans for p in x['paras'] if p.startswith('**')), '')
    body = hero('Serena <em>Rewards</em>', 'Programa de fidelización de Serena Hotels: más noches, más beneficios.', '/wp-content/uploads/2026/06/serena.webp', [('Inicio', '{{R}}'), ('Serena Rewards', '')], 'Programa de fidelización')
    body += editorial('Serena Hotels', 'Elegancia <em>sin límites</em>', [intro], '/wp-content/uploads/2026/06/serena.webp', None, cream=True,
                      cta=btn('Quiero registrarme', WA + 'Hola, quiero registrarme en Serena Rewards', 'gold', 'rewards_registro', True))
    body += f'<section class="sec"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">Cuatro niveles</p><h2 style="margin-top:20px">Elige tu <em>plan</em></h2></div><p>Los beneficios crecen con las noches que pasas con nosotros cada año.</p></div><div class="plans">{rows}</div><p class="plan-note">{e(note.replace("**", "*"))}</p></div></section>\n'
    body += bigq('Serena Rewards', 'Permítanos conocerle mejor.')
    return body


# ------------------------------------------------------------- legal / blog
def slugify(t):
    t = t.lower()
    for a, b in zip('áéíóúñü', 'aeiouna'): t = t.replace(a, b)
    return re.sub(r'[^a-z0-9]+', '-', t).strip('-')[:60]

def prose(blocks, toc=False, skip_first_h=False):
    out, heads, first = [], [], True
    for b in blocks:
        t = b['type']
        if t == 'heading':
            if skip_first_h and first:
                first = False; continue
            first = False
            lv = 2 if b['level'] <= 2 else 3
            tx = clean(b['text']).rstrip(':')
            if lv == 2 and tx.isupper(): tx = tx.capitalize()
            hid = slugify(tx)
            if lv == 2: heads.append((hid, tx))
            out.append(f'<h{lv} id="{hid}">{e(tx)}</h{lv}>')
        elif t == 'paragraph':
            tx = clean(b['text'])
            if tx: out.append(f'<p>{e(tx)}</p>')
        elif t == 'list':
            out.append('<ul>' + ''.join(f'<li>{e(clean(x))}</li>' for x in b['items']) + '</ul>')
        elif t == 'image':
            out.append(f'<figure><img src="{P(b["src"])}" alt="{e(b.get("alt",""))}" loading="lazy"></figure>')
    return '\n'.join(out), heads

def page_legal(slug, title_html, sub, crumb):
    html_, heads = prose(flat(slug), skip_first_h=(slug == 'politica-de-visitantes'))
    toc = ''.join(f'<li><a href="#{h}">{e(t)}</a></li>' for h, t in heads)
    body = hero(title_html, sub, '/wp-content/uploads/2026/06/hotel-haven-medellin.webp', [('Inicio', '{{R}}'), (crumb, '')], 'Haven · Medellín')
    body += f'<section class="sec" id="c"><div class="wrap doc-grid">{f"<aside class=\"toc\"><p class=\"eyebrow\">Contenido</p><ol>{toc}</ol></aside>" if len(heads) > 2 else "<div></div>"}<article class="prose" data-reveal>{html_}</article></div></section>\n'
    return body

BLOG = []  # (slug_es_json, title, date, excerpt, img)
ES2EN_URL = json.load(open('/tmp/claude-0/-home-claude/3a2a9c4a-835b-5048-bb5a-c96133d73201/scratchpad/es2en.json'))
EN2ES_SLUG = {v.rstrip('/').split('/')[-1]: k.rstrip('/').split('/')[-1] for k, v in ES2EN_URL.items() if v}
def load_blog():
    d = J('blogs')
    for p in d.get('posts', []):
        sl = p['slug'] if LANG == 'es' else EN2ES_SLUG.get(p['slug'], p['slug'])
        BLOG.append(dict(slug=sl, slug_es=sl, title=clean(p['title']).rstrip(':.'), date=p.get('date', ''), ex=clean(p.get('excerpt', '')), img=P(p['featured_image']['src'])))
    return next((clean(b['text']) for b in flat('blogs') if b['type'] == 'paragraph'), '')

def load_blog_es_slugs():
    out = []
    for b in flat('blogs', C_ES):
        if b['type'] in ('button', 'link') and b.get('href') and 'havenmedellin.com/es/' in b['href']:
            sl = blog_slug(b['href'])
            if sl not in out: out.append(sl)
    return out

def blog_slug(href):
    return href.rstrip('/').split('/')[-1]

def blog_card(p):
    big = re.sub(r'-\d+x\d+(\.\w+)$', r'\1', p['img'])
    return f'''<article class="card post-card"><a class="card-media" href="{{{{R}}}}blog/{p['slug']}/"><img src="{big}" alt="" loading="lazy"><img src="{big}" alt="" loading="lazy"><span class="card-tag">{e(p['date'])}</span></a>
  <div class="card-body"><h3>{e(p['title'])}</h3><p>{e(p['ex'])}…</p><div class="card-actions"><a class="btn btn-ghost" href="{{{{R}}}}blog/{p['slug']}/">Leer artículo {ARROW}</a></div></div></article>'''

def page_blogs(intro):
    body = hero('<em>Blog</em>', 'Guías, planes y sabores para vivir Medellín como un local.', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-4.webp', [('Inicio', '{{R}}'), ('Blog', '')], 'Haven · Medellín')
    body += f'<section class="sec" id="c"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">Medellín</p><h2 style="margin-top:20px">Historias de la <em>ciudad</em></h2></div><p>{e(intro)}</p></div><div class="cards">{"".join(blog_card(p) for p in BLOG)}</div></div></section>\n'
    return body

def page_post(p):
    bl = flat('blog-' + p['slug_es'])
    html_, heads = prose(bl)
    big = re.sub(r'-\d+x\d+(\.\w+)$', r'\1', p['img'])
    others = ''.join(blog_card(o) for o in BLOG if o is not p)
    body = hero(e(p['title']), p['ex'] + '…', big, [('Inicio', '{{R}}'), ('Blog', '{{R}}blogs/'), (p['title'][:40] + ('…' if len(p['title']) > 40 else ''), '')], p['date'])
    toc = ''.join(f'<li><a href="#{h}">{e(t)}</a></li>' for h, t in heads)
    body += f'<section class="sec" id="c"><div class="wrap doc-grid"><aside class="toc"><p class="eyebrow">En este artículo</p><ol>{toc}</ol></aside><article class="prose" data-reveal>{html_}<div style="margin-top:50px;display:flex;gap:10px;flex-wrap:wrap">{btn("Reservar en Haven", "#reservar", "gold", "blog_reservar")}{btn("Volver al blog", "{{R}}blogs/", "ghost")}</div></article></div></section>\n'
    body += f'<section class="sec" style="padding-top:0"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">Sigue leyendo</p><h2 style="margin-top:20px">Más <em>historias</em></h2></div></div><div class="cards">{others}</div></div></section>\n'
    body += bookbar()
    return body

def svc_titles():
    out = []
    for b in flat('servicios'):
        if b['type'] == 'heading' and b['level'] in (3, 4):
            out.append(clean(b['text']))
    return out

EN_META = {
    'habitaciones': ('Rooms and suites | Haven Medellín, El Poblado', 'Penthouse, presidential suite, suites with jacuzzi and design rooms in El Poblado, Medellín. Book direct at the best rate.'),
    'penthouse': ('Penthouse with jacuzzi | Haven Medellín', 'Emperor Suites 901 and 902: duplex penthouse of up to 138 m² for 8 guests in El Poblado, Medellín.'),
    'presidencial': ('Presidential Suite | Haven Medellín', 'Presidential Suite of 145 m²: four-bedroom duplex with a private indoor pool in El Poblado, Medellín.'),
    'suites-de-lujo': ('Luxury suites with jacuzzi | Haven Medellín', 'Imperial Suite 402 and Excelsior Suites 401, 604 and 804: suites with jacuzzi in El Poblado, Medellín.'),
    'rooftop': ('360° Rooftop: pool and bar overlooking Medellín | Haven', 'Bar and pool on the 11th floor with a 360° panoramic view of Medellín. Signature cocktails, Sunny Days and sunsets in El Poblado.'),
    'swing': ('Swing Bar: coffee by day, cocktails by night | Haven Medellín', 'Swing Bar in the Haven lobby: Colombian coffee in the morning, wine, beer and cocktails at night in El Poblado.'),
    'gastronomia': ('Gastronomy at the 360° Rooftop | Haven Medellín', 'Artisan pizzas, salmon, meatballs and local and international cuisine overlooking Medellín at the 360° Rooftop.'),
    'wellness': ('Wellness: massages, facials and IV therapy | Haven Medellín', 'Relaxing massage, facial hydration and Ultra Vit IV therapy to recover from your trip at Haven, El Poblado.'),
    'eventos': ('Events: parties, brunch and celebrations | Haven Medellín', 'Live DJ on the 360 Terrace, first-Friday party, pool party, Sunday brunch and special celebrations in El Poblado.'),
    'servicios': ('Hotel services | Haven Medellín', 'Airport transfers, spa, IV hydration, tours around Antioquia, car rental and more services at Haven Medellín.'),
    'contacto': ('Contact | Haven Medellín, El Poblado', 'Calle 10A #36-29, El Poblado, Medellín. Front desk (+57) 322 306 4585 · Reservations (+57) 321 209 7033 · frontdesk@serenahotelsco.com'),
    'serena-rewards': ('Serena Rewards: loyalty program | Haven Medellín', 'Silver, Gold, Diamond and Black plans: discounts on rooms and food, transfers, upgrades and 360° Rooftop access.'),
    'politica-de-visitantes': ('Visitor policy | Haven Medellín', 'Guest and visitor registration, additional-person charges and rules under Colombian Law 679 of 2011 at Haven Medellín.'),
    'terminos-y-condiciones': ('Terms and conditions | Haven Medellín', 'Reservations, cancellations, minibar, guarantee, non-smoking policy, privacy and hours at Haven, A Design Hotel.'),
    'blogs': ('Blog: Medellín guides and plans | Haven Medellín', 'Tips, guides and experiences to enjoy Medellín: gastronomy, paisa culture and the best things to do in the city.'),
    'preguntas-frecuentes': ('FAQ | Haven Medellín', 'Location, getting here from the airport, included services, 360° Rooftop hours and safety in El Poblado.'),
}

def main():
    load_covers()
    for s_, c_, f_ in ROOMS:
        ES_NAMES[s_] = room_data(s_, C_ES)['name']
    rooms_raw = [(room_data(s), c, f) for s, c, f in ROOMS]
    pages = json.load(open(os.path.join(SRC, 'pages.json'), encoding='utf-8'))
    pages = [p for p in pages if p.get('static') or p.get('lang', 'es') != LANG]
    import build
    if LANG == 'en':
        import i18n_en
    INV = {v: k for k, v in build.NAV_MAP.items()}
    def add(slug, key, title, desc, body, extra=''):
        canonical = INV.get(slug + '/', '/es/' + slug + '/')
        if LANG == 'en':
            es_slug = slug
            slug = build.to_en(slug + '/').rstrip('/')
            canonical = '/' + slug + '/'
            body = i18n_en.tr(body)
            title, desc = EN_META.get(es_slug, (i18n_en.tr(title), i18n_en.tr(desc)))
            fn = 'en_' + es_slug.replace('/', '_') + '.html'
        else:
            fn = (slug.replace('/', '_') or 'index') + '.html'
        if len(title) > 62 and ' | ' in title:
            t0 = title.split(' | ')[0]
            title = (t0 if len(t0) <= 62 else t0[:59].rsplit(' ', 1)[0] + '…')
        with open(os.path.join(SRC, 'pages', fn), 'w', encoding='utf-8') as f:
            f.write(body)
        pages.append(dict(slug=slug, key=key, file=fn, title=title, desc=desc, head_extra=extra, lang=LANG, canonical=canonical))
    add('habitaciones', 'habitaciones', 'Habitaciones y suites | Haven Medellín, El Poblado', 'Penthouse, suite presidencial, suites con jacuzzi y habitaciones de diseño en El Poblado, Medellín. Reserva directo al mejor precio.', page_habitaciones(rooms_raw))
    add('penthouse', 'penthouse', 'Penthouse con jacuzzi | Haven Medellín', 'Suites Emperor 901 y 902: penthouse dúplex de hasta 138 m² para 8 personas en El Poblado, Medellín.', page_category('penthouse', '<em>Penthouse</em>', 'Penthouse', 'penthouse', rooms_raw, '/wp-content/uploads/2026/06/emperor-phenthouse-901-room-kitchen-lounge-area.webp'))
    add('presidencial', 'presidencial', 'Suite Presidencial | Haven Medellín', 'Suite Presidencial de 145 m²: dúplex de 4 habitaciones con piscina interior privada en El Poblado, Medellín.', page_category('presidencial', 'Suite <em>Presidencial</em>', 'Presidencial', 'presidencial', rooms_raw, '/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1.webp'))
    add('suites-de-lujo', 'suites', 'Suites de lujo con jacuzzi | Haven Medellín', 'Suite Imperial 402 y Suites Excelsior 401, 604 y 804: suites con jacuzzi en El Poblado, Medellín.', page_category('suites-de-lujo', 'Suites de <em>lujo</em>', 'Suites de lujo', 'suites', rooms_raw, '/wp-content/uploads/2026/06/imperial-suite-402-room-jacuzzi-lounge-area-2.webp'))
    for r, c, f in rooms_raw:
        m2, per, beds, dup = spec_parts(r['specs'])
        EN_META['habitacion/' + r['slug']] = (f"{r['name']} · {m2} in El Poblado | Haven Medellín", (f"{r['name']}: {m2}, {per}, {', '.join(beds)}. " + (r['desc'][0] if r['desc'] else ''))[:155])
        num = lambda x: int(re.match(r'(\d+)', x).group(1)) if x and re.match(r'(\d+)', x) else None
        url_ = 'https://havenmedellin.com' + (('/en/' + build.EN_ROOM[r['slug']] + '/') if LANG == 'en' else ('/es/' + r['slug'] + '/'))
        sch = {"@context": "https://schema.org", "@type": "HotelRoom", "name": r['name'], "url": url_, "description": (r['desc'][0] if r['desc'] else '')[:300],
               "image": ['https://havenmedellin.com' + u for u in r['imgs'][:6]],
               "occupancy": {"@type": "QuantitativeValue", "maxValue": num(per) or 2, "unitCode": "C62"},
               "amenityFeature": [{"@type": "LocationFeatureSpecification", "name": a, "value": True} for a in r['amen'][:15]],
               "containedInPlace": {"@type": "Hotel", "name": "Haven, A Design Hotel", "url": "https://havenmedellin.com/", "address": {"@type": "PostalAddress", "streetAddress": "Calle 10A #36-29", "addressLocality": "Medellín", "addressCountry": "CO"}}}
        if num(m2): sch["floorSize"] = {"@type": "QuantitativeValue", "value": num(m2), "unitCode": "MTK"}
        if beds: sch["bed"] = [{"@type": "BedDetails", "typeOfBed": b} for b in beds]
        room_ld = '<script type="application/ld+json">' + json.dumps(sch, ensure_ascii=False) + '</script>\n'
        add('habitacion/' + r['slug'], 'room', f"{r['name']} · {m2} en El Poblado | Haven Medellín", (f"{r['name']}: {m2}, {per}, {', '.join(beds)}. " + (r['desc'][0] if r['desc'] else ''))[:155], page_room(r, c, f, rooms_raw), room_ld)
    add('rooftop', 'rooftop', '360° Rooftop: piscina y bar con vista a Medellín | Haven', 'Bar y piscina en el piso 11 con vista panorámica de 360° de Medellín. Cócteles de autor, Sunny Days y atardeceres en El Poblado.',
        page_experience('360-rooftop', '360° Rooftop <em>&amp; Restaurant</em>', 'Medellín desde el cielo: piscina, cócteles de autor y la mejor vista de El Poblado.', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-4.webp', '360° Rooftop', 'Atardeceres <em>en el 11</em>', 'Hola, quiero reservar en el 360 Rooftop', 'rooftop'))
    add('swing', 'swing', 'Swing Bar: café de día, cócteles de noche | Haven Medellín', 'Swing Bar en el vestíbulo de Haven: café colombiano por la mañana, vinos, cervezas y cócteles por la noche en El Poblado.',
        page_experience('swing-bar', 'Swing <em>Coffee Bar</em>', 'Café colombiano por la mañana, cócteles de autor por la noche.', '/wp-content/uploads/2026/06/swing-bar-6.webp', 'Swing Bar', 'El <em>Swing</em>', 'Hola, quiero reservar en el Swing Bar', 'swing', casa_enzo()))
    add('gastronomia', 'gastronomia', 'Gastronomía en el 360° Rooftop | Haven Medellín', 'Pizzas artesanales, salmón, albóndigas y cocina local e internacional con vista a Medellín en el 360° Rooftop.', page_gastronomia())
    add('wellness', 'wellness', 'Wellness: masajes, faciales y sueroterapia | Haven Medellín', 'Masaje relajante, hidratación facial y terapia de sueros Ultra Vit para recuperarte del viaje en Haven, El Poblado.',
        page_experience('wellness', '<em>Wellness</em>', 'Rituales para recuperar el equilibrio después del viaje.', '/wp-content/uploads/2026/07/masaje-relajante.webp', 'Wellness', 'Bienestar <em>y equilibrio</em>', 'Hola, quiero agendar un servicio de wellness', 'wellness'))
    add('eventos', 'eventos', 'Eventos: fiestas, brunch y celebraciones | Haven Medellín', 'DJ en vivo en la Terraza 360, fiesta del primer viernes, fiesta en la piscina, brunch dominical y celebraciones especiales en El Poblado.',
        page_experience('eventos', '<em>Eventos</em>', 'La noche cambia de ritmo en el piso 11: DJ en vivo, fiestas en la piscina y brunch dominical.', '/wp-content/uploads/2026/06/vida-nocturna-5.webp', 'Eventos', 'Noches <em>en el 11</em>', 'Hola, quiero reservar para un evento', 'eventos'))
    add('servicios', 'servicios', 'Servicios del hotel | Haven Medellín', 'Traslados al aeropuerto, spa, hidratación intravenosa, tours por Antioquia, alquiler de carros y más servicios en Haven Medellín.', page_servicios())
    add('contacto', 'contacto', 'Contacto | Haven Medellín, El Poblado', 'Calle 10A #36-29, El Poblado, Medellín. Recepción (+57) 322 306 4585 · Reservas (+57) 321 209 7033 · frontdesk@serenahotelsco.com', page_contacto())
    add('serena-rewards', 'rewards', 'Serena Rewards: programa de fidelización | Haven Medellín', 'Planes Silver, Gold, Diamond y Black: descuentos en alojamiento y alimentos, traslados, upgrades y acceso al 360° Rooftop.', page_rewards())
    fb, fx = page_faq()
    add('preguntas-frecuentes', 'faq', 'Preguntas frecuentes | Haven Medellín', 'Ubicación, cómo llegar desde el aeropuerto, servicios incluidos, horarios del 360° Rooftop y seguridad en El Poblado.', fb, fx)
    add('politica-de-visitantes', 'legal', 'Política de visitantes | Haven Medellín', 'Registro de huéspedes y visitantes, cargos por persona adicional y normas según la Ley 679 de 2011 en Haven Medellín.', page_legal('politica-de-visitantes', 'Política de <em>visitantes</em>', 'Registro de huéspedes y visitantes en Haven, de acuerdo con la ley colombiana.', 'Política de visitantes'))
    add('terminos-y-condiciones', 'legal', 'Términos y condiciones | Haven Medellín', 'Reservas, cancelaciones, minibar, garantía, política de no fumar, privacidad y horarios de Haven, A Design Hotel.', page_legal('terminos-y-condiciones', 'Términos y <em>condiciones</em>', 'Reservas, garantía, privacidad y horarios del hotel.', 'Términos y condiciones'))
    intro = load_blog()
    add('blogs', 'blog', 'Blog: guías y planes en Medellín | Haven Medellín', 'Recomendaciones, guías y experiencias para disfrutar Medellín: gastronomía, cultura paisa y las mejores cosas que hacer en la ciudad.', page_blogs(intro))
    for p in BLOG:
        EN_META['blog/' + p['slug']] = (p['title'] + ' | Haven Medellín', p['ex'][:150])
        pj = J('blog-' + p['slug_es']).get('post', {})
        url_ = 'https://havenmedellin.com/' + (('en/' + build.BLOG_EN[p['slug']]) if LANG == 'en' else ('es/' + p['slug'])) + '/'
        bp = {"@context": "https://schema.org", "@type": "BlogPosting", "headline": p['title'][:110], "url": url_, "mainEntityOfPage": url_,
              "image": 'https://havenmedellin.com' + re.sub(r'-\d+x\d+(\.\w+)$', r'\1', p['img']), "datePublished": pj.get('published'), "dateModified": pj.get('modified') or pj.get('published'),
              "inLanguage": LANG, "author": {"@type": "Organization", "name": "Haven, A Design Hotel"}, "publisher": {"@type": "Organization", "name": "Haven, A Design Hotel", "logo": {"@type": "ImageObject", "url": "https://havenmedellin.com/wp-content/uploads/2026/06/logo-haven.webp"}}}
        add('blog/' + p['slug'], 'post', p['title'] + ' | Haven Medellín', p['ex'][:150], page_post(p), '<script type="application/ld+json">' + json.dumps(bp, ensure_ascii=False) + '</script>\n')
    json.dump(pages, open(os.path.join(SRC, 'pages.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print(len(pages), 'páginas')

if __name__ == '__main__':
    main()
