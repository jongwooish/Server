#!/bin/bash
# /usr/local/sbin/mariadb-backup.sh  (DB)  chmod 700
# restore: gunzip -c /var/backups/mariadb/shop_<date>.sql.gz | mariadb
set -o pipefail
umask 077

DIR=/var/backups/mariadb
FILE=$DIR/shop_$(date +%F_%H%M).sql.gz
mkdir -p $DIR

if mariadb-dump --single-transaction --routines --triggers --databases shop | gzip > $FILE.tmp; then
    mv $FILE.tmp $FILE
    find $DIR -name 'shop_*.sql.gz' -mtime +7 -delete
    logger -t mariadb-backup "OK $FILE"
else
    rm -f $FILE.tmp
    logger -t mariadb-backup "FAILED"
    exit 1
fi
