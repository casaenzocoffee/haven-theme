#!/bin/sh
# Recalienta la caché de WP Rocket después de vaciarla: visita cada URL de los sitemaps con agente de celular y de escritorio.
# Uso (en el servidor): sh ~/repositories/haven-theme/wp/tools/warm.sh
M='Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
D='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36'
U=$(for s in page-sitemap.xml post-sitemap.xml; do curl -s "https://havenmedellin.com/$s" | grep -o '<loc>[^<]*' | sed 's/<loc>//'; done | sort -u)
n=0; for u in $U; do curl -s -o /dev/null -A "$M" "$u"; curl -s -o /dev/null -A "$D" "$u"; n=$((n+1)); done
echo "recalentadas $n URL (celular y escritorio)"
