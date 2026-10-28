#!/usr/bin/env python3
"""Exporta el contenido del prototipo (gen.py) a JSON estructurado para el importador del tema WordPress.
Uso: HV_LANG=es python3 export_bundle.py  (y luego con HV_LANG=en)"""
import os, sys, json, re
sys.path.insert(0, '/home/claude/haven-site'); sys.path.insert(0, '/home/claude/haven-wp')
os.chdir('/home/claude/haven-site')
import gen, build, rewards
L = gen.LANG
tr = (lambda s: s)
if L == 'en':
    import i18n_en
    _CAT = {'Presidencial': 'Presidential', 'Suites de lujo': 'Luxury suites', 'Habitaciones': 'Rooms', 'Penthouse': 'Penthouse'}
    tr = lambda s: _CAT.get(s) or i18n_en.tr(s)

def href_token(h):
    """Convierte enlaces del prototipo en tokens que el tema resuelve a permalinks reales."""
    if h.startswith('{{R}}'):
        p = h[5:]
        return 'page:' + (p.strip('/') or 'home')
    return h

def btns(s, wa):
    out, seen = [], set()
    for t, h in s['btns'][:3]:
        if not t or t.lower() in seen: continue
        seen.add(t.lower())
        href, ext = gen.fixhref(h, wa)
        out.append({'t': t.capitalize() if t.islower() else t, 'href': href_token(href), 'ext': bool(ext)})
    return out

def sections(slug, wa=''):
    return [{'eyebrow': s['eyebrow'], 'title': s['title'], 'paras': s['paras'], 'imgs': s['imgs'], 'btns': btns(s, wa), 'lists': s['lists']} for s in gen.sections_of(slug)]

gen.load_covers()
for s_, c_, f_ in gen.ROOMS:
    gen.ES_NAMES[s_] = gen.room_data(s_, gen.C_ES)['name']
rooms = []
for s, cat, f in gen.ROOMS:
    r = gen.room_data(s)
    m2, per, beds, dup = gen.spec_parts(r['specs'])
    rooms.append({'key': 'room-' + s, 'slug_es': s, 'slug': build.EN_ROOM[s] if L == 'en' else s, 'cat': tr(cat), 'f': f,
                  'name': r['name'], 'kicker': r['kicker'], 'm2': m2, 'per': (per.replace('personas', 'people').replace('persona', 'person') if L == 'en' else per), 'beds': beds, 'dup': dup,
                  'amen': r['amen'], 'desc': r['desc'], 'serv': r['serv'], 'imgs': r['imgs'], 'cover': gen.cover(r)})

def meta(key, title, desc):
    if L == 'en':
        title, desc = gen.EN_META.get(key, (tr(title), tr(desc)))
    return {'title': title, 'desc': desc}

P = {}
d = gen.flat('todas-las-habitaciones')
P['habitaciones'] = {'intro': gen.clean(next(b['text'] for b in d if b['type'] == 'paragraph'))}
for key, slug, th, cat, f, img in [('penthouse', 'penthouse', '<em>Penthouse</em>', 'Penthouse', 'penthouse', '/wp-content/uploads/2026/06/emperor-phenthouse-901-room-kitchen-lounge-area.webp'),
                                   ('presidencial', 'presidencial', 'Suite <em>Presidencial</em>', 'Presidencial', 'presidencial', '/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1.webp'),
                                   ('suites-de-lujo', 'suites-de-lujo', 'Suites de <em>lujo</em>', 'Suites de lujo', 'suites', '/wp-content/uploads/2026/06/imperial-suite-402-room-jacuzzi-lounge-area-2.webp')]:
    dd = gen.flat(slug)
    P[key] = {'intro': gen.clean(next(b['text'] for b in dd if b['type'] == 'paragraph')), 'title_html': tr(th), 'cat': tr(cat), 'f': f, 'img': img}
EXP = [('rooftop', '360-rooftop', '360° Rooftop <em>&amp; Restaurant</em>', 'Medellín desde el cielo: piscina, cócteles de autor y la mejor vista de El Poblado.', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-4.webp', '360° Rooftop', 'Atardeceres <em>en el 11</em>', 'Hola, quiero reservar en el 360 Rooftop'),
       ('swing', 'swing-bar', 'Swing <em>Coffee Bar</em>', 'Café colombiano por la mañana, cócteles de autor por la noche.', '/wp-content/uploads/2026/06/swing-bar-6.webp', 'Swing Bar', 'El <em>Swing</em>', 'Hola, quiero reservar en el Swing Bar'),
       ('wellness', 'wellness', '<em>Wellness</em>', 'Rituales para recuperar el equilibrio después del viaje.', '/wp-content/uploads/2026/07/masaje-relajante.webp', 'Wellness', 'Bienestar <em>y equilibrio</em>', 'Hola, quiero agendar un servicio de wellness'),
       ('eventos', 'eventos', '<em>Eventos</em>', 'La noche cambia de ritmo en el piso 11: DJ en vivo, fiestas en la piscina y brunch dominical.', '/wp-content/uploads/2026/06/vida-nocturna-5.webp', 'Eventos', 'Noches <em>en el 11</em>', 'Hola, quiero reservar para un evento')]
for key, slug, h1, sub, img, crumb, gt, wa in EXP:
    P[key] = {'h1': tr(h1), 'sub': tr(sub), 'img': img, 'crumb': tr(crumb), 'gal_title': tr(gt), 'wa': tr(wa), 'sections': sections(slug, tr(wa))}
P['swing']['first_img'] = 'theme:media/swing-cafe.webp'
P['swing']['casa_enzo'] = True
P['gastronomia'] = {'sections': sections('gastronomia')}
P['servicios'] = {'sections': sections('servicios')}
fq = gen.J('preguntas-frecuentes')
P['preguntas-frecuentes'] = {'items': [{'q': gen.clean(q['question']), 'a': [gen.clean(x) for x in (q['answer'] if isinstance(q['answer'], list) else [q['answer']]) if isinstance(x, str)]} for q in fq.get('faq_items', [])]}
secs = gen.sections_of('serena-rewards')
P['serena-rewards'] = {'intro': next(p for x in secs for p in x['paras']), 'tiers': rewards.TIERS[L], 'note': rewards.NOTE[L]}
for key, th, sub, crumb in [('politica-de-visitantes', 'Política de <em>visitantes</em>', 'Registro de huéspedes y visitantes en Haven, de acuerdo con la ley colombiana.', 'Política de visitantes'),
                            ('terminos-y-condiciones', 'Términos y <em>condiciones</em>', 'Reservas, garantía, privacidad y horarios del hotel.', 'Términos y condiciones')]:
    html_, heads = gen.prose(gen.flat(key), skip_first_h=(key == 'politica-de-visitantes'))
    P[key] = {'title_html': tr(th), 'sub': tr(sub), 'crumb': tr(crumb), 'prose': html_}
intro = gen.load_blog()
P['blogs'] = {'intro': intro}
posts = []
for p in gen.BLOG:
    pj = gen.J('blog-' + p['slug_es']).get('post', {})
    html_, heads = gen.prose(gen.flat('blog-' + p['slug_es']))
    posts.append({'key': 'post-' + p['slug_es'], 'slug_es': p['slug_es'], 'slug': build.BLOG_EN[p['slug_es']] if L == 'en' else p['slug_es'],
                  'title': p['title'], 'date': p['date'], 'ex': p['ex'], 'img': re.sub(r'-\d+x\d+(\.\w+)$', r'\1', p['img']), 'prose': html_,
                  'published': pj.get('published'), 'modified': pj.get('modified')})
M = {
 'habitaciones': meta('habitaciones', 'Habitaciones y suites | Haven Medellín, El Poblado', 'Penthouse, suite presidencial, suites con jacuzzi y habitaciones de diseño en El Poblado, Medellín. Reserva directo al mejor precio.'),
 'penthouse': meta('penthouse', 'Penthouse con jacuzzi | Haven Medellín', 'Suites Emperor 901 y 902: penthouse dúplex de hasta 138 m² para 8 personas en El Poblado, Medellín.'),
 'presidencial': meta('presidencial', 'Suite Presidencial | Haven Medellín', 'Suite Presidencial de 145 m²: dúplex de 4 habitaciones con piscina interior privada en El Poblado, Medellín.'),
 'suites-de-lujo': meta('suites-de-lujo', 'Suites de lujo con jacuzzi | Haven Medellín', 'Suite Imperial 402 y Suites Excelsior 401, 604 y 804: suites con jacuzzi en El Poblado, Medellín.'),
 'rooftop': meta('rooftop', '360° Rooftop: piscina y bar con vista a Medellín | Haven', 'Bar y piscina en el piso 11 con vista panorámica de 360° de Medellín. Cócteles de autor, Sunny Days y atardeceres en El Poblado.'),
 'swing': meta('swing', 'Swing Bar: café de día, cócteles de noche | Haven Medellín', 'Swing Bar en el vestíbulo de Haven: café colombiano por la mañana, vinos, cervezas y cócteles por la noche en El Poblado.'),
 'gastronomia': meta('gastronomia', 'Gastronomía en el 360° Rooftop | Haven Medellín', 'Pizzas artesanales, salmón, albóndigas y cocina local e internacional con vista a Medellín en el 360° Rooftop.'),
 'wellness': meta('wellness', 'Wellness: masajes, faciales y sueroterapia | Haven Medellín', 'Masaje relajante, hidratación facial y terapia de sueros Ultra Vit para recuperarte del viaje en Haven, El Poblado.'),
 'eventos': meta('eventos', 'Eventos: fiestas, brunch y celebraciones | Haven Medellín', 'DJ en vivo en la Terraza 360, fiesta del primer viernes, fiesta en la piscina, brunch dominical y celebraciones especiales en El Poblado.'),
 'servicios': meta('servicios', 'Servicios del hotel | Haven Medellín', 'Traslados al aeropuerto, spa, hidratación intravenosa, tours por Antioquia, alquiler de carros y más servicios en Haven Medellín.'),
 'contacto': meta('contacto', 'Contacto | Haven Medellín, El Poblado', 'Calle 10A #36-29, El Poblado, Medellín. Recepción (+57) 322 306 4585 · Reservas (+57) 321 209 7033 · frontdesk@serenahotelsco.com'),
 'serena-rewards': meta('serena-rewards', 'Serena Rewards: programa de fidelización | Haven Medellín', 'Planes Silver, Golden, Diamond y Black: descuentos en alojamiento y alimentos, traslados, upgrades y beneficios exclusivos.'),
 'preguntas-frecuentes': meta('preguntas-frecuentes', 'Preguntas frecuentes | Haven Medellín', 'Ubicación, cómo llegar desde el aeropuerto, servicios incluidos, horarios del 360° Rooftop y seguridad en El Poblado.'),
 'politica-de-visitantes': meta('politica-de-visitantes', 'Política de visitantes | Haven Medellín', 'Registro de huéspedes y visitantes, cargos por persona adicional y normas según la Ley 679 de 2011 en Haven Medellín.'),
 'terminos-y-condiciones': meta('terminos-y-condiciones', 'Términos y condiciones | Haven Medellín', 'Reservas, cancelaciones, minibar, garantía, política de no fumar, privacidad y horarios de Haven, A Design Hotel.'),
 'blogs': meta('blogs', 'Blog: guías y planes en Medellín | Haven Medellín', 'Recomendaciones, guías y experiencias para disfrutar Medellín: gastronomía, cultura paisa y las mejores cosas que hacer en la ciudad.'),
}
if L == 'en':
    M['serena-rewards'] = {'title': 'Serena Rewards: loyalty program | Haven Medellín', 'desc': 'Silver, Golden, Diamond and Black plans: discounts on rooms and food, transfers, upgrades and exclusive benefits.'}
for r in rooms:
    t = f"{r['name']} · {r['m2']} {'in' if L == 'en' else 'en'} El Poblado | Haven Medellín"
    if len(t) > 62: t = t.split(' | ')[0] if len(t.split(' | ')[0]) <= 62 else r['name'] + ' | Haven Medellín'
    M[r['key']] = {'title': t, 'desc': (f"{r['name']}: {r['m2']}, {r['per']}, {', '.join(r['beds'])}. " + (r['desc'][0] if r['desc'] else ''))[:155]}
for p in posts:
    t = p['title'] + ' | Haven Medellín'
    M[p['key']] = {'title': t if len(t) <= 62 else (p['title'] if len(p['title']) <= 62 else p['title'][:59].rsplit(' ', 1)[0] + '…'), 'desc': p['ex'][:150]}
M['home'] = {'title': 'Haven Medellín | Hotel de diseño en El Poblado' if L == 'es' else 'Haven Medellín | Design hotel in El Poblado',
             'desc': 'Haven, A Design Hotel: suites con jacuzzi, 360° Rooftop, Swing Bar y wellness en el corazón de El Poblado, Medellín. Reserva directo al mejor precio.' if L == 'es' else 'Haven, A Design Hotel: suites with jacuzzi, 360° Rooftop, Swing Bar and wellness in the heart of El Poblado, Medellín. Book direct at the best rate.'}
# rutas reales de cada página (slug relativo a /es/ o /en/)
PATHS = {}
for k, v in build.NAV_MAP.items():
    key = v.strip('/') or 'home'
    PATHS.setdefault(key, k.strip('/').split('/', 1)[1] if '/' in k.strip('/') else '')
# Guía y quiz (páginas nuevas: títulos y SEO para crearlas en el sitio oficial)
_G = {'guia-medellin': 'guia-medellin' if L == 'es' else 'en/medellin-guide', 'que-suite-elegir': 'que-suite-elegir' if L == 'es' else 'en/which-suite'}
_PJ = {x.get('slug'): x for x in json.load(open('/home/claude/haven-site/src/pages.json'))}
TITLES = {}
for k, sl in _G.items():
    x = _PJ.get(sl, {})
    if x.get('title'): M[k] = {'title': x['title'], 'desc': x.get('desc') or x.get('description') or ''}
TITLES = {'guia-medellin': 'Guía Medellín' if L == 'es' else 'Medellín guide', 'que-suite-elegir': '¿Qué suite va contigo?' if L == 'es' else 'Which suite is yours?'}
out = {'lang': L, 'rooms': rooms, 'pages': P, 'posts': posts, 'meta': M, 'titles': TITLES}
json.dump(out, open(f'/home/claude/haven-wp/haven/import/bundle-{L}.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print(L, len(rooms), 'habitaciones', len(P), 'páginas', len(posts), 'artículos')
