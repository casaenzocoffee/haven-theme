"""Tamaños que WordPress ya generó para cada imagen (verificado en el servidor).
Se usan para servir srcset: el navegador baja la versión justa en vez del original de 500-650 KB."""
import re
DEFAULT = ['768x512']
EXC = {
 '2026/06/hotel-haven-rooftop-menu.webp': ['768x426'], '2026/06/preguntas-frecuentes.webp': ['768x430'], '2026/06/servicios-hotel-haven.webp': ['768x430'],
 '2026/06/swing-bar-1.webp': ['768x531'], '2026/06/swing-bar-2.webp': ['768x531'], '2026/06/swing-bar-6.webp': ['768x531'], '2026/06/vida-nocturna-5.webp': ['768x514'],
 '2026/07/10-mejores-cosas-para-hacer-y-ver-en-medellin.webp': ['768x512', '1024x683', '1536x1024'], '2026/07/experiencia-gastronomica-medellin.webp': ['768x512', '1024x683', '1536x1024'],
 '2026/07/la-cultura-paisa.webp': ['768x512', '1024x683', '1536x1024'], '2026/07/hidratacion-facial.webp': ['768x960'],
 '2026/07/la-culpable-pizza.webp': ['683x1024', '768x1152'], '2026/07/masaje-relajante.webp': ['683x1024', '768x1152'], '2026/07/suero-terapia-.webp': ['683x1024', '768x1152'],
 '2026/07/mini-albondigas-endiabladas.webp': ['768x513'], '2026/07/piscina-rooftop.webp': ['768x511', '1024x682', '1536x1023'], '2026/07/swing-bar.webp': ['768x511', '1024x682', '1536x1023'],
 '2026/07/terapia-de-sueros.webp': ['768x955'],
}
for _n in ['Moravia1', 'Moravia2', 'aromas-coffee-1', 'aromas-coffee-2', 'aromas-coffee-4', 'primavera2', 'primavera3', 'primavera4', 'silleteras-farms-2', 'silleteras-farms-3', 'silleteras-farms-4']:
    EXC['2026/07/' + _n + '.webp'] = ['768x707']
for _n in ['IMG_2359', 'emperor-penthouse-901-room-5', 'emperor-penthouse-901-room-6', 'emperor-penthouse-901-room-7', 'emperor-penthouse-901-room-8']:
    EXC['2026/09/' + _n + '.jpg'] = ['768x512', '1024x683', '1536x1024', '2048x1365']
NONE = {'2026/06/alquiler-de-coche.webp', '2026/06/hidratacion-intravenosa-vitamina.webp', '2026/06/menu-desayuno.webp', '2026/06/menu-rooftop-360.webp', '2026/06/menu-swing.webp',
        '2026/06/mini-bar.webp', '2026/06/spa-masajes.webp', '2026/06/tours-antioquia.webp', '2026/06/traslado-aeropuerto.webp', '2026/06/hotel-haven-medellin.webp',
        '2026/06/medellin-haven-a-design-hotel.webp', '2026/06/serena.webp', '2026/06/servicios-dentales.webp', '2026/06/logo-haven.webp'}

def variants(src):
    """src '/wp-content/uploads/2026/06/x.webp' (o una variante -WxH) -> (base, [(url, w)]) o None."""
    m = re.match(r'^/wp-content/uploads/(\d{4}/\d{2}/.+?)(-\d+x\d+)?(\.\w+)$', src)
    if not m or '-scaled' in src:
        return None
    key = m.group(1) + m.group(3)
    if key in NONE or not key.startswith(('2026/06', '2026/07', '2026/08', '2026/09')):
        return None
    vs = EXC.get(key, DEFAULT)
    base = '/wp-content/uploads/' + m.group(1)
    return base + m.group(3), [(f'{base}-{v}{m.group(3)}', int(v.split('x')[0])) for v in vs]
