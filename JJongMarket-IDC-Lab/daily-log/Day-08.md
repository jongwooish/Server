# Day 08 - 2026.10.23 (금) 쇼핑몰, 그리고 오류의 날

## 오늘 한 일

- 오전: 간단한 HTML 쇼핑몰(v1)을 올렸는데 CLIENT에서 한글이 전부 네모
- 디자인이 너무 아쉬워서 쇼핑몰을 다시 만듦(v2): 노란색 테마, 상품 160개, 리뷰 800개, 검색, 장바구니, 주문, 가상 배송, 판매 등록, 카드 등록, 멤버십, 광고 팝업
- 밤: v2 올리고 500 에러, 상세 페이지 깨짐, 회원가입 오류를 차례로 해결

![v1](../docs/images/troubleshooting/01-hangul-broken.png)

## 막혔던 것

| 증상 | 원인 | 해결 |
| --- | --- | --- |
| 한글이 네모 | CLIENT에 한글 글꼴 없음 | `fonts-noto-cjk` |
| 500 에러 | Windows scp로 보낸 `inc` 폴더가 700 | `chmod -R a+rX` |
| 상세 깨짐, 회원가입 오류 | `php-mbstring` 없음 | 설치 + 대체 함수 |
| 주문서 입력칸 빈칸 | `header.php` 와 페이지가 `$f` 변수를 같이 씀 | `$flash_msg` 로 이름 변경 |

500 에러는 root로 `php index.php` 하면 멀쩡하고 `runuser -u www-data` 로 하면 Permission denied가 나와서 권한 문제라는 걸 알았다. 실행하는 사용자를 바꿔서 재현해 보는 방법을 처음 써 봤다.

![permission](../docs/images/troubleshooting/03-permission-denied.png)

자세한 건 [troubleshooting.md](../docs/troubleshooting.md)

## 느낀 점

오늘이 제일 오래 걸렸다. 그래도 하나씩 원인을 찾아서 고치고 나니까 쇼핑몰이 진짜 쇼핑몰처럼 돌아가는 게 뿌듯했다.
