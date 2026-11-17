# Haven Medellín — tema de WordPress y herramientas

- **Para trabajar con Claude Code, leer primero `CLAUDE.md`.**
- `wp/haven/` es el tema que está en vivo en havenmedellin.com (ver `wp/haven/LEEME.md`).

## Prototipo original

- `src/` partials, páginas y assets (CSS/JS compartidos, reloj 3D).
- `build.py` arma `dist/` (HTML estático por página).
- `.cpanel.yml` despliega `dist/` a `/prototipo-haven/` (vista previa) y al staging.

No afecta el sitio en vivo. Las páginas llevan `noindex`.
