# Day 03 - 2026.10.16 (목) VM 생성, IP, 포워딩

## 오늘 한 일

- VMware에 Host-only VMnet0~3 만들고 DHCP 끔 (VMnet0은 기본이 Bridged라 바꿔야 했음)
- VM 8대 생성, Basic Configuration (패키지, root SSH 허용, 호스트네임)
- `/etc/network/interfaces` 로 고정 IP
- FW에 IP 포워딩

## 배운 것

- FW에 `net.ipv4.ip_forward=1` 을 넣어도 재부팅하면 포워딩이 꺼져 있는 경우가 있었다. `rc.local` 에서 부팅 때마다 `sysctl -p` 를 다시 돌리게 했다.
- 오후에는 회사에서 제조사별 서버 OS 설치, 마운트 교육을 받았다. 실물 서버에서 배운 RAID, iLO가 이 프로젝트의 "서버가 어디에 어떻게 꽂혀 있나"를 상상하는 데 도움이 됐다.
