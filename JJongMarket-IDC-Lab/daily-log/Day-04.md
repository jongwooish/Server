# Day 04 - 2026.10.19 (월) DHCP, nftables

## 오늘 한 일

- MGMT에 isc-dhcp-server, CLIENT가 10.10.30.100~150 받는 것 확인
- REMOTE는 다른 망이라 브로드캐스트가 MGMT까지 못 감 → FW에 dnsmasq로 DHCP 릴레이
- nftables 규칙 작성 (기본 drop, 필요한 것만 허용, 외부 80·443·53 DNAT)

## 배운 것

- 릴레이를 거치면 DHCP 서버는 패킷 안의 릴레이 주소(giaddr)를 보고 어느 서브넷에서 줄지 고른다. 그래서 dhcpd.conf에 203.0.113.0 서브넷이 있어야 한다.
- REMOTE 범위에는 `option routers` 를 일부러 안 넣었다. 게이트웨이가 없으니 VPN 없이는 내부로 갈 길 자체가 없다.
- nftables 작업 중에 내가 만든 규칙에 내가 막혀서 scp가 안 되는 일이 몇 번 있었다. 작업할 때만 `systemctl stop nftables` 하고 끝나면 다시 켜는 습관이 생겼다.
