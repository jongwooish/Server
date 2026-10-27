<?php
// 주문 상세 + 가상 배송 조회 + 주문취소 / 구매확정
require __DIR__ . '/inc/app.php';
$u = require_login();

$o = q('SELECT * FROM orders WHERE id = ? AND user_id = ?', [(int)($_GET['id'] ?? $_POST['id'] ?? 0), $u['id']])->fetch();
if (!$o) { flash('주문을 찾을 수 없습니다.', 'warn'); redirect('/orders.php'); }
$st = order_stage($o);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'cancel' && !$st['canceled'] && $o['status'] === '진행' && $st['index'] < 2) {
        $pdo = db();
        $pdo->beginTransaction();
        foreach (q('SELECT product_id, qty FROM order_items WHERE order_id = ?', [$o['id']])->fetchAll() as $it) {
            q('UPDATE products SET stock = stock + ? WHERE id = ?', [$it['qty'], $it['product_id']]);
        }
        q("UPDATE orders SET status = '주문취소', closed_at = ? WHERE id = ?", [now(), $o['id']]);
        $pdo->commit();
        flash('주문을 취소했습니다. ' . h($o['pay_label']) . '로 ' . won((int)$o['total']) . ' 환불됩니다. (가상)');
    } elseif ($action === 'confirm' && $o['status'] === '진행' && $st['index'] === 4) {
        q("UPDATE orders SET status = '구매확정', closed_at = ? WHERE id = ?", [now(), $o['id']]);
        flash('구매를 확정했습니다. 상품평을 남겨 주세요!');
    } else {
        flash('지금은 처리할 수 없는 요청입니다.', 'warn');
    }
    redirect('/order.php?id=' . $o['id']);
}

$items = q('SELECT oi.*, p.delivery FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?', [$o['id']])->fetchAll();
$fast  = in_array('쫑배송', array_column($items, 'delivery'), true);
$title = '주문 상세';
include __DIR__ . '/inc/header.php';
?>
<div class="mylayout">
  <?php $mytab = 'orders'; include __DIR__ . '/inc/mymenu.php'; ?>
  <section>
    <?php if (!empty($_GET['done'])): ?>
      <div class="done-box">
        <div class="big">✅</div>
        <h2>주문이 완료되었습니다</h2>
        <p><?= h($o['recipient']) ?>님께 <b><?= h(arrive_text($fast ? '쫑배송' : '일반배송')) ?></b> · <?= h($o['pay_label']) ?> <?= won((int)$o['total']) ?> 결제</p>
        <div class="buttons"><a class="btn ghost" href="/">쇼핑 계속하기</a><a class="btn" href="/orders.php">주문목록 보기</a></div>
      </div>
    <?php endif; ?>

    <h1 class="page">주문 상세 <small>주문번호 <?= (int)$o['id'] ?> · <?= h(substr((string)$o['created_at'], 0, 16)) ?></small></h1>

    <div class="box">
      <h3>배송 조회 <small>쫑로지스 · 송장번호 <?= h($o['tracking_no']) ?></small></h3>
      <?php if ($st['canceled']): ?>
        <p class="red"><b>주문취소</b> · <?= h(substr((string)$o['closed_at'], 0, 16)) ?> 취소 완료, <?= h($o['pay_label']) ?>로 환불</p>
      <?php else: ?>
        <ol class="track">
          <?php foreach (STAGES as $i => [$label, $after, $desc]): ?>
            <li class="<?= $i < $st['index'] ? 'past' : ($i === $st['index'] ? 'now' : '') ?>">
              <span class="dot"><?= $i <= $st['index'] ? '✓' : $i + 1 ?></span>
              <b><?= h($label) ?></b>
              <small><?= $i <= $st['index'] ? h($st['times'][$i]) : '예정' ?></small>
            </li>
          <?php endforeach; ?>
        </ol>
        <p class="track-desc"><?= h(STAGES[$st['index']][2]) ?></p>
        <?php if ($st['index'] < 4): ?>
          <p class="hint">가상 배송입니다. 결제 후 1분 → 상품준비중, 3분 → 배송시작, 5분 → 배송중, 8분 → 배송완료로 바뀝니다. 새로고침해서 확인하세요.</p>
        <?php endif; ?>
      <?php endif; ?>

      <form method="post" class="order-actions">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
        <?php if ($o['status'] === '진행' && $st['index'] < 2): ?>
          <button class="btn ghost" name="action" value="cancel" onclick="return confirm('주문을 취소할까요?')">주문 취소</button>
          <span class="hint">배송이 시작되기 전까지 취소할 수 있습니다.</span>
        <?php elseif ($o['status'] === '진행' && $st['index'] === 4): ?>
          <button class="btn" name="action" value="confirm">구매 확정</button>
        <?php elseif ($o['status'] === '구매확정'): ?>
          <span class="green"><b>구매확정 완료</b></span>
        <?php endif; ?>
      </form>
    </div>

    <div class="box">
      <h3>주문 상품</h3>
      <?php foreach ($items as $it): ?>
        <div class="oline">
          <a href="/item.php?id=<?= (int)$it['product_id'] ?>"><span class="withimg"><?= pimg((int)$it['product_id'], $it['name']) ?><?= h($it['name']) ?></span> <small>× <?= (int)$it['qty'] ?></small></a>
          <span>
            <b><?= won((int)$it['price'] * (int)$it['qty']) ?></b>
            <?php if (!$st['canceled'] && $st['index'] === 4): ?>
              <?= $it['reviewed'] ? '<span class="muted">리뷰 작성 완료</span>' : '<a class="btn small" href="/review.php?item=' . (int)$it['id'] . '">리뷰 쓰기</a>' ?>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="cols2">
      <div class="box">
        <h3>받는 사람</h3>
        <table class="spec">
          <tr><th>이름</th><td><?= h($o['recipient']) ?></td></tr>
          <tr><th>연락처</th><td><?= h($o['phone']) ?></td></tr>
          <tr><th>주소</th><td><?= h($o['address']) ?></td></tr>
          <tr><th>요청사항</th><td><?= h($o['memo']) ?></td></tr>
        </table>
      </div>
      <div class="box">
        <h3>결제 정보</h3>
        <table class="spec">
          <tr><th>결제수단</th><td><?= h($o['pay_label']) ?></td></tr>
          <tr><th>상품 금액</th><td><?= won((int)$o['items_total']) ?></td></tr>
          <tr><th>쫑클럽 할인</th><td class="red">-<?= won((int)$o['member_dc']) ?></td></tr>
          <tr><th>배송비</th><td><?= $o['shipping_fee'] ? won((int)$o['shipping_fee']) : '무료' ?></td></tr>
          <tr><th>총 결제</th><td><b><?= won((int)$o['total']) ?></b></td></tr>
        </table>
      </div>
    </div>
  </section>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
