<?php
require __DIR__ . '/inc/app.php';
$u = user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = require_login();
    csrf_check();
    if (($_POST['action'] ?? '') === 'join') {
        $card = q('SELECT * FROM cards WHERE user_id = ? ORDER BY is_default DESC LIMIT 1', [$u['id']])->fetch();
        if (!$card) { flash('쫑클럽 가입에는 결제 카드가 필요합니다.', 'warn'); redirect('/cards.php?next=/membership.php'); }
        $base  = is_member($u) ? $u['membership_until'] : date('Y-m-d');
        $until = date('Y-m-d', strtotime($base . ' +30 day'));
        q('UPDATE users SET membership_until = ? WHERE id = ?', [$until, $u['id']]);
        flash("쫑클럽에 가입했습니다. {$card['brand']} ****{$card['last4']}로 월 " . won(MEMBER_FEE) . " 결제 (실습용)");
    } else {
        q('UPDATE users SET membership_until = NULL WHERE id = ?', [$u['id']]);
        flash('쫑클럽을 해지했습니다.');
    }
    redirect('/membership.php');
}

$member = is_member($u);
$title  = '쫑클럽';
include __DIR__ . '/inc/header.php';
?>
<section class="club">
  <div class="club-hero">
    <small>JJONG CLUB</small>
    <h1>🏅 쫑클럽</h1>
    <p>월 <b><?= won(MEMBER_FEE) ?></b>으로 매일 쓰는 쇼핑을 더 가볍게</p>
    <?php if ($member): ?>
      <div class="club-status">이용 중 · <?= h($u['membership_until']) ?>까지</div>
    <?php endif; ?>
  </div>

  <div class="perks">
    <div><span>🚚</span><h3>전 상품 무료배송</h3><p>금액 상관없이 일반배송 상품도 배송비 0원</p></div>
    <div><span>💸</span><h3>추가 <?= MEMBER_DC ?>% 할인</h3><p>할인가에서 한 번 더, 결제 금액에서 자동 적용</p></div>
    <div><span>🚀</span><h3>쫑배송 내일 도착</h3><p>밤 12시 전 주문하면 내일 문 앞까지</p></div>
    <div><span>↩️</span><h3>30일 무료 반품</h3><p>단순 변심도 30일 안에 무료로 반품</p></div>
  </div>

  <form method="post" class="club-cta">
    <?= csrf_field() ?>
    <?php if (!$u): ?>
      <a class="btn big" href="/login.php?next=/membership.php">로그인하고 가입하기</a>
    <?php elseif ($member): ?>
      <button class="btn big" name="action" value="join">30일 연장하기</button>
      <button class="btn ghost" name="action" value="cancel" onclick="return confirm('쫑클럽을 해지할까요?')">해지하기</button>
    <?php else: ?>
      <button class="btn big" name="action" value="join">월 <?= won(MEMBER_FEE) ?>로 가입하기</button>
    <?php endif; ?>
    <p class="hint">기본 결제 카드로 매월 결제됩니다. 실습용 쇼핑몰이라 실제 결제는 없습니다.</p>
  </form>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
