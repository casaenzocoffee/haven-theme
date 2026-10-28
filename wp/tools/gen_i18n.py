"""Genera haven/inc/i18n-en.php a partir de tr_all.json (textos de interfaz ES -> EN)."""
import json, os
B = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
d = json.load(open(os.path.join(B, 'tr_all.json')))
q = lambda s: '"' + s.replace('\\', '\\\\').replace('"', '\\"').replace('$', '\\$') + '"'
out = ['<?php', '// Textos de interfaz ES -> EN (el contenido editable vive en cada página). Generado por tools/gen_i18n.py desde tr_all.json.', 'return array(']
out += ['  %s => %s,' % (q(k), q(v)) for k, v in d.items()]
out += [');', '']
open(os.path.join(B, 'haven/inc/i18n-en.php'), 'w').write('\n'.join(out))
print(len(d), 'textos')
