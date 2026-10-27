<?php
// 상품평 쓰기 — 배송완료된 내 주문 상품만, 상품당 한 번
require __DIR__ . '/inc/app.php';
$u = require_login();

$it = q('SELECT oi.*, o.user_id, o.status, o.created_at AS ordered_at
         FROM order_items oi JOIN orders o ON o.id = oi.order_id
         WHERE oi.id = ? AND o.user_id = ?', [(int)($_GET['item'] ?? $_POST['item'] ?? 0), $u['id']])->fetch();
if (!$it) { flash('리뷰를 쓸 수 있는 상품이 아닙니다.', 'warn'); redirect('/orders.php'); }

$st = order_stage(['created_at' => $it['ordered_at'], 'status' => $it['status']]);
if ($st['canceled'] || $st['index'] < 4 || $it['reviewed']) {
    flash($it['reviewed'] ? '이미 리뷰를 작성했습니다.' : '배송이 완료된 뒤에 리뷰를 쓸 수 있습니다.', 'warn');
    redirect('/order.php?id=' . (int)$it['order_id']);
}

$rating = 5; $body = ''; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $body   = trim($_POST['body'] ?? '');
    if (mb_strlen($body) < 10) $errors[] = '리뷰를 10자 이상 작성해 주세요.';

    if (!$errors) {
        // 이름 가운데를 가려서 표시: 김쫑 → 김*, 김민수 → 김*수
        $n = $u['name'];
        $masked = mb_strlen($n) <= 2 ? mb_substr($n, 0, 1) . '*' : mb_substr($n, 0, 1) . '*' . mb_substr($n, -1);
        $pdo = db();
        $pdo->beginTransaction();
        q('INSERT INTO reviews (product_id, user_id, author, rating, body, option_txt, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
          [$it['product_id'], $u['id'], $masked, $rating, $body, $it['qty'] . '개 구매', now()]);
        q('UPDATE products SET rating = ROUND((rating * reviews + ?) / (reviews + 1), 1), reviews = reviews + 1 WHERE id = ?',
          [$rating, $it['product_id']]);
        q('UPDATE order_items SET reviewed = 1 WHERE id = ?', [$it['id']]);
        $pdo->commit();
        flash('소중한 리뷰 감사합니다!');
        redirect('/item.php?id=' . (int)$it['product_id'] . '#review');
    }
}

$title = '리뷰 쓰기';
include __DIR__ . '/inc/header.php';
?>
<div class="authbox wide">
  <h2>상품평 쓰기</h2>
  <p class="review-target"><?= pimg((int)$it['product_id'], $it['name']) ?><?= h($it['name']) ?></p>
  <?php if ($errors): ?><div class="flash warn"><?= implode('<br>', array_map('h', $errors)) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="item" value="<?= (int)$it['id'] ?>">
    <div class="starpick">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <input type="radio" id="s<?= $i ?>" name="rating" value="<?= $i ?>" <?= $rating === $i ? 'checked' : '' ?>><label for="s<?= $i ?>">★</label>
      <?php endfor; ?>
    </div>
    <label class="field">어떤 점이 좋았나요?<textarea name="body" rows="6" placeholder="상품을 사용해 보신 느낌을 10자 이상 남겨 주세요." required><?= h($body) ?></textarea></label>
    <button class="btn block">등록하기</button>
  </form>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
