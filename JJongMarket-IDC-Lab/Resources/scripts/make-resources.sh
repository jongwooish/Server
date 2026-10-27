#!/bin/bash
# Build the deploy zips in resources/ (the files kept in the Notion "Resources" tab)
# run inside Resources/:  scripts/make-resources.sh
set -e
OUT=.; T=$(mktemp -d)
rm -f $OUT/*.zip

# WEB: shop files + Apache conf + DB seed
mkdir -p $T/web/configs/web $T/web/configs/mariadb
cp -r web $T/web/web
cp configs/web/* $T/web/configs/web/
cp configs/mariadb/* $T/web/configs/mariadb/
(cd $T/web && zip -qr - .) > $OUT/jjong-idc-lab_WEB.zip

# Streaming: video + service
mkdir -p $T/live/live $T/live/configs/streaming
cp live/jjonglive.mp4 $T/live/live/
cp configs/streaming/jjonglive.service $T/live/configs/streaming/
(cd $T/live && zip -qr - .) > $OUT/jjong-idc-lab_Streaming.zip

# Monitoring: blackbox, prometheus, rules, grafana provisioning + dashboard
mkdir -p $T/mon/configs
cp -r configs/monitoring $T/mon/configs/
(cd $T/mon && zip -qr - .) > $OUT/jjong-idc-lab_Monitoring.zip

# Ansible
scripts/make-ansible-zip.sh $OUT/jjong-idc-lab_Ansible.zip >/dev/null

rm -rf $T
ls -l $OUT
