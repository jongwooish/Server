<?php
require __DIR__ . '/inc/app.php';

$p = q('SELECT * FROM products WHERE id = ?', [(int)($_GET['id'] ?? 0)])->fetch();
if (!$p) { http_response_code(404); $title = '상품 없음'; include __DIR__ . '/inc/header.php';
    echo '<div class="empty"><div class="big">📭</div><h2>상품을 찾을 수 없습니다</h2><a class="btn" href="/products.php">상품 둘러보기</a></div>';
    include __DIR__ . '/inc/footer.php'; exit; }

$sale    = sale_price($p);
$u       = user();
$member  = is_member($u);
$mprice  = (int)(floor($sale * (100 - MEMBER_DC) / 100 / 10) * 10);
$img_v   = q('SELECT updated_at FROM product_images WHERE product_id = ?', [$p['id']])->fetch()['updated_at'] ?? '';
$is_mine = $u && (int)$p['seller_id'] === (int)$u['id'];
$rsort   = in_array($_GET['rsort'] ?? '', ['new', 'low'], true) ? $_GET['rsort'] : 'best';
$order   = ['best' => 'helpful DESC, rating DESC', 'new' => 'created_at DESC', 'low' => 'rating ASC, created_at DESC'][$rsort];
$reviews = q("SELECT * FROM reviews WHERE product_id = ? ORDER BY $order LIMIT 20", [$p['id']])->fetchAll();
$dist    = [];
foreach (q('SELECT rating, COUNT(*) n FROM reviews WHERE product_id = ? GROUP BY rating', [$p['id']]) as $d) $dist[(int)$d['rating']] = (int)$d['n'];
$rv_total = array_sum($dist);
$related = q('SELECT * FROM products WHERE category = ? AND id <> ? ORDER BY reviews DESC LIMIT 5', [$p['category'], $p['id']])->fetchAll();

$title = $p['name'];
include __DIR__ . '/inc/header.php';
?>
<div class="crumb"><a href="/">홈</a> › <a href="/products.php?cat=<?= urlencode($p['category']) ?>"><?= h($p['category']) ?></a></div>

<section class="detail">
  <div class="dimg"><?= pimg((int)$p['id'], $p['name'], (string)($img_v ?? '')) ?></div>

  <div class="dinfo">
    <div class="dseller">
      <?= h($p['seller']) ?> <?= $p['seller'] === '쫑마켓' ? '<span class="official">공식</span>' : '' ?>
      <?php if ($is_mine): ?><a class="btn small ghost" href="/sell.php?edit=<?= (int)$p['id'] ?>">✏️ 내 상품 수정</a><?php endif; ?>
    </div>
    <h1><?= h($p['name']) ?></h1>
    <div class="drate"><?= stars((float)$p['rating']) ?> <b><?= h($p['rating']) ?></b> <a href="#review"><?= number_format((int)$p['reviews']) ?>개 상품평</a></div>

    <div class="dprice">
      <?php if ($p['discount'] > 0): ?>
        <div class="porig"><b><?= (int)$p['discount'] ?>%</b> <s><?= won((int)$p['price']) ?></s></div>
      <?php endif; ?>
      <div class="big"><?= won($sale) ?></div>
      <div class="member-price <?= $member ? 'on' : '' ?>">
        🏅 쫑클럽가 <b><?= won($mprice) ?></b>
        <?= $member ? '<span>회원 적용 중</span>' : '<a href="/membership.php">가입하고 받기 ›</a>' ?>
      </div>
    </div>

    <div class="dship">
      <?php if ($p['delivery'] === '쫑배송'): ?>
        <div class="badge rocket">🚀 쫑배송</div>
        <p><b class="green"><?= h(arrive_text('쫑배송')) ?></b> · 밤 12시 전 주문 시</p>
      <?php else: ?>
        <div class="badge <?= $p['delivery'] === '무료배송' ? 'free' : '' ?>"><?= h($p['delivery']) ?></div>
        <p><?= h(arrive_text($p['delivery'])) ?></p>
      <?php endif; ?>
      <p class="muted">
        <?= $p['delivery'] === '일반배송' ? '배송비 ' . won(SHIP_FEE) . ' (' . won(FREE_SHIP_MIN) . ' 이상 무료, 쫑클럽 무료)' : '무료배송' ?>
      </p>
    </div>

    <?php if ($p['stock'] > 0): ?>
      <form method="post" action="/cart.php" class="dbuy">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <label>수량
          <select name="qty"><?php for ($i = 1; $i <= min(10, (int)$p['stock']); $i++): ?><option><?= $i ?></option><?php endfor; ?></select>
        </label>
        <span class="muted">남은 수량 <?= (int)$p['stock'] ?>개</span>
        <div class="buttons">
          <button name="action" value="add" class="btn ghost">장바구니 담기</button>
          <button name="action" value="buy" class="btn">바로구매 ›</button>
        </div>
      </form>
    <?php else: ?>
      <div class="dbuy"><button class="btn" disabled>일시품절</button></div>
    <?php endif; ?>
  </div>
</section>

<section class="dtabs">
  <h2>상품 정보</h2>
  <table class="spec">
    <tr><th>판매자</th><td><?= h($p['seller']) ?></td><th>카테고리</th><td><?= h($p['category']) ?></td></tr>
    <tr><th>상품번호</th><td><?= (int)$p['id'] ?></td><th>등록일</th><td><?= h(substr((string)$p['created_at'], 0, 10)) ?></td></tr>
  </table>
  <p class="desc"><?= nl2br(h($p['description'])) ?></p>

  <h2 id="review">상품평</h2>
  <div class="rv-summary">
    <div class="rv-score">
      <b><?= h($p['rating']) ?></b>
      <?= stars((float)$p['rating']) ?>
      <span class="muted"><?= number_format((int)$p['reviews']) ?>개 상품평</span>
    </div>
    <div class="rv-bars">
      <?php foreach ([5, 4, 3, 2, 1] as $n): $pct = $rv_total ? round(($dist[$n] ?? 0) / $rv_total * 100) : 0; ?>
        <div><span><?= $n ?>점</span><i><em style="width:<?= $pct ?>%"></em></i><span><?= $pct ?>%</span></div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="rv-sort">
    <a href="?id=<?= (int)$p['id'] ?>&rsort=best#review" class="<?= $rsort === 'best' ? 'on' : '' ?>">베스트순</a>
    <a href="?id=<?= (int)$p['id'] ?>&rsort=new#review" class="<?= $rsort === 'new' ? 'on' : '' ?>">최신순</a>
    <a href="?id=<?= (int)$p['id'] ?>&rsort=low#review" class="<?= $rsort === 'low' ? 'on' : '' ?>">낮은 별점순</a>
  </div>

  <?php if (!$reviews): ?><p class="muted">아직 작성된 상품평이 없습니다.</p><?php endif; ?>
  <?php foreach ($reviews as $r): ?>
    <div class="review">
      <div class="rv-head">
        <span class="avatar"><?= h(mb_substr($r['author'], 0, 1)) ?></span>
        <div>
          <b><?= h($r['author']) ?></b> <?= $r['user_id'] ? '<span class="buyer">구매 인증</span>' : '' ?><br>
          <?= stars((float)$r['rating']) ?> <small><?= h(substr((string)$r['created_at'], 0, 10)) ?></small>
        </div>
      </div>
      <?php if ($r['option_txt']): ?><div class="rv-opt"><?= h($p['name']) ?>, <?= h($r['option_txt']) ?></div><?php endif; ?>
      <p><?= nl2br(h($r['body'])) ?></p>
      <div class="rv-help"><?= (int)$r['helpful'] ?>명에게 도움 됨</div>
    </div>
  <?php endforeach; ?>
  <p class="hint">실습용 쇼핑몰입니다. '구매 인증'이 없는 상품평은 실습을 위해 만든 가상 데이터입니다.</p>
</section>

<?php if ($related): ?>
<section class="shelf">
  <div class="shelf-head"><h2>같은 카테고리 인기 상품</h2></div>
  <div class="row"><?php foreach ($related as $r) echo product_card($r); ?></div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
