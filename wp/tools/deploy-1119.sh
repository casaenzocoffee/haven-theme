#!/bin/bash
# 19 nov 2026 — publica: Fiweex -> reservas.havenmedellin.com, ReserveAction y título "Menú 360" de la carta.
# Orden: copia de seguridad -> staging -> comprobación -> vivo -> caché.
R=~/repositories/haven-theme
STAMP=$(date +%Y%m%d-%H%M)
FILES="assets/haven.js import/bundle-en.json import/bundle-es.json inc/helpers.php inc/seo-fix.php templates/gastronomy.php"
mkdir -p ~/hv-backups
pub() { # $1 = carpeta themes, $2 = nombre
  T=$1
  tar czf ~/hv-backups/tema-$2-antes-reservas360-$STAMP.tgz -C "$T" haven || { echo "FALLO backup $2"; exit 1; }
  for f in $FILES; do cp "$R/wp/haven/$f" "$T/haven/$f.new" && mv "$T/haven/$f.new" "$T/haven/$f"; done
  for f in inc/helpers.php inc/seo-fix.php templates/gastronomy.php; do php -l "$T/haven/$f" | grep -v '^No syntax' ; done
  echo "diff $2:"; diff -rq $R/wp/haven "$T/haven" && echo "  IGUAL"
}
echo "== STAGING"; pub ~/staging.havenmedellin.com/wp-content/themes staging
(cd ~/staging.havenmedellin.com && wp eval 'if(function_exists("rocket_clean_domain"))rocket_clean_domain();' 2>/dev/null)
S=$(curl -sL -k --max-time 40 -A "Mozilla/5.0 hv-check" "https://staging.havenmedellin.com/360-rooftop/?nc=$STAMP")
echo "staging 360: bytes=${#S} reservas=$(echo "$S" | grep -o 'reservas.havenmedellin.com' | wc -l) fiweex=$(echo "$S" | grep -o 'fiweex' | wc -l) ReserveAction=$(echo "$S" | grep -o 'ReserveAction' | wc -l)"
if [ ${#S} -lt 20000 ] || ! echo "$S" | grep -q 'reservas.havenmedellin.com' || echo "$S" | grep -q 'fiweex'; then echo "STAGING NO PASA — se detiene antes de vivo"; exit 1; fi
echo "== VIVO"; pub ~/havenmedellin.com/wp-content/themes vivo
C=~/havenmedellin.com/360-rooftop-carta
cp $C/index.html ~/hv-backups/carta360-antes-$STAMP.html
cp $R/carta360/index.html $C/index.html.new && mv $C/index.html.new $C/index.html
diff -q $R/carta360/index.html $C/index.html && echo "carta IGUAL"
(cd ~/havenmedellin.com && wp eval 'rocket_clean_domain(); echo "cache limpia\n";' 2>&1 | tail -1)
echo "== fin $STAMP"
