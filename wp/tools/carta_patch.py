#!/usr/bin/env python3
"""Aplica a una carta nueva (carta360/index.html) los ajustes del sitio del hotel.
Uso: python3 carta_patch.py ruta/a/index.html
Es idempotente: se puede correr varias veces sin duplicar nada.

1. Nombre, descripción, canonical, Open Graph y datos de restaurante para Google.
2. Efectos siempre activos (ignora "reducir movimiento").
3. "Ubicación" y "Abrir en Google Maps" van a la ficha de 360 Rooftop Bar & Restaurante en Google Maps.
4. Botón "Horarios" en el menú y ancla #horarios (si la carta no los trae).
5. Botón "Descubre nuestro menú" más grande en celular.
"""
import json, re, sys

URL = 'https://havenmedellin.com/360-rooftop-carta/'
TITLE = 'Carta 360 Rooftop Bar & Restaurante · Medellín'
DESC = ('Carta del 360 Rooftop Bar & Restaurante en el piso 11 de Haven, en Provenza, El Poblado (Medellín): '
        'desayunos, platos fuertes, hamburguesas, pizzas, postres, cócteles, vinos y más.')
MAPS = ('https://www.google.com/maps/search/?api=1&amp;query=360%20Rooftop%20Bar%20%26%20Restaurante%2C%20Calle%2010A'
        '%20%2336-29%2C%20Medell%C3%ADn&amp;query_place_id=ChIJkWmO1w4pRI4RJ_Ez8Fw5es8')
LD = {"@context": "https://schema.org", "@type": "Restaurant", "name": "360 Rooftop Bar & Restaurante",
      "alternateName": "360 Rooftop Medellín", "url": URL, "image": URL + "img/hero_poster.webp",
      "servesCuisine": ["Internacional", "Colombiana", "Cócteles"], "hasMenu": URL, "acceptsReservations": "True",
      "telephone": "+57 312 6273647",
      "address": {"@type": "PostalAddress", "streetAddress": "Calle 10A #36-29, piso 11 (Haven, A Design Hotel)",
                  "addressLocality": "Medellín", "addressRegion": "Antioquia", "addressCountry": "CO"},
      "containedInPlace": {"@type": "Hotel", "name": "Haven, A Design Hotel", "url": "https://havenmedellin.com/"},
      "openingHoursSpecification": [
          {"@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday", "Tuesday", "Wednesday"], "opens": "07:00", "closes": "23:00"},
          {"@type": "OpeningHoursSpecification", "dayOfWeek": "Thursday", "opens": "07:00", "closes": "00:00"},
          {"@type": "OpeningHoursSpecification", "dayOfWeek": "Friday", "opens": "07:00", "closes": "02:00"},
          {"@type": "OpeningHoursSpecification", "dayOfWeek": "Saturday", "opens": "08:00", "closes": "02:00"},
          {"@type": "OpeningHoursSpecification", "dayOfWeek": "Sunday", "opens": "08:00", "closes": "00:00"}]}
EXTRA_CSS = ('/* hv: botón "Descubre nuestro menú" más grande y fácil de tocar en celular */\n'
             '.index-bar{height:70px}\n'
             '.index-bar .ib-cta{height:50px;padding:0 18px 0 22px;font-size:13px;letter-spacing:.1em;gap:10px;box-shadow:0 6px 18px rgba(33,29,26,.22)}\n'
             '.index-bar .ib-cta .chev{width:15px;height:15px}\n'
             '.index-bar b{font-size:16px}\n')


def drop_media_blocks(s, query):
    """Quita bloques @media (query){...} con llaves anidadas."""
    out, i = [], 0
    pat = re.compile(r'@media\s*\(\s*' + query + r'\s*\)\s*\{')
    while True:
        m = pat.search(s, i)
        if not m:
            out.append(s[i:]); break
        out.append(s[i:m.start()])
        depth, j = 1, m.end()
        while j < len(s) and depth:
            depth += {'{': 1, '}': -1}.get(s[j], 0); j += 1
        i = j
    return ''.join(out)


def patch(s):
    log = []
    # 1. SEO
    if 'hv-seo' not in s:
        s = re.sub(r'<title>[^<]*</title>\s*', '', s)
        t = TITLE.replace('&', '&amp;')
        head = ('<meta name="hv-seo" content="1"><title>' + t + '</title>'
                '<meta name="description" content="' + DESC + '"><link rel="canonical" href="' + URL + '">'
                '<meta property="og:type" content="restaurant.menu"><meta property="og:site_name" content="360 Rooftop · Haven Medellín">'
                '<meta property="og:title" content="' + t + '"><meta property="og:description" content="' + DESC + '">'
                '<meta property="og:url" content="' + URL + '"><meta property="og:image" content="' + URL + 'img/hero_poster.webp">'
                '<meta name="twitter:card" content="summary_large_image">'
                '<script type="application/ld+json">' + json.dumps(LD, ensure_ascii=False) + '</script>')
        s, n = re.subn(r'<html[^>]*><head><meta charset=utf8>', '<html lang="es"><head><meta charset=utf8>' + head, s, count=1)
        if not n:
            s = s.replace('<head>', '<head>' + head, 1)
        log.append('seo')
    # 2. efectos siempre
    n0 = s.count('prefers-reduced-motion')
    s = drop_media_blocks(s, r'prefers-reduced-motion\s*:\s*reduce')
    s = re.sub(r"const reduce\s*=\s*matchMedia\('\(prefers-reduced-motion: ?reduce\)'\)\.matches;",
               "const reduce=false; /* los efectos se muestran siempre (pedido del gerente) */", s)
    if n0: log.append('reduce-motion x%d' % n0)
    # 3. Google Maps
    s, n1 = re.subn(r'<a href="#casa" data-t="nav_ubi">', '<a href="' + MAPS + '" target="_blank" rel="noopener" data-t="nav_ubi">', s)
    s, n2 = re.subn(r'https://www\.google\.com/maps/search/\?api=1&amp;query=Haven[^"]*', MAPS, s)
    if n1 or n2: log.append('maps %d+%d' % (n1, n2))
    # 4. Horarios
    if 'data-t="nav_hor"' not in s:
        s = s.replace('<a href="#reservas" data-t="nav_res">Reservar</a>\n',
                      '<a href="#reservas" data-t="nav_res">Reservar</a>\n      <a href="#horarios" data-t="nav_hor">Horarios</a>\n', 1)
        s = s.replace('nav_res:"Reservar",', 'nav_res:"Reservar",nav_hor:"Horarios",', 1).replace('nav_res:"Book",', 'nav_res:"Book",nav_hor:"Hours",', 1)
        log.append('nav horarios')
    if 'id="horarios"' not in s:
        s = s.replace('<div><h4 class="lbl" data-t="hor_t">', '<div id="horarios" style="scroll-margin-top:96px"><h4 class="lbl" data-t="hor_t">', 1)
        log.append('ancla horarios')
    # 5. botón grande
    if 'hv: botón "Descubre' not in s:
        k = s.find('.index-bar')
        e = s.find('</style>', k)
        if k >= 0 and e >= 0:
            s = s[:e] + EXTRA_CSS + s[e:]
            log.append('boton grande')
    return s, log


if __name__ == '__main__':
    p = sys.argv[1]
    s = open(p, encoding='utf-8').read()
    s, log = patch(s)
    open(p, 'w', encoding='utf-8').write(s)
    print('ajustes:', ', '.join(log) or 'ninguno (ya estaba)')
