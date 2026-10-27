# Day 06 - 2026.10.21 (수) WireGuard, SSH CA, chrony

## 오늘 한 일

- FW에 wg0 (10.8.0.1), REMOTE는 `nmcli connection import` 로 터널 연결
- MGMT에 SSH CA를 만들고 root 키에 서명
- DNS를 시간 기준(local stratum 8)으로, 나머지는 모두 DNS에 맞춤

## 배운 것

- WireGuard의 `AllowedIPs` 는 방향마다 의미가 다르다. FW 쪽은 "이 피어에게서 받을 주소", REMOTE 쪽은 "터널로 보낼 목적지". REMOTE에 `10.10.0.0/16` 만 넣어서 내부망 트래픽만 터널을 탄다.
- 내부 서버는 기본 게이트웨이가 FW라서, 10.8.0.2로 가는 응답이 따로 경로를 안 넣어도 FW → wg0로 돌아간다.
- SSH CA를 쓰면 서버마다 공개키를 넣을 필요 없이 CA 공개키 하나만 믿게 하면 된다. 다만 `/etc/ssl` 을 통째로 복사하면서 CA 개인키까지 퍼졌다는 건 나중에 점검하면서 알았다.
- 인터넷이 없으니 chrony 기본 `pool` 줄은 주석 처리해야 한다. 안 하면 계속 밖으로 나가려고 한다.
