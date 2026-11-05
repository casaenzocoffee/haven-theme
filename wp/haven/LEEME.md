# Tema "Haven, A Design Hotel"

Es el diseño del prototipo hecho tema de WordPress. Usa las mismas URLs, Polylang para ES/EN, Yoast para el SEO y GTM4WP para la analítica.

## Dónde se edita cada cosa
- **Cualquier página con diseño Haven:** abre Páginas → la página → el panel **"Contenido Haven"**. Ahí cambias títulos, párrafos, fotos (con el botón *Elegir imagen*), botones y bloques (*+ Agregar*, ↑ ↓, ✕). Los cambios se ven al pulsar **Actualizar**.
- **Portada:** abre la página de inicio de cada idioma → "Contenido Haven". Tiene sus textos e imágenes, agrupados por sección. Si dejas un campo igual al original, se usa el texto original.
- **Habitaciones:** cada habitación es una página. El orden en el listado se cambia en su panel, en *Orden en el listado*.
- **Blog:** las entradas nuevas se escriben normal, con el editor de WordPress. El tema les pone el diseño solo.
- **SEO:** títulos y descripciones en Yoast, dentro de cada página.
- **Textos fijos de la interfaz en inglés:** `tr_all.json` en el repositorio, y luego `python3 tools/gen_i18n.py`.

## Cómo funciona
- Cada página lleva una clave (`_hv_key`): `home`, `habitaciones`, `room-habitacion-duke`, `rooftop`… La clave elige la plantilla en `templates/`. La plantilla de WordPress y los datos de Elementor no se cambian, así que desactivar el tema devuelve todo a como estaba.
- El contenido estructurado está en la meta `_hv_data` (JSON). Antes de cada cambio se guarda una copia en `_hv_data_bak`.
- Las páginas sin clave usan `templates/page.php`. Si están hechas con Elementor, las dibuja Elementor.
- La guía de Medellín (mapa, plan y quiz) vive en `guia/` y se puede insertar en cualquier página con `[haven_guia bloque="guia|mapa|quiz"]`.

## Importar o actualizar el contenido inicial
Herramientas → **Haven (contenido)**. Primero haz *Solo prueba* y después ejecútalo de verdad. Por WP-CLI: `wp haven import [--dry-run] [--create] [--force] [--seo]`.

## Bloques extra ("more") y planes (5 nov 2026)
- Cualquier página Haven (Swing, Wellness, Gastronomía, Servicios, el quiz) puede llevar en `_hv_data` la clave `more`: una lista de bloques como los de las landings (`text` con `sub`/`h3`, `rooms`, `faq`, `links`, `cta`). Se dibujan al final con `hv_render_blocks()` (`inc/seo-fix.php`) y los `faq` salen también como FAQPage.
- Los textos de esos bloques están en `wp/tools/seo-content/more.json` (se generan con `more_build.py`); `seo-content.php aplicar` los vuelve a poner.
- Portada: `{{sc:haven_planes}}` muestra "Planes en Haven". Pie: bloque `.foot-plans` con 10 enlaces a las páginas por intención y la guía.
- hreflang `x-default` en todas las páginas (filtro `pll_rel_hreflang_attributes`).
