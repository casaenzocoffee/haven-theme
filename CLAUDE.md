# Haven Medellín (havenmedellin.com) — guía para Claude Code

Sitio WordPress del hotel Haven, A Design Hotel (El Poblado, Medellín). El gerente escribe en español y espera respuestas en español claro, sin jerga técnica.

## Reglas del gerente (siempre)
- **Proteger el sitio en vivo.** Cuidado, sin errores y sin rehacer lo que ya funciona.
- **Copia de seguridad antes de cada cambio** en el servidor (`~/hv-backups/…`) y **probar primero en staging**.
- **Mostrar los cambios de diseño antes de publicarlos** (captura en computador y celular) y esperar su aprobación.
- **Nunca escribir contraseñas.** El gerente inicia sesión él mismo. Pedir permiso antes de descargas, datos sensibles o autorizaciones.
- **No inventar datos ni precios.** Lo que no se pueda confirmar se marca como "pendiente de verificar".
- **No perjudicar a Casa Enzo** (cafetería aliada que aparece en el sitio).
- **Esta guía es solo para havenmedellin.com.** Viktory (viktoryhotel.com) se trabaja en otra sesión.
- **Sitio en vivo = staging = GitHub**, siempre iguales.
- Después de publicar, **verificar las páginas una por una** (no 34 en paralelo: el 12 nov eso, sumado a la recarga de caché, tumbó la base de datos unos minutos).

## Qué hay en este repositorio
- `wp/haven/` — **el tema de WordPress que está en vivo** (PHP, CSS, JS). Ver `wp/haven/LEEME.md` para la estructura (claves `_hv_key`, plantillas, panel "Contenido Haven", `_hv_data`).
  - `inc/` lógica: `router.php` (clave → plantilla), `partials.php` (relleno de `{{t:N}}`, `{{img:N}}`, `{{room:…}}`), `components.php` (tarjetas), `disponibilidad.php` (calendario en vivo de Roiback), `setup.php` (scripts y estilos), `seo*.php`.
  - `templates/` una plantilla por tipo de página; `partials/home.html` es la portada.
  - `assets/` `haven.css`, `haven.js`, `hv-cal.js` (calendario), `clock3d.js` (reloj de la portada), `media/` (fotos del tema).
  - `footer.php` incluye el banner y el pop-up de temporada (Black Week hasta el 3 dic 2026, se oculta solo).
- `wp/tools/` — herramientas de mantenimiento (scripts `wp eval-file`, contenido SEO, etiqueta de Tag Manager, `warm.sh` para recalentar la caché).
- `wp/server/` — copias de configuraciones que viven solo en el servidor (por ejemplo el bloque "Haven raíz" del `.htaccess`), para referencia.
- `src/`, `build.py`, `dist/`, `carta360/`, `carta-swing/` — prototipo estático original y cartas digitales (no es el sitio en vivo).
- Otra sesión trabaja el SEO en este mismo repo: **no sobrescribir sus archivos**. Copiar o editar solo los archivos que se cambian; no usar `wp/sync-wp.sh` a ciegas.

## Servidor (FastComet, cPanel)
- Sitio en vivo: `~/havenmedellin.com` · staging: `~/staging.havenmedellin.com` (responde `noindex`).
- Tema en vivo: `$(wp eval 'echo get_theme_root();')/haven`; staging: `~/staging.havenmedellin.com/wp-content/themes/haven`.
- Copia del repo en el servidor: `~/repositories/haven-theme` (cPanel Git).
- Caché: WP Rocket. Después de publicar: `wp eval 'rocket_clean_domain();'` y luego `sh ~/repositories/haven-theme/wp/tools/warm.sh` en segundo plano.
- Cron real de cPanel cada 5 min (`wp cron event run --due-now`) mueve el calendario en vivo y la precarga.

### Cómo se publica un cambio del tema (procedimiento probado)
1. Editar en `wp/haven/…`, probar en local, commit y push a `main`.
2. En el servidor: `git -C ~/repositories/haven-theme pull`.
3. Para staging y para el sitio en vivo, en ese orden:
   - copia: `tar czf ~/hv-backups/tema-<sitio>-antes-<cambio>-$(date +%Y%m%d-%H%M).tgz -C <themes> haven`
   - copiar **solo los archivos cambiados** con `cp archivo archivo.new && mv archivo.new archivo` (cambio atómico)
   - comprobar: `diff -rq ~/repositories/haven-theme/wp/haven <themes>/haven` (debe quedar igual)
4. `php -l` de los PHP tocados, limpiar caché, recalentar y verificar en vivo (ES y EN, computador y celular).

## Integraciones (configuradas fuera del tema)
- **Motor de reservas Roiback:** `havenmedellin.backhotelite.com/{es|en}/bookcore/availability/havenmedellin/LLEGADA/SALIDA/ADULTOS/0/`. El calendario agrega `?hv_room=<nombre en Roiback>`.
- **Google Tag Manager GTM-TVFGX8W:** la etiqueta "Haven - Habitación elegida primero (calendario web)" (código en `wp/tools/gtm-habitacion-primero.html`) pone de primera en el motor la habitación elegida. El espacio de trabajo por defecto tiene cambios de Roiback sin publicar: **no tocarlos**. Publicar desde un espacio aparte (Admin → Importar contenedor → espacio nuevo → **Combinar**, nunca "Sobrescribir").
- **Calendario en vivo:** `inc/disponibilidad.php` consulta Roiback cada 5 min y escribe `wp-content/uploads/hv-dispo/*.json` (120 días, precio más bajo no reembolsable con IVA).
- Polylang (ES/EN), Yoast SEO, WP Rocket, GTM4WP.

## Notas completas
El historial detallado de decisiones y cambios (con fechas) está en el proyecto "PAGINA HAVEN WP" de claude.ai (documentos `claude/haven-*.md`). Esos documentos tienen datos internos y no se copian a este repositorio, que es público.
