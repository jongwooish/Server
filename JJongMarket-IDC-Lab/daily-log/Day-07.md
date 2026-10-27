# Day 07 - 2026.10.22 (목) MariaDB, 백업, keepalived

## 오늘 한 일

- DB에 MariaDB, `bind-address = 10.10.20.10`, utf8mb4
- 백업 스크립트 + cron, MGMT가 rsync로 가져가게
- WEB1·WEB2 keepalived로 VIP 10.10.10.100
- MGMT에서 웹 인증서(www.crt) 발급, WEB에 Apache 설치

## 배운 것

- 백업 스크립트에 `set -o pipefail` 이 없으면 `mariadb-dump` 가 실패해도 뒤의 gzip이 성공해서 빈 백업이 "성공"으로 남는다.
- 쓰는 중인 파일을 MGMT가 가져가지 않게 `.tmp` 로 쓰고 끝나면 `mv` 했다. rsync에도 `--exclude '*.tmp'`.
- `/etc/cron.d` 파일 이름에 점이 있으면 cron이 무시한다. `mariadb-backup.cron` 같은 이름은 안 된다.
- keepalived는 Apache 상태를 2초마다 확인하고, 실패하면 priority를 20 내린다. 110 - 20 = 90이 WEB2의 100보다 낮아져서 VIP가 넘어간다. 숫자로 설계하는 게 재밌었다.

## 내일 할 것

쇼핑몰 페이지 올리기
