#!/bin/sh
# Copia el tema WordPress (y sus herramientas) al repo de despliegue. Luego se publica con ../haven-site/sync.sh "mensaje".
set -e
R=/home/claude/haven-theme/wp
rm -rf $R && mkdir -p $R
cp -r /home/claude/haven-wp/haven $R/haven
cp -r /home/claude/haven-wp/tools /home/claude/haven-wp/export_bundle.py /home/claude/haven-wp/rewards.py /home/claude/haven-wp/tr_all.json /home/claude/haven-wp/sync-wp.sh $R/
find $R -name __pycache__ -prune -exec rm -rf {} +
echo "tema copiado a $R"
