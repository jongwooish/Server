<?php
require __DIR__ . '/inc/app.php';
if (!user() && $_SERVER['REQUEST_METHOD'] === 'POST') {   // 비로그인으로 담기 → 로그인 후 상품으로 돌아오기
    flash('로그인 후 장바구니를 이용할 수 있습니다.', 'warn');
    redirect('/login.php?next=' . urlencode('/item.php?id=' . (int)($_POST['product_id'] ?? 0)));
}
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pid    = (int)($_POST['product_id'] ?? 0);
    $qty    = max(1, min(10, (int)($_POST['qty'] ?? 1)));
    $action = $_POST['action'] ?? '';
    $p      = q('SELECT id, stock FROM products WHERE id = ?', [$pid])->fetch();

    if ($action === 'remove') {
        q('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?', [$u['id'], $pid]);
        flash('장바구니에서 삭제했습니다.');
    } elseif (!$p || $p['stock'] <= 0) {
        flash('품절된 상품입니다.', 'warn');
    } elseif ($action === 'update') {
        q('UPDATE cart_items SET qty = ? WHERE user_id = ? AND product_id = ?', [min($qty, (int)$p['stock']), $u['id'], $pid]);
    } elseif ($action === 'add' || $action === 'buy') {
        $cur = q('SELECT qty FROM cart_items WHERE user_id = ? AND product_id = ?', [$u['id'], $pid])->fetch();
        if ($action === 'buy') {
            // 바로구매: 장바구니를 이 상품 하나로 바꾸고 결제로
            q('DELETE FROM cart_items WHERE user_id = ?', [$u['id']]);
            q('INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)', [$u['id'], $pid, min($qty, (int)$p['stock'])]);
            redirect('/checkout.php');
        }
        if ($cur) {
            q('UPDATE cart_items SET qty = ? WHERE user_id = ? AND product_id = ?', [min($cur['qty'] + $qty, (int)$p['stock'], 10), $u['id'], $pid]);
        } else {
            q('INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)', [$u['id'], $pid, min($qty, (int)$p['stock'])]);
        }
        flash('장바구니에 담았습니다.');
    }
    redirect('/cart.php');
}

$s = cart_summary($u);
$title = '장바구니';
include __DIR__ . '/inc/header.php';
?>
<h1 class="page">🛒 장바구니 <small><?= count($s['items']) ?>개 상품</small></h1>

<?php if (!$s['items']): ?>
  <div class="empty"><div class="big">🛒</div><h2>장바구니가 비어 있습니다</h2><a class="btn" href="/">쇼핑 계속하기</a></div>
<?php else: ?>
<div class="cartwrap">
  <div class="cartlist">
    <?php foreach ($s['items'] as $it): ?>
      <div class="citem">
        <a class="ci-ic" href="/item.php?id=<?= (int)$it['id'] ?>"><?= pimg((int)$it['id'], $it['name']) ?></a>
        <div class="ci-body">
          <?php if ($it['delivery'] === '쫑배송'): ?><div class="badge rocket">🚀 쫑배송 · <?= h(arrive_text('쫑배송')) ?></div><?php endif; ?>
          <a class="ci-name" href="/item.php?id=<?= (int)$it['id'] ?>"><?= h($it['name']) ?></a>
          <div class="muted"><?= won($it['sale']) ?> <?= $it['discount'] ? '<s>' . won((int)$it['price']) . '</s>' : '' ?></div>
          <?php if ($it['stock'] < $it['qty']): ?><div class="warn-text">재고가 <?= (int)$it['stock'] ?>개 남았습니다. 수량을 줄여 주세요.</div><?php endif; ?>
        </div>
        <form method="post" class="ci-qty">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
          <select name="qty" onchange="this.form.submit()">
            <?php for ($i = 1; $i <= 10; $i++): ?><option <?= $i == $it['qty'] ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?>
          </select>
          <input type="hidden" name="action" value="update">
        </form>
        <div class="ci-line"><?= won($it['line']) ?></div>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
          <button name="action" value="remove" class="x" title="삭제">✕</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <aside class="summary">
    <h3>주문 예상 금액</h3>
    <dl>
      <dt>총 상품 가격</dt><dd><?= won($s['list']) ?></dd>
      <dt>상품 할인</dt><dd class="red">-<?= won($s['product_dc']) ?></dd>
      <?php if ($s['member']): ?><dt>쫑클럽 추가 할인</dt><dd class="red">-<?= won($s['member_dc']) ?></dd><?php endif; ?>
      <dt>배송비</dt><dd><?= $s['shipping'] ? '+' . won($s['shipping']) : '무료' ?></dd>
    </dl>
    <div class="total"><span>결제 예정 금액</span><b><?= won($s['total']) ?></b></div>
    <?php if (!$s['member']): ?>
      <p class="hint">
        <?= $s['shipping'] ? won(FREE_SHIP_MIN - $s['items_total']) . ' 더 담으면 무료배송!<br>' : '' ?>
        쫑클럽이면 <b><?= won((int)(floor($s['items_total'] * MEMBER_DC / 100 / 10) * 10) + $s['shipping']) ?></b> 더 아껴요. <a href="/membership.php">가입 ›</a>
      </p>
    <?php endif; ?>
    <a class="btn block" href="/checkout.php">구매하기</a>
  </aside>
</div>
<?php endif; ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
