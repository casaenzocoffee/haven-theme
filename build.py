#!/usr/bin/env python3
"""Arma el prototipo multipágina de Haven a partir de partials + pages.
Salida: dist/ (lista para subir a /prototipo-haven/)."""
import json, os, re, shutil, hashlib, html as htmlmod
from imgvariants import variants

SRC = os.path.join(os.path.dirname(__file__), 'src')
DIST = os.path.join(os.path.dirname(__file__), 'dist')

def rd(p):
    with open(os.path.join(SRC, p), encoding='utf-8') as f:
        return f.read()

PAGES = json.loads(rd('pages.json'))

# Rutas reales del sitio -> rutas del prototipo
NAV_MAP = {
    '/es/': '',
    '/es/todas-las-habitaciones/': 'habitaciones/',
    '/es/360-rooftop/': 'rooftop/',
    '/es/swing-bar/': 'swing/',
    '/es/gastronomia/': 'gastronomia/',
    '/es/wellness/': 'wellness/',
    '/es/eventos/': 'eventos/',
    '/es/contacto/': 'contacto/',
    '/es/penthouse/': 'penthouse/', '/es/presidencial/': 'presidencial/', '/es/suites-de-lujo/': 'suites-de-lujo/',
    '/es/servicios/': 'servicios/', '/es/serena-rewards/': 'serena-rewards/', '/es/preguntas-frecuentes/': 'preguntas-frecuentes/',
}
for _s in ['suite-emperor-901','suite-emperor-902','suite-presidential','suite-imperial-402','suite-excelsior-401','suite-excelsior-804','suite-excelsior-604',
           'habitacion-ambassador','habitacion-ambassador-jr','habitacion-viscount-803','habitacion-viscount','habitacion-lord','habitacion-earl',
           'habitacion-marques-plus','habitacion-marques','habitacion-duke','habitacion-consort']:
    NAV_MAP['/es/'+_s+'/'] = 'habitacion/'+_s+'/'
NAV_MAP['/es/suite-presidencial/'] = 'habitacion/suite-presidential/'
for _s in ['politica-de-visitantes', 'terminos-y-condiciones', 'blogs', 'guia-medellin', 'que-suite-elegir']:
    NAV_MAP['/es/' + _s + '/'] = _s + '/'
BLOG_EN = {'experiencias-gastronomicas-de-cinco-estrellas-en-medellin': 'five-star-gastronomic-experiences-in-medellin',
    'las-10-mejores-cosas-que-hacer-y-ver-en-la-ciudad-de-la-eterna-primavera': 'the-10-best-things-to-do-and-see-in-the-city-of-eternal-spring',
    'conozca-la-cultura-paisa-y-descubra-sus-tradiciones-en-una-experiencia-unica': 'get-to-know-the-paisa-culture-and-discover-its-traditions-in-a-unique-experience'}
for _s in BLOG_EN:
    NAV_MAP['/es/' + _s + '/'] = 'blog/' + _s + '/'

ES2EN_PATH = {
    '': 'en/', 'habitaciones/': 'en/all-rooms/', 'penthouse/': 'en/penthouse/', 'presidencial/': 'en/presidential/', 'suites-de-lujo/': 'en/luxury-suites/',
    'rooftop/': 'en/360-rooftop/', 'swing/': 'en/swing-bar/', 'gastronomia/': 'en/gastronomy/', 'wellness/': 'en/wellness/', 'eventos/': 'en/events/',
    'servicios/': 'en/services/', 'contacto/': 'en/contact/', 'serena-rewards/': 'en/serena-rewards/', 'preguntas-frecuentes/': 'en/frequently-asked-questions/',
    'politica-de-visitantes/': 'en/visitor-policy/', 'terminos-y-condiciones/': 'en/terms-and-conditions/', 'blogs/': 'en/blogs/',
    'guia-medellin/': 'en/medellin-guide/', 'que-suite-elegir/': 'en/which-suite/',
}
EN_ROOM = {'suite-emperor-901': 'emperor-suite-901', 'suite-emperor-902': 'emperor-suite-902', 'suite-presidential': 'presidential-suite',
    'suite-imperial-402': 'imperial-suite-402', 'suite-excelsior-401': 'excelsior-suite-401', 'suite-excelsior-804': 'excelsior-suite-804', 'suite-excelsior-604': 'excelsior-suite-604',
    'habitacion-ambassador': 'ambassador-room', 'habitacion-ambassador-jr': 'ambassador-jr-room', 'habitacion-viscount-803': 'viscount-room-803', 'habitacion-viscount': 'viscount-room',
    'habitacion-lord': 'lords-room', 'habitacion-earl': 'earls-room', 'habitacion-marques-plus': 'marques-plus-room', 'habitacion-marques': 'marques-room',
    'habitacion-duke': 'duke-room', 'habitacion-consort': 'consort-room'}
for _k, _v in EN_ROOM.items():
    ES2EN_PATH['habitacion/' + _k + '/'] = 'en/' + _v + '/'
for _k, _v in BLOG_EN.items():
    ES2EN_PATH['blog/' + _k + '/'] = 'en/' + _v + '/'
EN2ES_PATH = {v: k for k, v in ES2EN_PATH.items()}
# URLs reales en inglés -> ruta del prototipo
NAV_MAP_EN = {'/' + v: v for v in ES2EN_PATH.values()}

def to_en(p):
    return ES2EN_PATH.get(p, p)

FONTS = 'https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap'
REAL = {}
GVER = ''
# Contenedor de Google Tag Manager (GA4, Google Ads, Meta). Vacío = no se carga.
GTM_ID = 'GTM-TVFGX8W'
# Solo se carga en producción (HV_PROD=1), para que las visitas y pruebas del prototipo no ensucien GA4 ni las conversiones de Google Ads.
GTM_ON = os.environ.get('HV_PROD') == '1'

# Herramientas "Medellín desde Haven" (mismo código del plugin haven-guia).
# En una página se insertan con <!--HG:guia-->, <!--HG:mapa--> o <!--HG:quiz-->.
GUIA_MODS = {'guia': ['mapa', 'plan', 'quiz'], 'mapa': ['mapa'], 'quiz': ['quiz']}

def guia_insert(body, page):
    lang = 'en' if page.get('lang') == 'en' else 'es'
    mods = []
    def rep(m):
        for x in GUIA_MODS[m.group(1)]:
            if x not in mods: mods.append(x)
        h = rd(f'guia/{m.group(1)}_{lang}.html').replace('class="hg-map__stage"', 'class="hg-map__stage" data-lenis-prevent')
        if m.group(1) != 'guia':
            h = re.sub(r'(hg-eyebrow">)(?:Capítulo|Chapter) \d+ · ', r'\1', h)
        return h
    body = re.sub(r'<!--HG:(guia|mapa|quiz)-->', rep, body)
    page['_hg'] = mods
    return body

def guia_scripts(page, R, tr, gver):
    mods = page.get('_hg')
    if not mods: return ''
    lang = 'en' if page.get('lang') == 'en' else 'es'
    d = json.loads(rd(f'guia/data_{lang}.json'))
    if 'mapa' not in mods and 'plan' not in mods:
        d['lugares'] = []
    if 'quiz' not in mods:
        d['suites'] = [{k: v for k, v in x.items() if k in ('id', 'n', 'url', 'img', 'cat')} for x in d['suites']]
    data = json.dumps(d, ensure_ascii=False, separators=(',', ':')).replace('<', '\\u003C').replace('>', '\\u003E').replace('&', '\\u0026')
    data = re.sub(r'\{\{R\}\}([a-z0-9/-]*)', lambda m: R + tr(m.group(1)), data)
    out = f'<script type="application/json" id="haven-guia-data">{data}</script>\n<script src="{R}assets/hg-core.js?v={gver}" defer></script>\n'
    out += ''.join(f'<script src="{R}assets/hg-{x}.js?v={gver}" defer></script>\n' for x in mods)
    return out

def head(page, R, ver):
    canonical = 'https://havenmedellin.com' + page.get('canonical', '/es/')
    og = page.get('og', '/wp-content/uploads/2026/06/hotel-haven-medellin.webp')
    extra = page.get('head_extra', '')
    if page.get('extra_head_file'):
        extra += rd(page['extra_head_file']).replace('{{R}}', R)
    en = page.get('lang') == 'en'
    sl = page['slug'] + '/' if page['slug'] else ''
    es_p = EN2ES_PATH.get(sl, sl) if en else sl
    en_p = sl if en else ES2EN_PATH.get(sl)
    es_u, en_u = REAL.get(es_p), REAL.get(en_p) if en_p else None
    alts = ''
    if es_u: alts += f'<link rel="alternate" hreflang="es" href="{es_u}">\n<link rel="alternate" hreflang="x-default" href="{es_u}">\n'
    if en_u: alts += f'<link rel="alternate" hreflang="en" href="{en_u}">\n'
    if og.startswith('/'): og = 'https://havenmedellin.com' + og
    crumbs = page.get('_crumbs')
    if crumbs:
        extra += '<script type="application/ld+json">' + json.dumps({"@context": "https://schema.org", "@type": "BreadcrumbList", "itemListElement": [
            {"@type": "ListItem", "position": i + 1, "name": n, **({"item": u} if u else {})} for i, (n, u) in enumerate(crumbs)]}, ensure_ascii=False) + '</script>\n'
    gtm = ("<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" + GTM_ID + "');</script>\n") if (GTM_ID and GTM_ON) else ''
    hgcss = f'\n<link rel="stylesheet" href="{R}assets/haven-guia.css?v={GVER}">' if page.get('_hg') else ''
    preload = ''.join(f'<link rel="preload" as="image" href="{u}" fetchpriority="high">\n' for u in page.get('preload', []))
    return f'''<!doctype html>
<html lang="{'en' if en else 'es'}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{page['title']}</title>
<meta name="description" content="{page['desc']}">
<meta name="robots" content="noindex, nofollow">
<link rel="canonical" href="{canonical}">
<meta property="og:type" content="{page.get('og_type', 'website')}">
<meta property="og:url" content="{canonical}">
<meta property="og:site_name" content="Haven, A Design Hotel">
<meta property="og:locale" content="{'en_US' if en else 'es_CO'}">
{alts}
<meta property="og:title" content="{page['title']}">
<meta property="og:description" content="{page['desc']}">
<meta property="og:image" content="{og}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#191311">
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="{FONTS}">
<link rel="stylesheet" href="{FONTS}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{FONTS}"></noscript>
{preload}<link rel="stylesheet" href="{R}assets/haven.css?v={ver}">{hgcss}
{gtm}{extra}</head>
'''

def header(slug, R, en=False):
    h = rd('partials/header_en.html' if en else 'partials/header.html')
    sl = slug + '/' if slug else ''
    es_p = EN2ES_PATH.get(sl, sl) if en else sl
    en_p = sl if en else ES2EN_PATH.get(sl, 'en/')
    h = h.replace('{{ES}}', (R + es_p) or './').replace('{{EN}}', R + en_p)
    NM = NAV_MAP_EN if en else NAV_MAP
    def rep(m):
        href = m.group(1)
        if href in NM:
            target = NM[href]
            cur = ' aria-current="page"' if target == (slug + '/' if slug else '') else ''
            return f'href="{R}{target}"{cur}'
        return m.group(0)
    h = h.replace(' aria-current="page"', '')
    h = re.sub(r'href="(/e[sn]/[^"]*)"', rep, h)
    return h

def footer(R, en=False):
    f = rd('partials/footer_en.html' if en else 'partials/footer.html')
    NM = NAV_MAP_EN if en else NAV_MAP
    return re.sub(r'href="(/e[sn]/[^"]*)"', lambda m: f'href="{R}{NM[m.group(1)]}"' if m.group(1) in NM else m.group(0), f)

def _brand_txt(t):
    t = re.sub(r'Rooftop 360°?', '360° Rooftop', t)
    t = re.sub(r'360° Rooftop(?! &| <em>&)(?!\s+$)', '360° Rooftop &amp; Restaurant', t)
    t = re.sub(r'\bSwing Bar\b', 'Swing Coffee Bar', t)
    t = re.sub(r'\bSwing\b(?! Coffee| Bar| Menu)(?!\s+$)', 'Swing Coffee Bar', t)
    return t

def brand(html):
    """Nombres de marca: '360° Rooftop & Restaurant' y 'Swing Coffee Bar' en todo el texto visible."""
    parts = re.split(r'(<script\b.*?</script>|<style\b.*?</style>|<[^>]+>)', html, flags=re.S)
    out = []
    for p in parts:
        if not p:
            continue
        if p.startswith('<script') or p.startswith('<style'):
            out.append(p)
        elif p.startswith('<'):
            out.append(re.sub(r'\b(alt|title|aria-label|content)="([^"]*)"', lambda m: f'{m.group(1)}="{_brand_txt(m.group(2)) if not m.group(2).startswith(("http", "/", "width")) else m.group(2)}"', p))
        else:
            out.append(_brand_txt(p))
    return ''.join(out)

def responsive(html):
    """Añade srcset/sizes a las imágenes de WordPress y usa la variante de 768 px como src por defecto."""
    def rep(m):
        tag = m.group(0)
        if 'srcset=' in tag or 'class="poster"' in tag:
            return tag
        sm = re.search(r'\bsrc="([^"]+)"', tag)
        v = variants(sm.group(1)) if sm else None
        if not v:
            return tag
        orig, vs = v
        hero = 'fetchpriority="high"' in tag
        big = max(w for _, w in vs)
        cand = [f'{u} {w}w' for u, w in vs] + ([f'{orig} 1600w'] if big < 1500 else [])
        # en celular se pide la variante de 768 px (nítida a 2x) en vez del original pesado
        sizes = '(max-width: 820px) 256px, 100vw' if hero else '(max-width: 820px) 256px, 48vw'
        src_default = vs[0][0]
        tag = tag.replace(sm.group(0), f'src="{src_default}" srcset="{", ".join(cand)}" sizes="{sizes}"')
        if 'decoding=' not in tag:
            tag = tag.replace('<img ', '<img decoding="async" ', 1)
        return tag
    return re.sub(r'<img\b[^>]*>', rep, html)

def build():
    if os.path.isdir(DIST):
        shutil.rmtree(DIST)
    os.makedirs(os.path.join(DIST, 'assets'))
    css = rd('assets/haven.css'); js = rd('assets/haven.js')
    ver = hashlib.sha1((css + js + rd('assets/clock3d.js')).encode()).hexdigest()[:8]
    global GVER
    GVER = hashlib.sha1(''.join(rd('assets/' + a) for a in ('haven-guia.css', 'hg-core.js', 'hg-mapa.js', 'hg-plan.js', 'hg-quiz.js')).encode()).hexdigest()[:8]
    for a in os.listdir(os.path.join(SRC, 'assets')):
        shutil.copy(os.path.join(SRC, 'assets', a), os.path.join(DIST, 'assets', a))
    if os.path.isdir(os.path.join(SRC, 'media')):
        shutil.copytree(os.path.join(SRC, 'media'), os.path.join(DIST, 'media'))
    for a in os.listdir(os.path.join(SRC, 'root')):
        shutil.copy(os.path.join(SRC, 'root', a), os.path.join(DIST, a))
    for page in PAGES:
        REAL[page['slug'] + '/' if page['slug'] else ''] = 'https://havenmedellin.com' + page.get('canonical', '/es/')
    MV = hashlib.sha1(b''.join(open(os.path.join(SRC, 'media', f), 'rb').read() for f in sorted(os.listdir(os.path.join(SRC, 'media'))))).hexdigest()[:8]
    for page in PAGES:
        slug = page['slug']
        R = '' if not slug else '../' * (slug.count('/') + 1)
        body = rd('pages/' + page['file'])
        body = guia_insert(body, page)
        cm = re.search(r'<nav class="crumbs"[^>]*>(.*?)</nav>', body, re.S)
        if cm:
            cr = []
            for m in re.finditer(r'<a href="([^"]*)">([^<]*)</a>|<span>([^<]+)</span>', cm.group(1)):
                if m.group(2) is not None:
                    p = m.group(1).replace('{{R}}', '')
                    p = to_en(p) if page.get('lang') == 'en' else p
                    cr.append((_brand_txt(htmlmod.unescape(m.group(2))).replace("&amp;", "&"), REAL.get(p)))
                else:
                    cr.append((_brand_txt(htmlmod.unescape(m.group(3))).replace("&amp;", "&"), None))
            page['_crumbs'] = cr
        if not page.get('og'):
            om = re.search(r'class="ph-media"><img src="([^"]+)"', body)
            if om: page['og'] = om.group(1)
        top = ''
        if '<!--BODY-->' in body:
            top, body = body.split('<!--BODY-->', 1)
        en = page.get('lang') == 'en'
        tr = to_en if en else (lambda p: p)
        body = re.sub(r'href="(/es/[^"]*)"', lambda m: f'href="{R}{tr(NAV_MAP[m.group(1)])}"' if m.group(1) in NAV_MAP else m.group(0), body)
        body = re.sub(r'href="(/en/[^"]*)"', lambda m: f'href="{R}{NAV_MAP_EN[m.group(1)]}"' if m.group(1) in NAV_MAP_EN else m.group(0), body)
        body = re.sub(r'\{\{R\}\}([a-z0-9/-]*)', lambda m: R + tr(m.group(1)), body)
        scripts = f'''<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js" defer></script>
<script src="{R}assets/haven.js?v={ver}" defer></script>
'''
        scripts += guia_scripts(page, R, (to_en if page.get('lang') == 'en' else (lambda p: p)), GVER)
        if page.get('clock'):
            scripts += f'<script type="module" src="{R}assets/clock3d.js?v={ver}"></script>\n'
        html = (head(page, R, ver) + f'<body class="no-js page-{page.get("key", slug or "home")}">\n' + top
                + rd('partials/nojs.html') + header(slug, R, en) + body + '\n' + footer(R, en) + scripts + '</body>\n</html>\n')
        html = re.sub(r'(media/[\w.-]+\.(?:mp4|webp))"', lambda m: m.group(1) + '?v=' + MV + '"', html)
        html = brand(html)
        html = responsive(html)
        out = os.path.join(DIST, slug, 'index.html') if slug else os.path.join(DIST, 'index.html')
        os.makedirs(os.path.dirname(out), exist_ok=True)
        with open(out, 'w', encoding='utf-8') as f:
            f.write(html)
        print(f'{slug or "/":28} {len(html):7d} bytes')

def sitemap():
    out = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">']
    for p in PAGES:
        sl = p['slug'] + '/' if p['slug'] else ''
        en = p.get('lang') == 'en'
        es_p = EN2ES_PATH.get(sl, sl) if en else sl
        en_p = sl if en else ES2EN_PATH.get(sl)
        out.append(f'<url><loc>{REAL[sl]}</loc>' + ''.join(f'<xhtml:link rel="alternate" hreflang="{l}" href="{REAL[x]}"/>' for l, x in (('es', es_p), ('en', en_p)) if x in REAL) + '</url>')
    out.append('</urlset>')
    open(os.path.join(DIST, 'sitemap-preview.xml'), 'w').write('\n'.join(out))

if __name__ == '__main__':
    build()
    sitemap()
