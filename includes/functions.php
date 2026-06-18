<?php
// includes/functions.php — Helper functions

function getAnimeList(PDO $pdo, array $opts = []): array {
    $where  = [];
    $params = [];

    if (!empty($opts['genre_slug'])) {
        $where[]  = 'EXISTS (SELECT 1 FROM anime_genres ag JOIN genres g ON ag.genre_id=g.id WHERE ag.anime_id=a.id AND g.slug=?)';
        $params[] = $opts['genre_slug'];
    }
    if (!empty($opts['status'])) {
        $where[]  = 'a.status = ?';
        $params[] = $opts['status'];
    }
    if (!empty($opts['search'])) {
        $where[]  = 'a.title LIKE ?';
        $params[] = '%' . $opts['search'] . '%';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $orderMap = [
        'latest'  => 'a.created_at DESC',
        'popular' => 'a.views DESC',
        'rating'  => 'a.rating DESC',
        'title'   => 'a.title ASC',
    ];
    $orderBy = $orderMap[$opts['sort'] ?? 'latest'] ?? 'a.created_at DESC';

    $limit  = (int)($opts['limit']  ?? 20);
    $offset = (int)($opts['offset'] ?? 0);

    $sql = "SELECT a.*, GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') AS genres
            FROM anime a
            LEFT JOIN anime_genres ag ON a.id = ag.anime_id
            LEFT JOIN genres g ON ag.genre_id = g.id
            $whereSQL
            GROUP BY a.id
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAnimeById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT a.*, GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') AS genres,
                            GROUP_CONCAT(g.slug ORDER BY g.name SEPARATOR ',') AS genre_slugs
                           FROM anime a
                           LEFT JOIN anime_genres ag ON a.id = ag.anime_id
                           LEFT JOIN genres g ON ag.genre_id = g.id
                           WHERE a.id = ? GROUP BY a.id");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getEpisodes(PDO $pdo, int $animeId): array {
    $stmt = $pdo->prepare("SELECT * FROM episodes WHERE anime_id = ? ORDER BY episode_number ASC");
    $stmt->execute([$animeId]);
    return $stmt->fetchAll();
}

function getEpisodeById(PDO $pdo, int $epId): ?array {
    $stmt = $pdo->prepare("SELECT e.*, a.title AS anime_title FROM episodes e JOIN anime a ON e.anime_id=a.id WHERE e.id=?");
    $stmt->execute([$epId]);
    return $stmt->fetch() ?: null;
}

function getGenres(PDO $pdo): array {
    return $pdo->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();
}

function getWatchHistory(PDO $pdo, int $userId, int $limit = 10): array {
    $stmt = $pdo->prepare("SELECT wh.*, a.title AS anime_title, a.thumbnail,
                            e.episode_number, e.title AS ep_title, e.duration
                           FROM watch_history wh
                           JOIN anime a ON wh.anime_id = a.id
                           JOIN episodes e ON wh.episode_id = e.id
                           WHERE wh.user_id = ?
                           ORDER BY wh.watched_at DESC
                           LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function getTotalWatchTime(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(e.duration), 0) AS total
                           FROM watch_history wh
                           JOIN episodes e ON wh.episode_id = e.id
                           WHERE wh.user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function formatDuration(int $seconds): string {
    if ($seconds < 60)   return "{$seconds} detik";
    if ($seconds < 3600) return round($seconds/60) . " menit";
    $h = floor($seconds/3600);
    $m = floor(($seconds % 3600) / 60);
    return "{$h} jam " . ($m > 0 ? "{$m} menit" : "");
}

function recordWatch(PDO $pdo, int $userId, int $animeId, int $epId, int $progress = 0): void {
    $stmt = $pdo->prepare("INSERT INTO watch_history (user_id, anime_id, episode_id, progress)
                           VALUES (?,?,?,?)
                           ON DUPLICATE KEY UPDATE progress=?, watched_at=NOW()");
    $stmt->execute([$userId, $animeId, $epId, $progress, $progress]);

    // Increment view count
    $pdo->prepare("UPDATE anime SET views = views + 1 WHERE id = ?")->execute([$animeId]);
}

function getEmbedUrl(string $url): string {
    // ── Google Drive ──────────────────────────────────────────────
    // Format: https://drive.google.com/file/d/FILE_ID/view...
    //      → https://drive.google.com/file/d/FILE_ID/preview
    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $m)) {
        return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
    }
    // Format: https://drive.google.com/open?id=FILE_ID
    if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $url, $m)) {
        return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
    }
    // Sudah dalam format /preview → langsung pakai
    if (str_contains($url, 'drive.google.com') && str_contains($url, '/preview')) {
        return $url;
    }

    // ── YouTube ───────────────────────────────────────────────────
    // Sudah embed → langsung pakai
    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $m)) return $url;
    // watch?v=ID
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $m)) return 'https://www.youtube.com/embed/' . $m[1];
    // youtu.be/ID
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m)) return 'https://www.youtube.com/embed/' . $m[1];

    // Kembalikan URL asli jika tidak dikenali
    return $url;
}

// Alias lama agar kompatibel dengan kode yang sudah ada
function youtubeEmbed(string $url): string {
    return getEmbedUrl($url);
}

// Deteksi jenis sumber video
function getVideoSource(string $url): string {
    if (str_contains($url, 'drive.google.com')) return 'googledrive';
    if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) return 'youtube';
    return 'other';
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function avatarUrl(?string $avatar): string {
    if ($avatar && file_exists(__DIR__ . '/../uploads/avatars/' . $avatar)) {
        return '/test-antigravity/uploads/avatars/' . urlencode($avatar);
    }
    return 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($avatar ?? 'default');
}

function getStats(PDO $pdo): array {
    return [
        'anime'    => $pdo->query("SELECT COUNT(*) FROM anime")->fetchColumn(),
        'users'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
        'episodes' => $pdo->query("SELECT COUNT(*) FROM episodes")->fetchColumn(),
        'views'    => $pdo->query("SELECT COALESCE(SUM(views),0) FROM anime")->fetchColumn(),
    ];
}
