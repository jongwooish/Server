<?php
require __DIR__ . '/inc/app.php';
if (hash_equals(csrf_token(), (string)($_GET['t'] ?? ''))) {
    $_SESSION = [];
    session_destroy();
}
header('Location: /');
