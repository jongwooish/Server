# Resources

구축할 때 호스트 PC에서 각 VM으로 `scp` 하던 배포 묶음입니다. 노션 Resources 탭에 올려 두고 쓰던 파일과 같은 구성이고, 이 폴더에서 `scripts/make-resources.sh` 로 다시 만들 수 있습니다.

| 파일 | 내용 | 쓰는 챕터 |
| --- | --- | --- |
| `jjong-idc-lab_WEB.zip` | 쇼핑몰 PHP, Apache 사이트 설정, db.ini 예시, shop.sql | 11 MariaDB, 14 WEB |
| `jjong-idc-lab_Streaming.zip` | 쫑라이브 영상(3.6MB), jjonglive.service | 15 Streaming |
| `jjong-idc-lab_Monitoring.zip` | blackbox, prometheus, 알림 규칙, Grafana provisioning, 대시보드 JSON | 16 Monitoring |
| `jjong-idc-lab_Ansible.zip` | MGMT `/etc/ansible` 에 그대로 풀리는 플레이북 | 17 Ansible |

zip 안의 원본 파일도 폴더째 함께 두었습니다.

| 폴더 | 내용 |
| --- | --- |
| `configs/` | 챕터별 설정 파일 (network, dhcp, nftables, dns, wireguard, ssh, chrony, mariadb, backup, keepalived, web, streaming, monitoring) |
| `ansible/` | 플레이북 (`common.yml`, `web.yml`, `site.yml`) |
| `web/` | 쇼핑몰 PHP |
| `live/` | 쫑라이브 영상과 제작 소스 |
| `scripts/` | zip 생성 스크립트 |

저장소에 넣지 않은 외부 파일은 따로 받습니다.

| 파일 | 받는 곳 |
| --- | --- |
| `grafana-enterprise_8.3.4_amd64.deb` | [grafana.com 다운로드](https://grafana.com/grafana/download/8.3.4?edition=enterprise) |
| `hls.min.js` | [jsDelivr hls.js](https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js) |

노션은 파일 하나에 5MB 제한이 있어서 Grafana deb는 4MB씩 분할 압축해 올렸습니다.
