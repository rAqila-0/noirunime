<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$results = $q ? getAnimeList($pdo, ['search' => $q, 'sort' => 'popular', 'limit' => 24]) : [];

$pageTitle = $q ? "Hasil: \"$q\"" : 'Cari Anime';
$metaDesc  = "Hasil pencarian anime untuk \"$q\" di AnimeStream.";
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Search Bar Large -->
    <div class="max-w-2xl mx-auto mb-10">
        <form action="/test-antigravity/search.php" method="GET" class="relative">
            <input type="text" name="q" value="<?= h($q) ?>" id="main-search"
                   placeholder="Cari judul anime..."
                   class="form-input text-lg py-4 pl-6 pr-14 rounded-2xl">
            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>
    </div>

    <?php if ($q): ?>
    <div class="mb-6">
        <h1 class="text-2xl font-black text-white">
            <?= $results ? count($results) . ' hasil untuk' : 'Tidak ada hasil untuk' ?>
            <span class="text-primary-light">"<?= h($q) ?>"</span>
        </h1>
    </div>

    <?php if ($results): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach ($results as $anime): ?>
        <a href="/test-antigravity/watch.php?anime=<?= $anime['id'] ?>" class="anime-card group">
            <div class="thumbnail-wrapper">
                <img src="<?= h($anime['thumbnail']) ?>" alt="<?= h($anime['title']) ?>" loading="lazy">
                <div class="overlay"></div>
                <div class="play-btn">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <h3 class="font-semibold text-xs text-white truncate mb-1"><?= h($anime['title']) ?></h3>
                <p class="text-xs text-gray-500"><?= h($anime['year']) ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-20">
        <div class="text-6xl mb-4">🔍</div>
        <h2 class="text-xl font-bold text-white mb-2">Anime tidak ditemukan</h2>
        <p class="text-gray-500 text-sm mb-6">Coba kata kunci yang berbeda.</p>
        <a href="/test-antigravity/anime-list.php" class="btn-primary">Lihat Semua Anime</a>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center py-16">
        <div class="text-6xl mb-4">🎌</div>
        <p class="text-gray-500">Ketik judul anime di atas untuk mencari.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
