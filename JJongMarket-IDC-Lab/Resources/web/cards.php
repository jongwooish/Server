<?php
// 결제수단 관리 — 실습용. 카드번호 전체와 CVC는 검사만 하고 저장하지 않는다 (끝 4자리만 저장)
require __DIR__ . '/inc/app.php';
$u = require_login();
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '/cards.php');

function luhn_ok(string $n): bool
{
    $sum = 0; $alt = false;
    for ($i = strlen($n) - 1; $i >= 0; $i--) {
        $d = (int)$n[$i];
        if ($alt) { $d *= 2; if ($d > 9) $d -= 9; }
        $sum += $d; $alt = !$alt;
    }
    return $sum % 10 === 0;
}

function card_brand(string $n): string
{
    return match (true) {
        str_starts_with($n, '4')                   => 'VISA',
        (bool)preg_match('/^5[1-5]/', $n)          => 'Mastercard',
        (bool)preg_match('/^3[47]/', $n)           => 'AMEX',
        str_starts_with($n, '9')                   => '국내전용',
        default                                    => '카드',
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $num    = preg_replace('/\D/', '', $_POST['number'] ?? '');
        $holder = trim($_POST['holder'] ?? '');
        $mm     = (int)($_POST['mm'] ?? 0);
        $yy     = (int)($_POST['yy'] ?? 0) + 2000;
        $cvc    = preg_replace('/\D/', '', $_POST['cvc'] ?? '');
        $err = match (true) {
            strlen($num) < 15 || strlen($num) > 16 || !luhn_ok($num) => '카드번호를 다시 확인해 주세요.',
            $holder === ''                                          => '카드 소유자 이름을 입력해 주세요.',
            $mm < 1 || $mm > 12                                     => '유효기간(월)을 확인해 주세요.',
            sprintf('%04d-%02d', $yy, $mm) < date('Y-m')            => '유효기간이 지난 카드입니다.',
            strlen($cvc) < 3                                        => 'CVC 3자리를 입력해 주세요.',
            default                                                 => null,
        };
        if ($err) { flash($err, 'warn'); redirect('/cards.php?next=' . urlencode($next)); }

        $first = !(int)q('SELECT COUNT(*) n FROM cards WHERE user_id = ?', [$u['id']])->fetch()['n'];
        q('INSERT INTO cards (user_id, brand, last4, holder, exp_month, exp_year, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)',
          [$u['id'], card_brand($num), substr($num, -4), mb_substr($holder, 0, 50), $mm, $yy, $first ? 1 : 0]);
        flash('카드를 등록했습니다.');
        redirect($next);
    }

    $cid = (int)($_POST['card_id'] ?? 0);
    if ($action === 'default') {
        q('UPDATE cards SET is_default = 0 WHERE user_id = ?', [$u['id']]);
        q('UPDATE cards SET is_default = 1 WHERE id = ? AND user_id = ?', [$cid, $u['id']]);
        flash('기본 결제수단을 변경했습니다.');
    } elseif ($action === 'delete') {
        q('DELETE FROM cards WHERE id = ? AND user_id = ?', [$cid, $u['id']]);
        flash('카드를 삭제했습니다.');
    }
    redirect('/cards.php');
}

$cards = q('SELECT * FROM cards WHERE user_id = ? ORDER BY is_default DESC, id DESC', [$u['id']])->fetchAll();
$title = '결제수단 관리';
include __DIR__ . '/inc/header.php';
?>
<div class="mylayout">
  <?php $mytab = 'cards'; include __DIR__ . '/inc/mymenu.php'; ?>
  <section>
    <h1 class="page">💳 결제수단 관리</h1>

    <div class="cardlist">
      <?php foreach ($cards as $c): ?>
        <div class="paycard <?= $c['is_default'] ? 'default' : '' ?>">
          <div class="pc-brand"><?= h($c['brand']) ?> <?= $c['is_default'] ? '<em>기본</em>' : '' ?></div>
          <div class="pc-num">**** **** **** <?= h($c['last4']) ?></div>
          <div class="pc-foot"><span><?= h($c['holder']) ?></span><span><?= sprintf('%02d/%02d', $c['exp_month'], $c['exp_year'] % 100) ?></span></div>
          <form method="post" class="pc-actions">
            <?= csrf_field() ?><input type="hidden" name="card_id" value="<?= (int)$c['id'] ?>">
            <?php if (!$c['is_default']): ?><button name="action" value="default">기본으로</button><?php endif; ?>
            <button name="action" value="delete">삭제</button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (!$cards): ?><p class="muted">등록된 카드가 없습니다.</p><?php endif; ?>
    </div>

    <div class="box">
      <h3>새 카드 등록</h3>
      <form method="post" class="form cardform" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="next" value="<?= h($next) ?>">
        <label class="field">카드번호<input name="number" inputmode="numeric" maxlength="19" placeholder="0000 0000 0000 0000" required></label>
        <div class="inline">
          <label class="field">유효기간(월)<input name="mm" inputmode="numeric" maxlength="2" placeholder="MM" required></label>
          <label class="field">유효기간(년)<input name="yy" inputmode="numeric" maxlength="2" placeholder="YY" required></label>
          <label class="field">CVC<input name="cvc" inputmode="numeric" maxlength="4" placeholder="000" required></label>
        </div>
        <label class="field">카드 소유자<input name="holder" value="<?= h($u['name']) ?>" required></label>
        <button class="btn">카드 등록</button>
        <p class="hint">
          실습용 화면입니다. 카드번호는 형식(Luhn)만 검사하고 <b>끝 4자리만 저장</b>합니다. CVC는 저장하지 않습니다.<br>
          테스트 번호: <code>4111 1111 1111 1111</code>, <code>5555 5555 5555 4444</code>
        </p>
      </form>
    </div>
  </section>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
