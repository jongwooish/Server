<?php
require __DIR__ . '/inc/app.php';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '/');
$old  = ['email' => '', 'name' => '', 'phone' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = array_merge($old, array_map(fn($v) => trim((string)$v), array_intersect_key($_POST, $old)));
    $pw  = $_POST['password'] ?? '';

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))           $errors[] = '이메일 형식을 확인해 주세요.';
    if (mb_strlen($old['name']) < 2)                                  $errors[] = '이름을 2자 이상 입력해 주세요.';
    if (strlen($pw) < 8 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw))
                                                                      $errors[] = '비밀번호는 영문과 숫자를 포함해 8자 이상이어야 합니다.';
    if ($pw !== ($_POST['password2'] ?? ''))                          $errors[] = '비밀번호 확인이 일치하지 않습니다.';
    if (!$errors && q('SELECT 1 FROM users WHERE email = ?', [$old['email']])->fetch())
                                                                      $errors[] = '이미 가입된 이메일입니다.';
    if (!$errors) {
        q('INSERT INTO users (email, name, password_hash, phone) VALUES (?, ?, ?, ?)',
          [$old['email'], mb_substr($old['name'], 0, 50), password_hash($pw, PASSWORD_DEFAULT), mb_substr($old['phone'], 0, 20)]);
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)db()->lastInsertId();
        flash($old['name'] . '님, 쫑마켓 가입을 환영합니다!');
        redirect($next);
    }
}

$title = '회원가입';
include __DIR__ . '/inc/header.php';
?>
<div class="authbox">
  <a class="logo" href="/">쫑마켓</a>
  <h2>회원가입</h2>
  <?php if ($errors): ?><div class="flash warn"><?= implode('<br>', array_map('h', $errors)) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next) ?>">
    <label class="field">이메일<input type="email" name="email" value="<?= h($old['email']) ?>" required></label>
    <label class="field">비밀번호<input type="password" name="password" placeholder="영문, 숫자 포함 8자 이상" required></label>
    <label class="field">비밀번호 확인<input type="password" name="password2" required></label>
    <label class="field">이름<input name="name" value="<?= h($old['name']) ?>" required></label>
    <label class="field">휴대폰 번호<input name="phone" value="<?= h($old['phone']) ?>" placeholder="010-0000-0000"></label>
    <button class="btn block">동의하고 가입하기</button>
  </form>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
