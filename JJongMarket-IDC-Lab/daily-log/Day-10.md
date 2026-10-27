# Day 10 - 2026.10.27 (화) 정리, GitHub

## 오늘 한 일

- 노션에 정리한 README, Topology, Session, Verification, Resources를 GitHub 저장소로 옮김
- 노션 탭 구조 그대로 Session(Configuration)과 Resources(zip, 설정 파일)로 나누고, 실제 구축한 값과 일치하는지 다시 맞춤
- 막혔던 화면들을 모아 Troubleshooting 정리
- 비밀번호는 전부 `<ROOT_PASSWORD>` 같은 자리 표시로 바꿈

## 돌아보며

| 처음 계획 | 실제 |
| --- | --- |
| OpenVPN | WireGuard (설정이 단순) |
| nginx 뒤 Grafana(https) | Grafana 3000 http (개선 과제로 남김) |
| apache·mysqld exporter | blackbox로 서비스 응답을 직접 확인 |
| 상품 8개짜리 상품 목록 | 상품 160개 쇼핑몰 + 라이브 커머스 |

## 다음에 할 것

- SSH 접속을 CA 인증서 키로만 하도록 정리 (ssh-copy-id로 넣은 키 제거)
- FW, DNS, DB 설정도 Ansible로 옮기기
- Grafana https, DB 복제, FW 이중화
