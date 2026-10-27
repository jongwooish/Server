# Day 09 - 2026.10.26 (월) 쫑라이브, 모니터링, Ansible

## 오늘 한 일

- **쫑라이브**: 쇼호스트 "삐요"가 특가 상품을 소개하는 1분짜리 영상을 만들고, WEB1·WEB2에서 ffmpeg로 HLS 무한 반복 방송
- **모니터링**: node-exporter 6대, blackbox로 쇼핑몰·쫑라이브·DB를 손님처럼 확인, Prometheus 알림 규칙 6개, Grafana 대시보드
- **Ansible**: 공통 설정과 웹 배포 플레이북, 두 번째 실행 `changed=0`
- **Verification**: 챕터별 점검과 장애 테스트 정리

![jjonglive](../docs/images/jjonglive.gif)

## 배운 것

- 방송 서버를 따로 만들지 않고 웹 서버 두 대가 각자 방송하게 했다. `-c copy` 라 다시 인코딩을 안 해서 CPU를 거의 안 쓴다. WEB1이 죽어도 WEB2 방송으로 이어진다.
- blackbox는 "서버가 켜져 있나"가 아니라 "서비스가 응답하나"를 본다. Apache만 죽은 상황은 node-exporter로는 못 잡는다.
- Ansible이 처음엔 MGMT 빼고 전부 UNREACHABLE. 손으로 ssh 할 때는 내가 yes와 비밀번호를 쳤는데, Ansible은 그걸 못 한다. keyscan과 키 배포로 해결.
- 그다음엔 WEB1에서 인증서 경로가 달라서 멈췄다. 설정을 덮어쓰기 전에 파일부터 확인하게 만들어 둔 덕에 쇼핑몰은 안 죽었다.

![ansible](../docs/images/troubleshooting/07-ansible-cert-path.png)

## 느낀 점

처음 손으로 할 때 실수했던 것(700 권한, mbstring)을 Ansible이 매번 검사하고 고쳐 준다. 왜 자동화를 하는지 몸으로 이해한 날.
