<?php
require __DIR__ . '/inc/app.php';

$faq = [
    '배송' => [
        ['쫑배송은 언제 도착하나요?', '밤 12시 전에 주문하면 다음 날 도착합니다. 일요일과 공휴일에도 배송해요.'],
        ['배송비는 얼마인가요?', '쫑배송과 무료배송 상품은 무료입니다. 일반배송 상품은 ' . won(FREE_SHIP_MIN) . ' 이상 주문 시 무료, 그 미만은 ' . won(SHIP_FEE) . '입니다. 쫑클럽 회원은 금액과 상관없이 무료예요.'],
        ['배송지를 바꾸고 싶어요.', '주문/결제 화면에서 배송 주소를 바꿀 수 있고, 마이쫑 > 내 정보에서 기본 배송지를 저장할 수 있습니다.'],
    ],
    '주문/결제' => [
        ['어떤 결제수단을 쓸 수 있나요?', '마이쫑 > 결제수단 관리에서 신용·체크카드를 등록해 사용할 수 있습니다. 카드번호는 끝 4자리만 보관합니다.'],
        ['주문 내역은 어디서 보나요?', '마이쫑 > 주문목록에서 주문일, 상품, 결제 카드, 금액을 확인할 수 있습니다.'],
        ['결제 도중 재고가 없다고 나와요.', '다른 고객이 먼저 구매해 재고가 부족해진 경우입니다. 이때 주문은 전부 취소되고 결제도 진행되지 않습니다.'],
    ],
    '쫑클럽' => [
        ['쫑클럽 혜택이 뭔가요?', '전 상품 무료배송, 결제 금액 추가 ' . MEMBER_DC . '% 할인, 30일 무료 반품을 제공합니다.'],
        ['해지는 어떻게 하나요?', '쫑클럽 페이지에서 언제든 해지할 수 있습니다.'],
    ],
    '판매' => [
        ['누구나 상품을 팔 수 있나요?', '회원이면 누구나 상품 등록 메뉴에서 바로 판매를 시작할 수 있습니다.'],
        ['등록한 상품은 어디서 확인하나요?', '마이쫑 > 내 정보 하단의 "내가 등록한 상품"에서 판매가와 재고를 볼 수 있습니다.'],
    ],
];

$title = '고객센터';
include __DIR__ . '/inc/header.php';
?>
<section class="cs-hero">
  <h1>무엇을 도와드릴까요?</h1>
  <form action="/products.php" class="cs-search"><input name="q" placeholder="상품명으로 바로 찾기"><button class="btn">검색</button></form>
  <div class="cs-quick">
    <a href="/orders.php">📦<span>주문 조회</span></a>
    <a href="/cart.php">🛒<span>장바구니</span></a>
    <a href="/cards.php">💳<span>결제수단</span></a>
    <a href="/membership.php">🏅<span>쫑클럽</span></a>
    <a href="/sell.php">📤<span>판매하기</span></a>
  </div>
</section>

<section class="cs-body">
  <div class="faq">
    <h2>자주 묻는 질문</h2>
    <?php foreach ($faq as $group => $list): ?>
      <h3><?= h($group) ?></h3>
      <?php foreach ($list as [$qq, $a]): ?>
        <details><summary><?= h($qq) ?></summary><p><?= h($a) ?></p></details>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
  <aside class="contact">
    <div class="box">
      <h3>📞 전화 상담</h3>
      <p class="tel">1588-0000</p>
      <p class="muted">평일 09:00 ~ 18:00 (점심 12:00 ~ 13:00)</p>
    </div>
    <div class="box">
      <h3>✉️ 이메일 문의</h3>
      <p>help@jjongmarket.kr</p>
      <p class="muted">영업일 기준 24시간 안에 답변</p>
    </div>
    <div class="box" id="terms">
      <h3>이용약관 · 개인정보</h3>
      <p class="muted" id="privacy">실습용 쇼핑몰입니다. 입력한 정보는 실습 DB에만 저장되며, 카드번호 전체와 CVC는 저장하지 않습니다.</p>
    </div>
  </aside>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
