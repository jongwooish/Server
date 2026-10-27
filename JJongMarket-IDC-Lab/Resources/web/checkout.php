<?php
require __DIR__ . '/inc/app.php';
$u = require_login();
$s = cart_summary($u);
if (!$s['items']) redirect('/cart.php');

$cards = q('SELECT * FROM cards WHERE user_id = ? ORDER BY is_default DESC, id DESC', [$u['id']])->fetchAll();
$banks = ['쫑뱅크', '한빛은행', '푸른은행', '바다은행'];
$telcos = ['SKT', 'KT', 'LG U+', '알뜰폰'];

$f = [
    'recipient' => $u['name'], 'phone' => $u['phone'], 'address' => $u['address'],
    'memo' => DELIVERY_MEMOS[0], 'pay_method' => $cards ? '카드' : '계좌이체',
    'card_id' => $cards[0]['id'] ?? '', 'bank' => $banks[0], 'telco' => $telcos[0],
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($f as $k => $v) $f[$k] = trim((string)($_POST[$k] ?? $v));

    if (mb_strlen($f['recipient']) < 2)                       $errors[] = '받는 사람 이름을 입력해 주세요.';
    if (!preg_match('/^01\d-?\d{3,4}-?\d{4}$/', $f['phone']))  $errors[] = '연락처를 010-0000-0000 형식으로 입력해 주세요.';
    if (mb_strlen($f['address']) < 5)                         $errors[] = '배송 주소를 입력해 주세요.';
    if (!in_array($f['memo'], DELIVERY_MEMOS, true))          $f['memo'] = DELIVERY_MEMOS[0];

    $pay_label = '';
    if ($f['pay_method'] === '카드') {
        $card = q('SELECT * FROM cards WHERE id = ? AND user_id = ?', [(int)$f['card_id'], $u['id']])->fetch();
        if (!$card) $errors[] = '결제할 카드를 선택해 주세요.';
        else $pay_label = $card['brand'] . ' ****' . $card['last4'];
    } elseif ($f['pay_method'] === '계좌이체') {
        if (!in_array($f['bank'], $banks, true)) $errors[] = '은행을 선택해 주세요.';
        else $pay_label = $f['bank'] . ' 계좌이체';
    } elseif ($f['pay_method'] === '휴대폰') {
        if (!in_array($f['telco'], $telcos, true)) $errors[] = '통신사를 선택해 주세요.';
        else $pay_label = $f['telco'] . ' 휴대폰 결제';
    } else {
        $errors[] = '결제 수단을 선택해 주세요.';
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // 재고 차감 — 그사이 다른 손님이 사서 재고가 모자라면 주문 전체 취소
            foreach ($s['items'] as $it) {
                $ok = q('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?', [$it['qty'], $it['id'], $it['qty']])->rowCount();
                if (!$ok) throw new RuntimeException($it['name'] . ' 재고가 부족합니다.');
            }
            $tracking = 'JJ' . date('ymd') . random_int(100000, 999999);
            q('INSERT INTO orders (user_id, recipient, phone, address, memo, pay_method, pay_label, tracking_no,
                                   items_total, member_dc, shipping_fee, total, created_at)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
              [$u['id'], $f['recipient'], $f['phone'], $f['address'], $f['memo'], $f['pay_method'], $pay_label, $tracking,
               $s['items_total'], $s['member_dc'], $s['shipping'], $s['total'], now()]);
            $oid = (int)$pdo->lastInsertId();
            foreach ($s['items'] as $it) {
                q('INSERT INTO order_items (order_id, product_id, name, icon, price, qty) VALUES (?, ?, ?, ?, ?, ?)',
                  [$oid, $it['id'], $it['name'], $it['icon'], $it['sale'], $it['qty']]);
            }
            q('DELETE FROM cart_items WHERE user_id = ?', [$u['id']]);
            if (!empty($_POST['save_address'])) {
                q('UPDATE users SET address = ?, phone = ? WHERE id = ?', [$f['address'], $f['phone'], $u['id']]);
            }
            $pdo->commit();
        } catch (RuntimeException $e) {
            $pdo->rollBack();
            flash($e->getMessage() . ' 장바구니를 확인해 주세요.', 'warn');
            redirect('/cart.php');
        }
        redirect('/order.php?id=' . $oid . '&done=1');
    }
}

$title = '주문/결제';
include __DIR__ . '/inc/header.php';
?>
<h1 class="page">주문/결제</h1>
<?php if ($errors): ?><div class="flash warn"><?= implode('<br>', array_map('h', $errors)) ?></div><?php endif; ?>

<form method="post" class="cartwrap" id="checkout">
  <?= csrf_field() ?>
  <div class="cartlist">
    <div class="box">
      <h3>구매자 정보</h3>
      <table class="spec">
        <tr><th>이름</th><td><?= h($u['name']) ?></td></tr>
        <tr><th>이메일</th><td><?= h($u['email']) ?></td></tr>
        <tr><th>휴대폰</th><td><?= h($u['phone'] ?: '-') ?></td></tr>
      </table>
    </div>

    <div class="box">
      <h3>받는 사람 정보</h3>
      <div class="form">
        <div class="inline">
          <label class="field">받는 사람<input name="recipient" value="<?= h($f['recipient']) ?>" required></label>
          <label class="field">연락처<input name="phone" value="<?= h($f['phone']) ?>" placeholder="010-0000-0000" required></label>
        </div>
        <label class="field">배송 주소<input name="address" value="<?= h($f['address']) ?>" placeholder="도로명 주소, 동·호수까지" required></label>
        <label class="field">배송 요청사항
          <select name="memo">
            <?php foreach (DELIVERY_MEMOS as $m): ?><option <?= $f['memo'] === $m ? 'selected' : '' ?>><?= h($m) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label class="check"><input type="checkbox" name="save_address" value="1" checked> 기본 배송지로 저장</label>
      </div>
    </div>

    <div class="box">
      <h3>배송 상품 <small><?= count($s['items']) ?>건</small></h3>
      <?php foreach ($s['items'] as $it): ?>
        <div class="oline">
          <span class="withimg"><?= pimg((int)$it['id'], $it['name']) ?><?= h($it['name']) ?> × <?= (int)$it['qty'] ?></span>
          <span><?= $it['delivery'] === '쫑배송' ? '<span class="badge rocket">🚀 ' . h(arrive_text('쫑배송')) . '</span> ' : '' ?><b><?= won($it['line']) ?></b></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="box">
      <h3>결제 수단</h3>
      <div class="paytabs">
        <?php foreach (['카드' => '💳 신용/체크카드', '계좌이체' => '🏦 계좌이체', '휴대폰' => '📱 휴대폰'] as $k => $label): ?>
          <label><input type="radio" name="pay_method" value="<?= $k ?>" <?= $f['pay_method'] === $k ? 'checked' : '' ?>><span><?= $label ?></span></label>
        <?php endforeach; ?>
      </div>

      <div class="paypanel" data-for="카드">
        <?php if (!$cards): ?>
          <p>등록된 카드가 없습니다. <a class="btn small" href="/cards.php?next=/checkout.php">카드 등록하기</a></p>
        <?php else: ?>
          <?php foreach ($cards as $c): ?>
            <label class="cardopt">
              <input type="radio" name="card_id" value="<?= (int)$c['id'] ?>" <?= (string)$f['card_id'] === (string)$c['id'] ? 'checked' : '' ?>>
              <span class="minicard"><?= h($c['brand']) ?> **** <?= h($c['last4']) ?></span>
              <?= $c['is_default'] ? '<em>기본</em>' : '' ?>
            </label>
          <?php endforeach; ?>
          <a href="/cards.php?next=/checkout.php" class="muted">+ 다른 카드 등록</a>
        <?php endif; ?>
      </div>
      <div class="paypanel" data-for="계좌이체">
        <label class="field">은행 선택
          <select name="bank"><?php foreach ($banks as $b): ?><option <?= $f['bank'] === $b ? 'selected' : '' ?>><?= $b ?></option><?php endforeach; ?></select>
        </label>
        <p class="hint">결제하기를 누르면 선택한 은행으로 바로 이체된 것으로 처리됩니다. (가상)</p>
      </div>
      <div class="paypanel" data-for="휴대폰">
        <label class="field">통신사
          <select name="telco"><?php foreach ($telcos as $t): ?><option <?= $f['telco'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select>
        </label>
        <p class="hint">결제 금액은 다음 달 휴대폰 요금에 합산됩니다. (가상)</p>
      </div>
    </div>
  </div>

  <aside class="summary">
    <h3>최종 결제 금액</h3>
    <dl>
      <dt>총 상품 가격</dt><dd><?= won($s['list']) ?></dd>
      <dt>상품 할인</dt><dd class="red">-<?= won($s['product_dc']) ?></dd>
      <?php if ($s['member']): ?><dt>쫑클럽 추가 할인</dt><dd class="red">-<?= won($s['member_dc']) ?></dd><?php endif; ?>
      <dt>배송비</dt><dd><?= $s['shipping'] ? '+' . won($s['shipping']) : '무료' ?></dd>
    </dl>
    <div class="total"><span>총 결제 금액</span><b><?= won($s['total']) ?></b></div>
    <button class="btn block"><?= won($s['total']) ?> 결제하기</button>
    <p class="hint">실습용 쇼핑몰입니다. 실제 결제와 배송은 일어나지 않습니다.</p>
  </aside>
</form>
<script>
  // 선택한 결제 수단의 입력칸만 보이기
  const form = document.getElementById('checkout');
  function showPay() {
    const v = form.querySelector('input[name=pay_method]:checked')?.value;
    form.querySelectorAll('.paypanel').forEach(p => p.hidden = p.dataset.for !== v);
  }
  form.addEventListener('change', showPay); showPay();
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
