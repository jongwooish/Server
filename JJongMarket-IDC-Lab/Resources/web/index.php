<?php
require __DIR__ . '/inc/app.php';

$deals  = q('SELECT * FROM products WHERE stock > 0 ORDER BY discount DESC, reviews DESC LIMIT 10')->fetchAll();
$rocket = q("SELECT * FROM products WHERE delivery = '쫑배송' AND stock > 0 ORDER BY reviews DESC LIMIT 10")->fetchAll();
$fresh  = q('SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT 10')->fetchAll();
$maxdc  = (int)q("SELECT MAX(discount) m FROM products WHERE category = '전자기기' AND stock > 0")->fetch()['m'];
$best   = q('SELECT * FROM products WHERE reviews >= 100 ORDER BY rating DESC, reviews DESC LIMIT 5')->fetchAll();

$title = '';
include __DIR__ . '/inc/header.php';
?>
<section class="hero">
  <a class="banner b1" href="/products.php?cat=<?= urlencode('전자기기') ?>">
    <small>가을 데스크 셋업 기획전</small>
    <strong>키보드·마우스·모니터<br>최대 <?= $maxdc ?>% 할인</strong>
    <span>지금 주문하면 <?= h(arrive_text('쫑배송')) ?></span>
  </a>
  <div class="banner-side">
    <a class="banner b2" href="/products.php?cat=<?= urlencode('식품') ?>">
      <small>산지 직송</small><strong>제철 먹거리<br>모아보기</strong>
    </a>
    <a class="banner b3" href="/membership.php">
      <small>쫑클럽</small><strong>월 <?= number_format(MEMBER_FEE) ?>원<br>무료배송 + <?= MEMBER_DC ?>%</strong>
    </a>
  </div>
</section>

<a class="livebar" href="/live.php">
  <span class="live-pill">● LIVE</span>
  <b>쫑라이브 방송 중</b> · 쇼호스트 삐요가 소개하는 오늘의 특가 4종 <em>지금 보러 가기 ›</em>
</a>

<section class="catgrid">
  <?php foreach (CATEGORIES as $c => $ic): ?>
    <a href="/products.php?cat=<?= urlencode($c) ?>"><span><?= $ic ?></span><?= h($c) ?></a>
  <?php endforeach; ?>
</section>

<section class="shelf">
  <div class="shelf-head"><h2>⏰ 오늘의 특가</h2><a href="/products.php?sort=sale">더보기 ›</a></div>
  <div class="row"><?php foreach ($deals as $p) echo product_card($p); ?></div>
</section>

<section class="shelf">
  <div class="shelf-head"><h2>🚀 쫑배송 인기 상품 <small>밤 12시 전 주문 시 <?= h(arrive_text('쫑배송')) ?></small></h2><a href="/products.php?rocket=1">더보기 ›</a></div>
  <div class="row"><?php foreach ($rocket as $p) echo product_card($p); ?></div>
</section>

<section class="shelf two">
  <div>
    <div class="shelf-head"><h2>🏆 고객 만족 베스트</h2><a href="/products.php?sort=rating">더보기 ›</a></div>
    <ol class="ranking">
      <?php foreach ($best as $i => $p): ?>
        <li>
          <a href="/item.php?id=<?= (int)$p['id'] ?>">
            <b><?= $i + 1 ?></b><span class="ic"><?= pimg((int)$p['id'], $p['name']) ?></span>
            <span class="nm"><?= h($p['name']) ?><small><?= stars((float)$p['rating']) ?> <?= h($p['rating']) ?> (<?= number_format((int)$p['reviews']) ?>)</small></span>
            <em><?= won(sale_price($p)) ?></em>
          </a>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
  <div class="sellbox">
    <h2>📦 내 물건도 팔아 보세요</h2>
    <p>회원이면 누구나 판매자가 될 수 있어요.<br>상품을 등록하면 바로 검색과 목록에 노출됩니다.</p>
    <a class="btn" href="/sell.php">상품 등록하기</a>
  </div>
</section>

<section class="shelf">
  <div class="shelf-head"><h2>🆕 방금 등록된 상품</h2><a href="/products.php?sort=new">더보기 ›</a></div>
  <div class="row"><?php foreach ($fresh as $p) echo product_card($p); ?></div>
</section>

<!-- 광고 팝업 2개 — '오늘 하루 보지 않기'는 이 브라우저에만 기억 (localStorage) -->
<div class="popwrap" id="popwrap" hidden>
  <div class="popup" data-pop="club" hidden>
    <div class="pop-art pop-club">
      <span class="tag">JJONG CLUB</span>
      <span class="deco">🏅</span>
      <p><b>무료배송 + 추가 <?= MEMBER_DC ?>% 할인</b></p>
      <h2>쫑클럽<br>지금 시작하세요</h2>
      <div class="price"><?= number_format(MEMBER_FEE) ?>원<small style="color:rgba(0,0,0,.6)">/월</small></div>
    </div>
    <a class="btn pop-cta" href="/membership.php">혜택 보러 가기 ›</a>
    <div class="pop-foot"><button data-today>오늘 하루 보지 않기</button><button data-close>닫기 ✕</button></div>
  </div>

  <div class="popup" data-pop="sale" hidden>
    <div class="pop-art pop-sale">
      <span class="tag">TODAY ONLY</span>
      <span class="deco">⚡</span>
      <p>가을 데스크 셋업 · 생활 필수템</p>
      <h2>오늘의 특가<br>최대 <em>45%</em> 할인</h2>
      <div class="timer" id="saleTimer"><span>00</span>:<span>00</span>:<span>00</span></div>
    </div>
    <a class="btn pop-cta" href="/products.php?sort=sale">특가 상품 보기 ›</a>
    <div class="pop-foot"><button data-today>오늘 하루 보지 않기</button><button data-close>닫기 ✕</button></div>
  </div>
</div>

<script>
(function () {
  const wrap = document.getElementById('popwrap');
  const today = new Date().toISOString().slice(0, 10);
  const get = k => { try { return localStorage.getItem(k); } catch (e) { return null; } };
  const set = (k, v) => { try { localStorage.setItem(k, v); } catch (e) {} };

  const pops = [...wrap.querySelectorAll('.popup')];
  pops.forEach(p => { if (get('jjpop-' + p.dataset.pop) !== today) p.hidden = false; });
  const refresh = () => { wrap.hidden = pops.every(p => p.hidden); };
  setTimeout(refresh, 400);   // 첫 화면이 그려진 뒤 살짝 늦게 띄우기

  pops.forEach(p => {
    p.querySelector('[data-close]').onclick = () => { p.hidden = true; refresh(); };
    p.querySelector('[data-today]').onclick = () => { set('jjpop-' + p.dataset.pop, today); p.hidden = true; refresh(); };
  });
  wrap.addEventListener('click', e => { if (e.target === wrap) { pops.forEach(p => p.hidden = true); refresh(); } });

  // 자정까지 남은 시간
  const t = document.getElementById('saleTimer').children;
  function tick() {
    const n = new Date(), end = new Date(n); end.setHours(24, 0, 0, 0);
    let s = Math.floor((end - n) / 1000);
    [Math.floor(s / 3600), Math.floor(s % 3600 / 60), s % 60].forEach((v, i) => t[i].textContent = String(v).padStart(2, '0'));
  }
  tick(); setInterval(tick, 1000);
})();
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
