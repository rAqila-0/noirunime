<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id    = (int)($_GET['id'] ?? 0);
$anime = getAnimeById($pdo, $id);

if ($anime) {
    $pdo->prepare("DELETE FROM anime WHERE id=?")->execute([$id]);
}

header('Location: /test-antigravity/admin/anime.php?msg=deleted');
exit;
