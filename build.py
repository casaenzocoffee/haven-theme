#!/usr/bin/env python3
"""Arma el prototipo multipágina de Haven a partir de partials + pages.
Salida: dist/ (lista para subir a /prototipo-haven/)."""
import json, os, re, shutil, hashlib

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
}

def head(page, R, ver):
    canonical = 'https://havenmedellin.com' + page.get('canonical', '/es/')
    og = page.get('og', '/wp-content/uploads/2026/06/hotel-haven-medellin.webp')
    extra = page.get('head_extra', '')
    if page.get('extra_head_file'):
        extra += rd(page['extra_head_file'])
    preload = ''.join(f'<link rel="preload" as="image" href="{u}" fetchpriority="high">\n' for u in page.get('preload', []))
    return f'''<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{page['title']}</title>
<meta name="description" content="{page['desc']}">
<meta name="robots" content="noindex, nofollow">
<link rel="canonical" href="{canonical}">
<meta property="og:type" content="website">
<meta property="og:title" content="{page['title']}">
<meta property="og:description" content="{page['desc']}">
<meta property="og:image" content="{og}">
<meta name="theme-color" content="#191311">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap">
{preload}<link rel="stylesheet" href="{R}assets/haven.css?v={ver}">
{extra}</head>
'''

def header(slug, R):
    h = rd('partials/header.html')
    def rep(m):
        href = m.group(1)
        if href in NAV_MAP:
            target = NAV_MAP[href]
            cur = ' aria-current="page"' if target == (slug + '/' if slug else '') else ''
            return f'href="{R}{target}"{cur}'
        return m.group(0)
    h = h.replace(' aria-current="page"', '')
    h = re.sub(r'href="(/es/[^"]*)"', rep, h)
    return h

def footer(R):
    f = rd('partials/footer.html')
    return re.sub(r'href="(/es/[^"]*)"', lambda m: f'href="{R}{NAV_MAP[m.group(1)]}"' if m.group(1) in NAV_MAP else m.group(0), f)

def build():
    if os.path.isdir(DIST):
        shutil.rmtree(DIST)
    os.makedirs(os.path.join(DIST, 'assets'))
    css = rd('assets/haven.css'); js = rd('assets/haven.js')
    ver = hashlib.sha1((css + js + rd('assets/clock3d.js')).encode()).hexdigest()[:8]
    for a in os.listdir(os.path.join(SRC, 'assets')):
        shutil.copy(os.path.join(SRC, 'assets', a), os.path.join(DIST, 'assets', a))
    for page in PAGES:
        slug = page['slug']
        R = '' if not slug else '../' * (slug.count('/') + 1)
        body = rd('pages/' + page['file'])
        top = ''
        if '<!--BODY-->' in body:
            top, body = body.split('<!--BODY-->', 1)
        body = re.sub(r'href="(/es/[^"]*)"', lambda m: f'href="{R}{NAV_MAP[m.group(1)]}"' if m.group(1) in NAV_MAP else m.group(0), body)
        body = body.replace('{{R}}', R)
        scripts = f'''<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js" defer></script>
<script src="{R}assets/haven.js?v={ver}" defer></script>
'''
        if page.get('clock'):
            scripts += f'<script type="module" src="{R}assets/clock3d.js?v={ver}"></script>\n'
        html = (head(page, R, ver) + f'<body class="no-js page-{page.get("key", slug or "home")}">\n' + top
                + rd('partials/nojs.html') + header(slug, R) + body + '\n' + footer(R) + scripts + '</body>\n</html>\n')
        out = os.path.join(DIST, slug, 'index.html') if slug else os.path.join(DIST, 'index.html')
        os.makedirs(os.path.dirname(out), exist_ok=True)
        with open(out, 'w', encoding='utf-8') as f:
            f.write(html)
        print(f'{slug or "/":28} {len(html):7d} bytes')

if __name__ == '__main__':
    build()
