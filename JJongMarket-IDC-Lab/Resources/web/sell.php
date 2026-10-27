<?php
// 상품 등록 / 수정 (판매자) — 사진은 DB(product_images)에 저장
require __DIR__ . '/inc/app.php';
$u = require_login();

$icons = ['📦','💻','⌨️','🖱️','🎧','📱','🔋','🧺','🧴','🛏️','💡','👕','👖','👟','🎒','🍎','🍚','☕','🍪','💄','🧖','⚽','🏋️','🏕️','📚','✏️','🐶','🐱','🎁','🪴'];
const MAX_IMG = 2 * 1024 * 1024;   // 2MB (PHP 기본 upload_max_filesize 와 같음)
const IMG_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

// 수정 모드 — 내가 등록한 상품만
$edit = null;
if ($eid = (int)($_GET['edit'] ?? $_POST['edit'] ?? 0)) {
    $edit = q('SELECT * FROM products WHERE id = ? AND seller_id = ?', [$eid, $u['id']])->fetch();
    if (!$edit) { flash('수정할 수 있는 상품이 아닙니다.', 'warn'); redirect('/mypage.php'); }
}

$old = ['name' => '', 'category' => '', 'icon' => '📦', 'price' => '', 'discount' => '0', 'stock' => '10', 'delivery' => '무료배송', 'description' => ''];
if ($edit) foreach ($old as $k => $v) $old[$k] = (string)$edit[$k];
$errors = [];

// 업로드 사진 검사 — 실제 이미지인지 파일 내용으로 확인
function read_upload(array &$errors): ?array
{
    $f = $_FILES['photo'] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > MAX_IMG) {
        $errors[] = '사진은 2MB 이하로 올려 주세요.'; return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = '사진 업로드에 실패했습니다.'; return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(IMG_TYPES[$mime]) || !@getimagesize($f['tmp_name'])) {
        $errors[] = 'JPG, PNG, WEBP, GIF 사진만 올릴 수 있습니다.'; return null;
    }
    return ['mime' => $mime, 'data' => file_get_contents($f['tmp_name'])];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (($_POST['action'] ?? '') === 'delete_photo' && $edit) {
        q('DELETE FROM product_images WHERE product_id = ?', [$edit['id']]);
        flash('사진을 삭제했습니다. 기본 이미지로 표시됩니다.');
        redirect('/sell.php?edit=' . $edit['id']);
    }

    $old = array_merge($old, array_map(fn($v) => trim((string)$v), array_intersect_key($_POST, $old)));
    $price = (int)$old['price']; $dc = (int)$old['discount']; $stock = (int)$old['stock'];

    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) $errors[] = '상품명은 2~100자로 입력해 주세요.';
    if (!isset(CATEGORIES[$old['category']]))                          $errors[] = '카테고리를 선택해 주세요.';
    if (!in_array($old['icon'], $icons, true))                          $errors[] = '대표 아이콘을 선택해 주세요.';
    if ($price < 100 || $price > 10000000)                              $errors[] = '정가는 100원 ~ 1,000만원 사이로 입력해 주세요.';
    if ($dc < 0 || $dc > 90)                                            $errors[] = '할인율은 0~90% 사이로 입력해 주세요.';
    if ($stock < 0 || $stock > 9999)                                    $errors[] = '재고는 0~9999개 사이로 입력해 주세요.';
    if (!in_array($old['delivery'], ['무료배송', '일반배송'], true))     $errors[] = '배송 방식을 선택해 주세요.';
    if (mb_strlen($old['description']) < 10)                            $errors[] = '상품 설명을 10자 이상 입력해 주세요.';
    $photo = read_upload($errors);

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        if ($edit) {
            q('UPDATE products SET name = ?, category = ?, icon = ?, price = ?, discount = ?, stock = ?, delivery = ?, description = ?
               WHERE id = ? AND seller_id = ?',
              [$old['name'], $old['category'], $old['icon'], $price, $dc, $stock, $old['delivery'], $old['description'], $edit['id'], $u['id']]);
            $id = (int)$edit['id'];
        } else {
            q('INSERT INTO products (seller_id, seller, name, category, icon, price, discount, stock, delivery, description, created_at)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
              [$u['id'], $u['name'], $old['name'], $old['category'], $old['icon'], $price, $dc, $stock, $old['delivery'], $old['description'], now()]);
            $id = (int)$pdo->lastInsertId();
        }
        if ($photo) {
            q('DELETE FROM product_images WHERE product_id = ?', [$id]);
            q('INSERT INTO product_images (product_id, mime, data, updated_at) VALUES (?, ?, ?, ?)', [$id, $photo['mime'], $photo['data'], now()]);
        }
        $pdo->commit();
        flash($edit ? '상품 정보를 수정했습니다.' : '상품을 등록했습니다. 지금부터 검색과 목록에 노출됩니다.');
        redirect('/item.php?id=' . $id);
    }
}

$img_v = $edit ? (q('SELECT updated_at FROM product_images WHERE product_id = ?', [$edit['id']])->fetch()['updated_at'] ?? null) : null;
$title = $edit ? '상품 수정' : '상품 등록';
include __DIR__ . '/inc/header.php';
?>
<div class="mylayout">
  <?php $mytab = 'sell'; include __DIR__ . '/inc/mymenu.php'; ?>
  <section>
    <h1 class="page"><?= $edit ? '✏️ 상품 수정' : '📤 상품 등록' ?> <small>판매자: <?= h($u['name']) ?></small></h1>

    <?php if ($errors): ?>
      <div class="flash warn"><?= implode('<br>', array_map('h', $errors)) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="box form sellform">
      <?= csrf_field() ?>
      <?php if ($edit): ?><input type="hidden" name="edit" value="<?= (int)$edit['id'] ?>"><?php endif; ?>

      <div class="photo-row">
        <div class="photo-preview" id="photoPreview">
          <?php if ($edit): ?><?= pimg((int)$edit['id'], $edit['name'], (string)$img_v) ?><?php else: ?><span>📷</span><?php endif; ?>
        </div>
        <div class="field">상품 사진 <small>(JPG·PNG·WEBP·GIF, 2MB 이하)</small>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" id="photoInput">
          <span class="hint">사진을 올리지 않으면 카테고리 색과 아래 아이콘으로 기본 이미지를 만들어 줍니다.</span>
          <?php if ($img_v): ?>
            <button class="btn small ghost" name="action" value="delete_photo" formnovalidate>올린 사진 삭제</button>
          <?php endif; ?>
        </div>
      </div>

      <label class="field">상품명<input name="name" value="<?= h($old['name']) ?>" maxlength="100" placeholder="예) 무선 충전 거치대 15W" required></label>

      <div class="inline">
        <label class="field">카테고리
          <select name="category" required>
            <option value="">선택</option>
            <?php foreach (CATEGORIES as $c => $ic): ?>
              <option value="<?= h($c) ?>" <?= $old['category'] === $c ? 'selected' : '' ?>><?= $ic ?> <?= h($c) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">배송
          <select name="delivery">
            <?php foreach (['무료배송', '일반배송'] as $d): ?>
              <option <?= $old['delivery'] === $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <div class="field">대표 아이콘 <small>(사진이 없을 때 기본 이미지에 쓰여요)</small>
        <div class="iconpick">
          <?php foreach ($icons as $ic): ?>
            <label><input type="radio" name="icon" value="<?= h($ic) ?>" <?= $old['icon'] === $ic ? 'checked' : '' ?>><span><?= $ic ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="inline">
        <label class="field">정가(원)<input name="price" type="number" min="100" step="10" value="<?= h($old['price']) ?>" required></label>
        <label class="field">할인율(%)<input name="discount" type="number" min="0" max="90" value="<?= h($old['discount']) ?>"></label>
        <label class="field">재고(개)<input name="stock" type="number" min="0" max="9999" value="<?= h($old['stock']) ?>"></label>
      </div>
      <p class="hint" id="preview"></p>

      <label class="field">상품 설명<textarea name="description" rows="5" placeholder="상품의 특징, 크기, 구성품 등을 적어 주세요." required><?= h($old['description']) ?></textarea></label>

      <button class="btn"><?= $edit ? '수정 내용 저장' : '상품 등록하기' ?></button>
      <p class="hint">🚀 쫑배송은 쫑마켓이 직접 매입한 상품에만 붙습니다. 판매자 상품은 무료배송 또는 일반배송으로 등록돼요.</p>
    </form>
  </section>
</div>
<script>
  // 판매가 미리보기, 사진 미리보기
  const f = document.querySelector('.sellform');
  const pv = document.getElementById('preview');
  function upd() {
    const p = +f.price.value || 0, d = +f.discount.value || 0;
    const s = Math.floor(p * (100 - d) / 100 / 10) * 10;
    pv.textContent = p ? `판매가 ${s.toLocaleString()}원으로 노출됩니다.` : '';
  }
  f.addEventListener('input', upd); upd();
  document.getElementById('photoInput').addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    const box = document.getElementById('photoPreview');
    box.innerHTML = ''; box.appendChild(img);
  });
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
