<?php
// 공통 머리 — 페이지에서 $title 을 정한 뒤 include
$u      = user();
$member = is_member($u);
$qv     = $_GET['q'] ?? '';
$catv   = $_GET['cat'] ?? '';
$self   = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(($title ?? '') ? $title . ' - 쫑마켓' : '쫑마켓 - 내일 도착하는 생활 쇼핑') ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="topbar">
  <div class="wrap">
    <span><?= $member ? '🏅 쫑클럽 회원님, 무료배송 + 추가 ' . MEMBER_DC . '% 할인 중' : '쫑클럽 가입하고 무료배송 + 추가 ' . MEMBER_DC . '% 할인 받기 → <a href="/membership.php">자세히</a>' ?></span>
    <span class="toplinks">
      <?php if ($u): ?>
        <b><?= h($u['name']) ?></b>님
        <a href="/logout.php?t=<?= csrf_token() ?>">로그아웃</a>
      <?php else: ?>
        <a href="/login.php">로그인</a>
        <a href="/signup.php">회원가입</a>
      <?php endif; ?>
      <a href="/cs.php">고객센터</a>
      <a href="/sell.php">판매자센터</a>
    </span>
  </div>
</div>

<header class="site">
  <div class="wrap">
    <a class="logo" href="/">쫑마켓</a>
    <form class="search" action="/products.php" method="get">
      <select name="cat" aria-label="카테고리">
        <option value="">전체</option>
        <?php foreach (CATEGORIES as $c => $ic): ?>
          <option value="<?= h($c) ?>" <?= $catv === $c ? 'selected' : '' ?>><?= h($c) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="search" name="q" value="<?= h($qv) ?>" placeholder="찾고 싶은 상품을 검색해 보세요!">
      <button type="submit" aria-label="검색">🔍</button>
    </form>
    <nav class="icons">
      <a href="/mypage.php"><span>👤</span>마이쫑</a>
      <a href="/cart.php"><span>🛒</span>장바구니<?php $cc = cart_count(); if ($cc): ?><em><?= $cc ?></em><?php endif; ?></a>
    </nav>
  </div>
</header>

<nav class="catbar">
  <div class="wrap">
    <a href="/products.php" class="<?= $self === 'products.php' && !$catv ? 'on' : '' ?>">☰ 전체 상품</a>
    <?php foreach (CATEGORIES as $c => $ic): ?>
      <a href="/products.php?cat=<?= urlencode($c) ?>" class="<?= $catv === $c ? 'on' : '' ?>"><?= $ic ?> <?= h($c) ?></a>
    <?php endforeach; ?>
    <a href="/live.php" class="livelink <?= $self === 'live.php' ? 'on' : '' ?>"><i></i>쫑라이브</a>
    <a href="/products.php?rocket=1" class="rocketlink">🚀 쫑배송</a>
  </div>
</nav>

<main class="wrap">
<?php if ($flash_msg = flash()): ?>
  <div class="flash <?= h($flash_msg[0]) ?>"><?= h($flash_msg[1]) ?></div>
<?php endif; ?>
