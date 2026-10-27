<?php
require __DIR__ . '/inc/app.php';
$u = require_login();

$orders = q('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [$u['id']])->fetchAll();
$items  = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    foreach (q("SELECT * FROM order_items WHERE order_id IN ($in) ORDER BY id", $ids) as $it) {
        $items[$it['order_id']][] = $it;
    }
}

$title = '주문목록';
include __DIR__ . '/inc/header.php';
?>
<div class="mylayout">
  <?php $mytab = 'orders'; include __DIR__ . '/inc/mymenu.php'; ?>
  <section>
    <h1 class="page">주문목록 <small><?= count($orders) ?>건</small></h1>
    <?php if (!$orders): ?>
      <div class="empty"><div class="big">📦</div><h2>주문 내역이 없습니다</h2><a class="btn" href="/">쇼핑하러 가기</a></div>
    <?php endif; ?>
    <?php foreach ($orders as $o): $st = order_stage($o); ?>
      <div class="order">
        <div class="ohead">
          <b><?= h(substr((string)$o['created_at'], 0, 10)) ?> 주문</b>
          <span class="muted">주문번호 <?= (int)$o['id'] ?></span>
          <a class="more" href="/order.php?id=<?= (int)$o['id'] ?>">주문 상세보기 ›</a>
        </div>
        <div class="ostage <?= $st['canceled'] ? 'canceled' : '' ?>">
          <b><?= h($st['label']) ?></b>
          <?php if (!$st['canceled'] && $st['index'] < 4): ?><span><?= h(STAGES[$st['index']][2]) ?></span><?php endif; ?>
        </div>
        <?php foreach ($items[$o['id']] ?? [] as $it): ?>
          <div class="oline">
            <a href="/item.php?id=<?= (int)$it['product_id'] ?>"><span class="withimg"><?= pimg((int)$it['product_id'], $it['name']) ?><?= h($it['name']) ?></span></a>
            <span><?= won((int)$it['price']) ?> × <?= (int)$it['qty'] ?></span>
          </div>
        <?php endforeach; ?>
        <div class="ofoot">
          <span class="muted"><?= h($o['pay_label']) ?> · 송장 <?= h($o['tracking_no']) ?></span>
          <span><b><?= won((int)$o['total']) ?></b></span>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
