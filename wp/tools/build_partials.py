#!/usr/bin/env python3
"""Convierte la portada y el pie del prototipo en plantillas del tema:
 - enlaces /es/… y /en/… -> {{url:clave}}   - medios del tema -> {{asset:ruta}}
 - en la portada, cada texto -> {{t:N}} y cada imagen -> {{img:N}} (editables desde el panel)."""
import re, json, sys, html as H
sys.path.insert(0, '/home/claude/haven-site')
import build
SRC = '/home/claude/haven-site/src/'
OUT = '/home/claude/haven-wp/haven/partials/'
# ruta real -> clave
P2K = {}
for real, proto in build.NAV_MAP.items():
    p = proto.strip('/')
    key = 'home' if not p else (('room-' + p.split('/', 1)[1]) if p.startswith('habitacion/') else (('post-' + p.split('/', 1)[1]) if p.startswith('blog/') else p))
    key = {'suites-de-lujo': 'suites-de-lujo'}.get(key, key)
    P2K[real] = key
    en = build.ES2EN_PATH.get(proto)
    if en is not None:
        P2K['/' + en] = key
P2K['/es/suite-presidencial/'] = 'room-suite-presidential'
def links(s):
    def rep(m):
        u = m.group(1)
        k = P2K.get(u)
        if not k:
            print('  ! enlace sin clave', u); return m.group(0)
        return 'href="{{url:' + k + '}}"'
    s = re.sub(r'href="(/e[sn]/[^"#]*)"', rep, s)
    s = re.sub(r'\{\{R\}\}(media/[\w.-]+)', r'{{asset:\1}}', s)
    s = s.replace('<!--HG:mapa-->', '{{sc:haven_mapa}}')
    return s
def textify(s, store):
    """Reemplaza textos visibles e imágenes por marcadores numerados."""
    out = []
    parts = re.split(r'(<script\b.*?</script>|<[^>]+>|\{\{[^}]+\}\})', s, flags=re.S)
    for p in parts:
        if not p: continue
        if p.startswith('<script') or p.startswith('{{'):
            out.append(p); continue
        if p.startswith('<'):
            def at(m):
                v = H.unescape(m.group(2))
                if not v.strip() or v.startswith('{{'): return m.group(0)
                store['t'].append(v); return f'{m.group(1)}="{{{{ta:{len(store["t"])-1}}}}}"'
            p = re.sub(r'\b(alt|aria-label|title|placeholder)="([^"]*)"', at, p)
            if p.startswith('<img') or p.startswith('<source'):
                def im(m):
                    store['img'].append(m.group(2)); return f'{m.group(1)}="{{{{img:{len(store["img"])-1}}}}}"'
                p = re.sub(r'\b(src|srcset)="(/wp-content/[^"]+)"', im, p)
            out.append(p); continue
        txt = p.strip()
        if not txt or not re.search(r'[A-Za-zÁÉÍÓÚáéíóúÑñ0-9★]', txt) or txt in ('/',):
            out.append(p); continue
        lead = p[:len(p) - len(p.lstrip())]; trail = p[len(p.rstrip()):]
        store['t'].append(H.unescape(txt))
        out.append(f'{lead}{{{{t:{len(store["t"])-1}}}}}{trail}')
    return ''.join(out)
res = {}
for lang, fn in [('es', 'pages/index.html'), ('en', 'pages/index_en.html')]:
    s = open(SRC + fn).read()
    intro, body = s.split('<!--BODY-->', 1)
    body = links(body)
    store = {'t': [], 'img': []}
    tpl = textify(body, store)
    res[lang] = {'tpl': tpl, 'defaults': store}
    open(OUT + f'intro.html', 'w').write(intro)
# la plantilla de la portada es la misma en ambos idiomas (solo cambian los textos)
a, b = res['es']['tpl'], res['en']['tpl']
if a != b:
    import difflib
    d = [l for l in difflib.unified_diff(a.splitlines(), b.splitlines(), lineterm='')][:20]
    print('  ! la estructura ES/EN difiere:'); print('\n'.join(d))
open(OUT + 'home.html', 'w').write(a)
json.dump({'es': res['es']['defaults'], 'en': res['en']['defaults']}, open(OUT + 'home-defaults.json', 'w'), ensure_ascii=False, indent=0)
for lang, fn in [('es', 'partials/footer.html'), ('en', 'partials/footer_en.html')]:
    open(OUT + f'footer-{lang}.html', 'w').write(links(open(SRC + fn).read()))
print('textos', len(res['es']['defaults']['t']), len(res['en']['defaults']['t']), 'imágenes', len(res['es']['defaults']['img']))
