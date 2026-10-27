<?php
require __DIR__ . '/inc/app.php';

$qv     = trim($_GET['q'] ?? '');
$cat    = $_GET['cat'] ?? '';
$rocket = !empty($_GET['rocket']);
$instock= !empty($_GET['instock']);
$sort   = $_GET['sort'] ?? 'rank';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 20;

$sorts = [
    'rank'   => ['쫑마켓 랭킹순', 'reviews DESC, rating DESC'],
    'low'    => ['낮은가격순',   '(price * (100 - discount)) ASC'],
    'high'   => ['높은가격순',   '(price * (100 - discount)) DESC'],
    'sale'   => ['할인율순',     'discount DESC, reviews DESC'],
    'rating' => ['평점높은순',   'rating DESC, reviews DESC'],
    'new'    => ['최신순',       'created_at DESC, id DESC'],
];
if (!isset($sorts[$sort])) $sort = 'rank';

$where = ['1=1']; $args = [];
if ($qv !== '') {
    $where[] = '(name LIKE ? OR description LIKE ? OR seller LIKE ?)';
    array_push($args, "%$qv%", "%$qv%", "%$qv%");
}
if (isset(CATEGORIES[$cat])) { $where[] = 'category = ?'; $args[] = $cat; } else { $cat = ''; }
if ($rocket)  $where[] = "delivery = '쫑배송'";
if ($instock) $where[] = 'stock > 0';
$w = implode(' AND ', $where);

$total = (int)q("SELECT COUNT(*) n FROM products WHERE $w", $args)->fetch()['n'];
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);
$off   = ($page - 1) * $per;
$rows  = q("SELECT * FROM products WHERE $w ORDER BY {$sorts[$sort][1]} LIMIT $per OFFSET $off", $args)->fetchAll();

// 현재 조건을 유지한 채 하나만 바꾼 링크
function link_with(array $change): string
{
    $p = array_merge($_GET, $change);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 0);
    return '/products.php' . ($p ? '?' . http_build_query($p) : '');
}

$title = $qv !== '' ? "'$qv' 검색 결과" : ($cat ?: '전체 상품');
include __DIR__ . '/inc/header.php';
?>
<div class="listing">
  <aside class="filters">
    <h3>카테고리</h3>
    <a href="<?= h(link_with(['cat' => '', 'page' => ''])) ?>" class="<?= $cat === '' ? 'on' : '' ?>">전체</a>
    <?php foreach (CATEGORIES as $c => $ic): ?>
      <a href="<?= h(link_with(['cat' => $c, 'page' => ''])) ?>" class="<?= $cat === $c ? 'on' : '' ?>"><?= $ic ?> <?= h($c) ?></a>
    <?php endforeach; ?>
    <h3>배송 / 재고</h3>
    <a href="<?= h(link_with(['rocket' => $rocket ? '' : 1, 'page' => ''])) ?>" class="check <?= $rocket ? 'on' : '' ?>">🚀 쫑배송만</a>
    <a href="<?= h(link_with(['instock' => $instock ? '' : 1, 'page' => ''])) ?>" class="check <?= $instock ? 'on' : '' ?>">품절 상품 제외</a>
  </aside>

  <section class="results">
    <div class="results-head">
      <h1><?= $qv !== '' ? "'" . h($qv) . "'" : h($cat ?: '전체 상품') ?> <small><?= number_format($total) ?>개</small></h1>
      <div class="sorts">
        <?php foreach ($sorts as $k => [$label]): ?>
          <a href="<?= h(link_with(['sort' => $k, 'page' => ''])) ?>" class="<?= $sort === $k ? 'on' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!$rows): ?>
      <div class="empty"><div class="big">🔎</div><h2>검색 결과가 없습니다</h2><p>다른 검색어를 입력하거나 필터를 해제해 보세요.</p></div>
    <?php else: ?>
      <div class="grid"><?php foreach ($rows as $p) echo product_card($p); ?></div>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
      <div class="pager">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="<?= h(link_with(['page' => $i])) ?>" class="<?= $i === $page ? 'on' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
