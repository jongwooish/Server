<?php $mytab = $mytab ?? ''; ?>
<aside class="mymenu">
  <h2>마이쫑</h2>
  <a href="/mypage.php"    class="<?= $mytab === 'home' ? 'on' : '' ?>">👤 내 정보</a>
  <a href="/orders.php"    class="<?= $mytab === 'orders' ? 'on' : '' ?>">📦 주문목록</a>
  <a href="/cart.php">🛒 장바구니</a>
  <a href="/cards.php"     class="<?= $mytab === 'cards' ? 'on' : '' ?>">💳 결제수단 관리</a>
  <a href="/membership.php" class="<?= $mytab === 'member' ? 'on' : '' ?>">🏅 쫑클럽</a>
  <a href="/sell.php"      class="<?= $mytab === 'sell' ? 'on' : '' ?>">📤 상품 등록</a>
</aside>
