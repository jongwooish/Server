<?php
require __DIR__ . '/inc/app.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    q('UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?', [
        mb_substr(trim($_POST['name'] ?? $u['name']), 0, 50) ?: $u['name'],
        mb_substr(trim($_POST['phone'] ?? ''), 0, 20),
        mb_substr(trim($_POST['address'] ?? ''), 0, 200),
        $u['id'],
    ]);
    flash('회원 정보를 저장했습니다.');
    redirect('/mypage.php');
}

$stat = q('SELECT COUNT(*) n, COALESCE(SUM(total),0) s FROM orders WHERE user_id = ?', [$u['id']])->fetch();
$ncard = (int)q('SELECT COUNT(*) n FROM cards WHERE user_id = ?', [$u['id']])->fetch()['n'];
$mine = q('SELECT p.*, i.updated_at AS img_v FROM products p LEFT JOIN product_images i ON i.product_id = p.id
            WHERE p.seller_id = ? ORDER BY p.id DESC', [$u['id']])->fetchAll();
$member = is_member($u);

$title = '마이쫑';
include __DIR__ . '/inc/header.php';
?>
<div class="mylayout">
  <?php $mytab = 'home'; include __DIR__ . '/inc/mymenu.php'; ?>
  <section>
    <div class="mysum">
      <div><span>회원 등급</span><b><?= $member ? '🏅 쫑클럽' : '일반' ?></b><?= $member ? '<small>~' . h($u['membership_until']) . '</small>' : '<a href="/membership.php">가입하기 ›</a>' ?></div>
      <div><span>주문</span><b><?= (int)$stat['n'] ?>건</b><small><?= won((int)$stat['s']) ?></small></div>
      <div><span>결제수단</span><b><?= $ncard ?>개</b><a href="/cards.php">관리 ›</a></div>
      <div><span>판매 상품</span><b><?= count($mine) ?>개</b><a href="/sell.php">등록 ›</a></div>
    </div>

    <div class="box">
      <h3>회원 정보</h3>
      <form method="post" class="form">
        <?= csrf_field() ?>
        <label class="field">이메일<input value="<?= h($u['email']) ?>" disabled></label>
        <label class="field">이름<input name="name" value="<?= h($u['name']) ?>" required></label>
        <label class="field">휴대폰<input name="phone" value="<?= h($u['phone']) ?>" placeholder="010-0000-0000"></label>
        <label class="field">기본 배송지<input name="address" value="<?= h($u['address']) ?>"></label>
        <button class="btn">저장</button>
      </form>
    </div>

    <div class="box">
      <h3>내가 등록한 상품</h3>
      <?php if (!$mine): ?>
        <p class="muted">아직 등록한 상품이 없습니다. <a href="/sell.php">첫 상품 등록하기 ›</a></p>
      <?php else: ?>
        <table class="spec">
          <tr><th>상품</th><th>판매가</th><th>재고</th><th>등록일</th><th></th></tr>
          <?php foreach ($mine as $p): ?>
            <tr>
              <td><a href="/item.php?id=<?= (int)$p['id'] ?>"><span class="withimg"><?= pimg((int)$p['id'], $p['name'], (string)($p['img_v'] ?? '')) ?><?= h($p['name']) ?></span></a></td>
              <td><?= won(sale_price($p)) ?></td>
              <td><?= (int)$p['stock'] ?></td>
              <td><?= h(substr((string)$p['created_at'], 0, 10)) ?></td>
              <td><a class="btn small ghost" href="/sell.php?edit=<?= (int)$p['id'] ?>">수정</a></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
