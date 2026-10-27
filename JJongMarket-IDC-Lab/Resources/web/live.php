<?php
// 쫑라이브 — 라이브 커머스 방송 + 방송 중 상품 바로 구매
require __DIR__ . '/inc/app.php';

$in    = implode(',', array_fill(0, count(LIVE_PRODUCTS), '?'));
$rows  = q("SELECT * FROM products WHERE id IN ($in)", LIVE_PRODUCTS)->fetchAll();
$byId  = array_column($rows, null, 'id');
$items = array_values(array_filter(array_map(fn($id) => $byId[$id] ?? null, LIVE_PRODUCTS)));

$title = '쫑라이브';
include __DIR__ . '/inc/header.php';
?>
<section class="live-wrap">
  <div class="live-main">
    <div class="player">
      <video id="player" muted autoplay playsinline controls></video>
      <div class="player-badge"><span class="live-pill">● LIVE</span> 쫑라이브 · 오늘의 특가 생방송</div>
      <div class="offline" id="offline" hidden>
        <div class="big">📺</div>
        <h2>방송 준비 중이에요</h2>
        <p>잠시 후 다시 시도해 주세요. 아래 특가 상품은 지금도 구매할 수 있어요.</p>
        <button class="btn small" onclick="startPlayer()">다시 연결</button>
      </div>
    </div>
    <div class="live-info">
      <h1>🔴 쫑라이브 — 오늘의 특가 생방송</h1>
      <p class="muted">쇼호스트 삐요가 소개하는 오늘의 특가! 방송 속 상품을 오른쪽에서 바로 담아 보세요. 소리는 플레이어에서 켤 수 있어요.</p>
    </div>
  </div>

  <aside class="live-side">
    <h2>방송 중 상품 <small><?= count($items) ?>개</small></h2>
    <?php foreach ($items as $i => $p): ?>
      <div class="live-item">
        <a href="/item.php?id=<?= (int)$p['id'] ?>" class="li-img"><?= pimg((int)$p['id'], $p['name']) ?><b><?= $i + 1 ?></b></a>
        <div class="li-body">
          <a href="/item.php?id=<?= (int)$p['id'] ?>" class="li-name"><?= h($p['name']) ?></a>
          <div><span class="red"><b><?= (int)$p['discount'] ?>%</b></span> <b class="li-price"><?= won(sale_price($p)) ?></b></div>
          <?php if ($p['delivery'] === '쫑배송'): ?><div class="badge rocket">🚀 <?= h(arrive_text('쫑배송')) ?></div><?php endif; ?>
          <?php if ($p['stock'] > 0): ?>
            <form method="post" action="/cart.php" class="li-buy">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="qty" value="1">
              <button name="action" value="add" class="btn small ghost">담기</button>
              <button name="action" value="buy" class="btn small">바로구매</button>
            </form>
          <?php else: ?>
            <div class="muted">일시품절</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </aside>
</section>

<script src="/js/hls.min.js"></script>
<script>
  // HLS 재생 — Firefox/Chrome 은 hls.js, Safari 는 기본 플레이어
  const SRC = <?= json_encode(LIVE_HLS) ?>;
  const video = document.getElementById('player');
  const offline = document.getElementById('offline');
  let hls;
  function startPlayer() {
    offline.hidden = true;
    if (window.Hls && Hls.isSupported()) {
      if (hls) hls.destroy();
      hls = new Hls({ liveSyncDurationCount: 3 });
      hls.loadSource(SRC);
      hls.attachMedia(video);
      hls.on(Hls.Events.MANIFEST_PARSED, () => video.play().catch(() => {}));
      hls.on(Hls.Events.ERROR, (e, d) => { if (d.fatal) { offline.hidden = false; setTimeout(startPlayer, 5000); } });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = SRC;
      video.addEventListener('error', () => offline.hidden = false, { once: true });
    } else {
      offline.hidden = false;
    }
  }
  startPlayer();
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
