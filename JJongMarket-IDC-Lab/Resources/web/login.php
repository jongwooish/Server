<?php
require __DIR__ . '/inc/app.php';
$next  = safe_next($_GET['next'] ?? $_POST['next'] ?? '/');
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $row = q('SELECT id, password_hash FROM users WHERE email = ?', [$email])->fetch();
    if ($row && password_verify($_POST['password'] ?? '', $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$row['id'];
        redirect($next);
    }
    flash('이메일 또는 비밀번호가 올바르지 않습니다.', 'warn');
    redirect('/login.php?next=' . urlencode($next));
}

$title = '로그인';
include __DIR__ . '/inc/header.php';
?>
<div class="authbox">
  <a class="logo" href="/">쫑마켓</a>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next) ?>">
    <label class="field">이메일<input type="email" name="email" value="<?= h($email) ?>" placeholder="아이디(이메일)" required autofocus></label>
    <label class="field">비밀번호<input type="password" name="password" placeholder="비밀번호" required></label>
    <button class="btn block">로그인</button>
  </form>
  <p class="muted">아직 회원이 아니신가요? <a href="/signup.php?next=<?= urlencode($next) ?>">회원가입</a></p>
  <p class="hint">데모 계정: demo@jjongmarket.kr / Jjong1234!</p>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
