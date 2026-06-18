<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id      = (int)($_GET['id']    ?? 0);
$animeId = (int)($_GET['anime'] ?? 0);

if ($id) {
    $pdo->prepare("DELETE FROM episodes WHERE id=?")->execute([$id]);
}

header('Location: /test-antigravity/admin/episode-add.php?anime=' . $animeId . '&msg=deleted');
exit;
