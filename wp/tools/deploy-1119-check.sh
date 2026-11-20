#!/bin/bash
# 19 nov 2026 — revisión previa (no cambia nada en los sitios): trae el repo y compara.
R=~/repositories/haven-theme
echo "== git pull"; git -C $R pull 2>&1 | tail -3; git -C $R log --oneline -1
LIVE_ROOT=$(cd ~/havenmedellin.com && wp eval 'echo get_theme_root();' 2>/dev/null)
LIVE=$LIVE_ROOT/haven
STG=~/staging.havenmedellin.com/wp-content/themes/haven
echo "LIVE=$LIVE"; echo "STG=$STG"
for T in "$STG" "$LIVE"; do
  echo "== diff repo vs $T"; diff -rq $R/wp/haven "$T" 2>&1 | head -20
done
echo "== cartas"
for d in ~/havenmedellin.com/360-rooftop-carta ~/staging.havenmedellin.com/360-rooftop-carta; do
  ls -la $d/index.html 2>&1
  [ -f $d/index.html ] && { echo "diff vs repo HEAD~3 (antes de hoy):"; git -C $R show 4848624:carta360/index.html | diff -q - $d/index.html && echo IGUAL_A_REPO_ANTERIOR; }
done
echo "== fin"
