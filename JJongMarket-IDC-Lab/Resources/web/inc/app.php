<?php
// =====================================================================
//  쫑마켓 공통 — DB 연결, DB 세션, 로그인, 장바구니 계산, 화면 도우미
//  모든 페이지 맨 위에서 require __DIR__ . '/inc/app.php';
// =====================================================================
declare(strict_types=1);
date_default_timezone_set('Asia/Seoul');

// php-mbstring 이 없는 서버에서도 한글 글자 수 계산이 되도록 최소한의 대체 함수
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $s, ?string $enc = null): int
    {
        return (int)preg_match_all('/./us', $s);
    }
    function mb_substr(string $s, int $start, ?int $len = null, ?string $enc = null): string
    {
        preg_match_all('/./us', $s, $m);
        return implode('', array_slice($m[0], $start, $len));
    }
}

const SHIP_FEE      = 3000;   // 기본 배송비
const FREE_SHIP_MIN = 19800;  // 이 금액 이상이면 무료배송
const MEMBER_FEE    = 4990;   // 쫑클럽 월 회비
const MEMBER_DC     = 5;      // 쫑클럽 추가 할인 %

const CATEGORIES = [
    '전자기기' => '💻', '생활용품' => '🧺', '패션의류' => '👕', '식품' => '🍎',
    '뷰티'     => '💄', '스포츠'   => '⚽', '도서'     => '📚', '반려동물' => '🐶',
];
const DELIVERIES = ['쫑배송', '무료배송', '일반배송'];

// 쫑라이브 방송 상품 (영상에 나오는 순서) — 무선 핸디 청소기, 원터치 텐트, 한돈 삼겹살, 27인치 모니터
const LIVE_PRODUCTS = [33, 115, 70, 3];
const LIVE_HLS      = '/hls/stream.m3u8';   // WEB1·WEB2 각자의 ffmpeg(jjonglive.service)가 만든 HLS

// 가상 배송 — 주문 후 몇 분이 지나면 다음 단계로 넘어가는지 (실습에서 바로 볼 수 있게 짧게)
const STAGES = [
    ['결제완료',   0, '주문이 접수되고 결제가 완료되었습니다.'],
    ['상품준비중', 1, '쫑마켓 물류센터에서 상품을 포장하고 있습니다.'],
    ['배송시작',   3, '상품이 물류센터를 출발했습니다.'],
    ['배송중',     5, '배송 기사님이 고객님 지역으로 이동 중입니다.'],
    ['배송완료',   8, '고객님이 요청하신 장소에 배송을 완료했습니다.'],
];
const DELIVERY_MEMOS = ['문 앞에 놓아주세요', '경비실에 맡겨주세요', '택배함에 넣어주세요', '직접 받을게요', '배송 전 연락 주세요'];

function now(): string { return date('Y-m-d H:i:s'); }

// 주문의 현재 배송 단계 — ['index' => 0~4, 'label' => ..., 'times' => [단계별 시각 또는 null]]
function order_stage(array $o): array
{
    $start = strtotime((string)$o['created_at']);
    $min   = (time() - $start) / 60;
    $idx   = 0;
    $times = [];
    foreach (STAGES as $i => [$label, $after]) {
        $times[$i] = date('m/d H:i', $start + $after * 60);
        if ($min >= $after) $idx = $i;
    }
    if ($o['status'] === '구매확정') $idx = 4;
    $label = $o['status'] === '주문취소' ? '주문취소' : ($o['status'] === '구매확정' ? '구매확정' : STAGES[$idx][0]);
    return ['index' => $idx, 'label' => $label, 'times' => $times, 'canceled' => $o['status'] === '주문취소'];
}

// ---------------------------------------------------------------------
//  DB 연결 — 접속 정보는 웹 루트 밖 /etc/jjongmarket/db.ini
// ---------------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $ini = getenv('JJONG_DB_INI') ?: '/etc/jjongmarket/db.ini';
    $c = @parse_ini_file($ini);
    if ($c === false) throw new RuntimeException('db.ini를 읽을 수 없습니다.');

    if (($c['driver'] ?? 'mysql') === 'sqlite') {            // 로컬 테스트용
        $pdo = new PDO('sqlite:' . $c['path']);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = "mysql:host={$c['host']};dbname={$c['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['user'], $c['password'], [PDO::ATTR_TIMEOUT => 3]);
        $pdo->exec("SET time_zone = '+09:00'");
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function q(string $sql, array $args = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st;
}

// DB가 죽으면 페이지 대신 안내 화면 (keepalived용 health.html은 정적 파일이라 영향 없음)
set_exception_handler(function (Throwable $e) {
    error_log('[jjongmarket] ' . $e->getMessage());
    http_response_code(503);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="/css/style.css">'
       . '<div class="wrap"><div class="empty"><div class="big">🛠️</div>'
       . '<h2>잠시 후 다시 시도해 주세요</h2><p>상품 정보를 불러오지 못했습니다.</p>'
       . '<a class="btn" href="/">홈으로</a></div></div>';
});

// ---------------------------------------------------------------------
//  세션을 DB에 저장 — WEB1이 죽어 WEB2로 넘어가도 로그인·장바구니 유지
// ---------------------------------------------------------------------
class DbSession implements SessionHandlerInterface
{
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $id): string|false
    {
        $row = q('SELECT data FROM sessions WHERE id = ?', [$id])->fetch();
        return $row ? $row['data'] : '';
    }
    public function write(string $id, string $data): bool
    {
        q('REPLACE INTO sessions (id, data, updated_at) VALUES (?, ?, ?)', [$id, $data, time()]);
        return true;
    }
    public function destroy(string $id): bool
    {
        q('DELETE FROM sessions WHERE id = ?', [$id]);
        return true;
    }
    public function gc(int $max): int|false
    {
        return q('DELETE FROM sessions WHERE updated_at < ?', [time() - $max])->rowCount();
    }
}

if (!defined('NO_SESSION')) {
    session_set_save_handler(new DbSession(), true);
    session_name('JJONGSESS');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}

// ---------------------------------------------------------------------
//  화면 도우미
// ---------------------------------------------------------------------
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function won(int $n): string { return number_format($n) . '원'; }
function sale_price(array $p): int { return (int)(floor($p['price'] * (100 - $p['discount']) / 100 / 10) * 10); }

function redirect(string $url): never { header('Location: ' . $url); exit; }

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) { $_SESSION['flash'] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function stars(float $r): string
{
    $full = (int)round($r);
    return '<span class="stars">' . str_repeat('★', $full) . '<i>' . str_repeat('★', 5 - $full) . '</i></span>';
}

function arrive_text(string $delivery): string
{
    $days = ['일', '월', '화', '수', '목', '금', '토'];
    $add = $delivery === '쫑배송' ? 1 : 3;
    $t = strtotime("+{$add} day");
    $label = $add === 1 ? '내일' : date('n/j', $t);
    return $label . '(' . $days[(int)date('w', $t)] . ') 도착' . ($add === 1 ? ' 보장' : ' 예정');
}

// ---------------------------------------------------------------------
//  CSRF — 모든 POST 폼에 csrf_field(), 처리 전에 csrf_check()
// ---------------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void
{
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('잘못된 요청입니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
    }
}

// ---------------------------------------------------------------------
//  회원
// ---------------------------------------------------------------------
function user(): ?array
{
    static $u = false;
    if ($u === false) {
        $u = empty($_SESSION['uid']) ? null
           : (q('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']])->fetch() ?: null);
    }
    return $u;
}

function require_login(): array
{
    $u = user();
    if (!$u) {
        flash('로그인이 필요합니다.', 'warn');
        redirect('/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
    }
    return $u;
}

function is_member(?array $u): bool
{
    return $u && $u['membership_until'] && $u['membership_until'] >= date('Y-m-d');
}

function safe_next(string $next): string
{
    return (str_starts_with($next, '/') && !str_starts_with($next, '//')) ? $next : '/';
}

// ---------------------------------------------------------------------
//  장바구니 계산 — 상품 할인 → 쫑클럽 추가 할인 → 배송비
// ---------------------------------------------------------------------
function cart_count(): int
{
    $u = user();
    if (!$u) return 0;
    return (int)q('SELECT COALESCE(SUM(qty),0) n FROM cart_items WHERE user_id = ?', [$u['id']])->fetch()['n'];
}

function cart_summary(array $u): array
{
    $items = q('SELECT c.qty, p.* FROM cart_items c JOIN products p ON p.id = c.product_id
                WHERE c.user_id = ? ORDER BY p.delivery, p.id', [$u['id']])->fetchAll();
    $list = 0; $items_total = 0;
    foreach ($items as &$it) {
        $it['sale'] = sale_price($it);
        $it['line'] = $it['sale'] * $it['qty'];
        $list        += $it['price'] * $it['qty'];
        $items_total += $it['line'];
    }
    unset($it);
    $member    = is_member($u);
    $member_dc = $member ? (int)(floor($items_total * MEMBER_DC / 100 / 10) * 10) : 0;
    $shipping  = (!$items || $member || $items_total >= FREE_SHIP_MIN) ? 0 : SHIP_FEE;
    return [
        'items'       => $items,
        'list'        => $list,
        'product_dc'  => $list - $items_total,
        'items_total' => $items_total,
        'member_dc'   => $member_dc,
        'shipping'    => $shipping,
        'total'       => $items_total - $member_dc + $shipping,
        'member'      => $member,
    ];
}

// 상품 이미지 태그 — 사진이 바뀌면 v 값이 바뀌어 브라우저 캐시를 새로 받음
function pimg(int $id, string $alt = '', string $v = ''): string
{
    return '<img src="/img.php?id=' . $id . ($v !== '' ? '&v=' . urlencode($v) : '') . '" alt="' . h($alt) . '" loading="lazy">';
}

// ---------------------------------------------------------------------
//  상품 카드 (목록 공통)
// ---------------------------------------------------------------------
function product_card(array $p): string
{
    $sale = sale_price($p);
    $soldout = $p['stock'] <= 0;
    $html  = '<a class="pcard' . ($soldout ? ' soldout' : '') . '" href="/item.php?id=' . (int)$p['id'] . '">';
    $html .= '<div class="pthumb">' . pimg((int)$p['id'], $p['name']) . ($soldout ? '<span class="sold">일시품절</span>' : '') . '</div>';
    $html .= '<div class="pbody"><div class="pname">' . h($p['name']) . '</div>';
    if ($p['discount'] > 0) {
        $html .= '<div class="porig"><b>' . (int)$p['discount'] . '%</b> <s>' . won((int)$p['price']) . '</s></div>';
    }
    $html .= '<div class="psale">' . won($sale) . '</div>';
    if ($p['delivery'] === '쫑배송') {
        $html .= '<div class="badge rocket">🚀 쫑배송 · ' . h(arrive_text('쫑배송')) . '</div>';
    } elseif ($p['delivery'] === '무료배송') {
        $html .= '<div class="badge free">무료배송</div>';
    }
    if ($p['reviews'] > 0) {
        $html .= '<div class="prate">' . stars((float)$p['rating']) . ' <span>(' . number_format((int)$p['reviews']) . ')</span></div>';
    }
    $html .= '</div></a>';
    return $html;
}
