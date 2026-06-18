<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$slug   = $_GET['slug'] ?? '';
$genres = getGenres($pdo);

// Find current genre
$currentGenre = null;
foreach ($genres as $g) {
    if ($g['slug'] === $slug) { $currentGenre = $g; break; }
}

$animeList = $slug ? getAnimeList($pdo, ['genre_slug' => $slug, 'sort' => 'popular', 'limit' => 40]) : [];
$pageTitle = $currentGenre ? 'Genre: ' . $currentGenre['name'] : 'Genre Anime';
$metaDesc  = 'Temukan anime berdasarkan genre favoritmu di AnimeStream.';

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-black text-white mb-2">🎭 Genre Anime</h1>
    <p class="text-gray-500 text-sm mb-8">Pilih genre untuk menemukan anime yang kamu suka.</p>

    <!-- Genre Tabs -->
    <div class="flex flex-wrap gap-2 mb-10">
        <?php
        $colors = ['bg-purple-600','bg-rose-600','bg-emerald-600','bg-amber-600','bg-sky-600','bg-pink-600','bg-indigo-600','bg-teal-600','bg-orange-600','bg-green-600','bg-cyan-600','bg-red-600'];
        foreach ($genres as $i => $g):
            $active = $g['slug'] === $slug;
            $color  = $colors[$i % count($colors)];
        ?>
        <a href="/test-antigravity/genre.php?slug=<?= h($g['slug']) ?>"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition-all hover:scale-105 <?= $active ? $color . ' text-white shadow-lg' : 'border border-border text-gray-400 hover:border-primary hover:text-primary' ?>">
            <?= h($g['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Results -->
    <?php if ($currentGenre): ?>
    <div class="mb-6">
        <h2 class="section-title">
            Anime <?= h($currentGenre['name']) ?>
            <span class="text-sm text-gray-500 font-normal ml-2">(<?= count($animeList) ?> anime)</span>
        </h2>
    </div>
    <?php if ($animeList): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($animeList as $anime): ?>
        <a href="/test-antigravity/watch.php?anime=<?= $anime['id'] ?>" class="anime-card group">
            <div class="thumbnail-wrapper">
                <img src="<?= h($anime['thumbnail']) ?>" alt="<?= h($anime['title']) ?>" loading="lazy">
                <div class="overlay"></div>
                <div class="play-btn">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    </div>
                </div>
                <div class="absolute top-2 right-2 flex items-center gap-0.5 bg-black/60 rounded-full px-2 py-0.5">
                    <span class="text-amber-400 text-xs">★</span>
                    <span class="text-xs text-white font-semibold"><?= h($anime['rating']) ?></span>
                </div>
            </div>
            <div class="p-3">
                <h3 class="font-semibold text-sm text-white truncate mb-1"><?= h($anime['title']) ?></h3>
                <p class="text-xs text-gray-500"><?= h($anime['year']) ?> • <?= h($anime['total_episodes']) ?> Eps</p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-16">
        <div class="text-5xl mb-3">😔</div>
        <p class="text-gray-500">Belum ada anime untuk genre ini.</p>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- No genre selected — show all genres with anime previews -->
    <p class="text-gray-400 text-center py-10">← Pilih genre di atas untuk melihat anime.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
