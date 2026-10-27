# 12. Backup

### [ DB ]

```bash
apt install -y rsync cron
vim /usr/local/sbin/mariadb-backup.sh
```

```
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
```

```bash
chmod 700 /usr/local/sbin/mariadb-backup.sh
vim /etc/cron.d/mariadb-backup
```

```
0 3 * * * root /usr/local/sbin/mariadb-backup.sh
```

### [ MGMT ]

```bash
apt install -y rsync cron
mkdir -p /srv/backup/db
chmod 700 /srv/backup/db
ssh 10.10.20.10 exit          # known_hosts 등록
vim /etc/cron.d/pull-db-backup
```

```
30 3 * * * root rsync -a --exclude '*.tmp' 10.10.20.10:/var/backups/mariadb/ /srv/backup/db/
40 3 * * * root find /srv/backup/db -name 'shop_*.sql.gz' -mtime +30 -delete
```

> `/etc/cron.d` 파일 이름에는 점(`.`)을 넣지 않습니다. 점이 있으면 cron이 그 파일을 무시합니다.
