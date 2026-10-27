<?php
// =====================================================================
//  상품 이미지 — /img.php?id=상품번호
//   1) 판매자가 올린 사진이 product_images 에 있으면 그 사진
//   2) 없으면 카테고리 색 배경 + 상품 아이콘으로 기본 이미지(SVG)를 그려서 응답
//  사진이 DB에 있으므로 WEB1/WEB2 어느 서버가 응답해도 같은 이미지가 나온다.
// =====================================================================
const NO_SESSION = true;            // 이미지 요청마다 세션을 만들 필요 없음
require __DIR__ . '/inc/app.php';

$id = (int)($_GET['id'] ?? 0);
header('Cache-Control: public, max-age=300');

$img = q('SELECT mime, data, updated_at FROM product_images WHERE product_id = ?', [$id])->fetch();
if ($img) {
    header('Content-Type: ' . $img['mime']);
    header('ETag: "' . md5($id . $img['updated_at']) . '"');
    echo $img['data'];
    exit;
}

$p = q('SELECT icon, category FROM products WHERE id = ?', [$id])->fetch() ?: ['icon' => '📦', 'category' => ''];
$palette = [
    '전자기기' => ['#e7f0ff', '#c9dcff'], '생활용품' => ['#e9f8ef', '#c8ecd6'], '패션의류' => ['#fdeef4', '#f6cfdf'],
    '식품'     => ['#fff5df', '#ffe2a8'], '뷰티'     => ['#f6edfd', '#e2cff6'], '스포츠'   => ['#e6f7f7', '#bfe9e8'],
    '도서'     => ['#f3efe8', '#e2d6c3'], '반려동물' => ['#fff0e6', '#ffd5bb'],
];
[$c1, $c2] = $palette[$p['category']] ?? ['#f1f3f5', '#dee2e6'];
$icon = htmlspecialchars($p['icon'], ENT_XML1, 'UTF-8');

header('Content-Type: image/svg+xml; charset=utf-8');
echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="$c1"/><stop offset="1" stop-color="$c2"/></linearGradient>
    <radialGradient id="glow" cx="0.5" cy="0.42" r="0.45"><stop offset="0" stop-color="#ffffff" stop-opacity="0.85"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/></radialGradient>
  </defs>
  <rect width="600" height="600" fill="url(#bg)"/>
  <circle cx="300" cy="260" r="230" fill="url(#glow)"/>
  <ellipse cx="300" cy="470" rx="150" ry="22" fill="#000" opacity="0.08"/>
  <text x="300" y="365" font-size="250" text-anchor="middle" font-family="Noto Color Emoji, Apple Color Emoji, Segoe UI Emoji, sans-serif">$icon</text>
</svg>
SVG;
