# Day 05 - 2026.10.20 (화) CA, DNS, VPN 교체

## 오늘 한 일

- MGMT에 사설 Root CA (`CA.pl -newca`), 모든 장비에 RootCA.crt 신뢰 등록
- BIND9 view 두 개(internal / external)로 split-horizon DNS
- VPN을 OpenVPN에서 **WireGuard**로 변경
- 노션 Session 탭에 챕터별 토글로 Configuration 정리 시작

## 왜 WireGuard로 바꿨나

OpenVPN은 서버·클라이언트 인증서, tls-crypt 키, 설정 파일이 많았다. WireGuard는 양쪽이 서로의 공개키만 알면 되고 설정이 10줄 남짓이다. 지금 프로젝트의 목적은 "관리망에 안전하게 들어오는 경로"라서 더 단순한 쪽을 골랐다.

## 배운 것

- 외부 질의는 FW가 DNS로 DNAT 해 주는데, 출발지 주소는 안 바뀐다. 그래서 DNS가 그 질의를 바깥 것으로 알아보고 external view로 답한다. 처음엔 DNAT 하면 다 내부에서 온 걸로 보일 줄 알았다.
- `recursion no` 라서 외부에서 `jjong.internal` 을 물으면 REFUSED가 나온다. 내부 이름이 밖으로 새지 않는다.
- zone 파일 틀은 bind9-doc의 ARM 문서에서 gpm으로 복사했다. 대회 때 쓰던 방식 그대로.

## 느낀 점

과제를 푸는 것과 다르게, 실무처럼 구성하려니 오류가 훨씬 많다. 그래도 "왜"를 계속 묻게 돼서 공부가 더 된다.
