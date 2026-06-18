<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tonton Anime Favorit Gratis';
$metaDesc  = 'AnimeStream — Nonton anime terbaru dan terpopuler secara gratis. Subtitle Indonesia, HD quality.';

// Data for homepage
$trendingAnime = getAnimeList($pdo, ['sort' => 'popular', 'limit' => 8]);
$latestAnime   = getAnimeList($pdo, ['sort' => 'latest',  'limit' => 8]);
$topRated      = getAnimeList($pdo, ['sort' => 'rating',  'limit' => 4]);
$hero          = $trendingAnime[0] ?? null;

include __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<?php if ($hero): ?>
<section class="relative overflow-hidden" style="min-height:560px;">
    <!-- Background blur thumbnail -->
    <div class="absolute inset-0 z-0">
        <img src="<?= h($hero['thumbnail']) ?>" alt="" class="w-full h-full object-cover scale-110 blur-sm opacity-20">
        <div class="absolute inset-0" style="background:linear-gradient(to right, rgba(10,10,18,0.98) 40%, rgba(10,10,18,0.5) 100%)"></div>
        <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(10,10,18,1) 0%, transparent 40%)"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center" style="min-height:560px;">
        <div class="flex flex-col md:flex-row gap-10 items-center w-full">
            <!-- Text -->
            <div class="flex-1 fade-in">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs font-bold text-primary-light tracking-widest uppercase">#1 Trending</span>
                    <span class="badge <?= $hero['status']==='ongoing'?'badge-ongoing':'badge-completed' ?>"><?= $hero['status']==='ongoing'?'Ongoing':'Completed' ?></span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-white mb-4 leading-tight"><?= h($hero['title']) ?></h1>
                <p class="text-gray-400 text-sm leading-relaxed mb-5 max-w-lg">
                    <?= h(mb_substr($hero['description'], 0, 200)) ?>...
                </p>
                <div class="flex items-center gap-4 mb-6 text-sm text-gray-400">
                    <span class="flex items-center gap-1"><span class="text-amber-400">★</span> <?= h($hero['rating']) ?></span>
                    <span>•</span>
                    <span><?= h($hero['year']) ?></span>
                    <span>•</span>
                    <span><?= h($hero['total_episodes']) ?> Eps</span>
                    <?php if ($hero['genres']): ?><span>•</span><span><?= h($hero['genres']) ?></span><?php endif; ?>
                </div>
                <div class="flex gap-3">
                    <a href="<?= $BASE ?>/watch.php?anime=<?= $hero['id'] ?>" class="btn-primary pulse-glow">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        Tonton Sekarang
                    </a>
                    <a href="<?= $BASE ?>/watch.php?anime=<?= $hero['id'] ?>&info=1" class="flex items-center gap-2 border border-white/20 text-white text-sm font-semibold px-5 py-3 rounded-xl hover:bg-white/10 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Detail
                    </a>
                </div>
            </div>
            <!-- Poster -->
            <div class="flex-shrink-0 fade-in-delay-2 hidden md:block">
                <div class="relative w-52 rounded-2xl overflow-hidden shadow-2xl" style="box-shadow:0 25px 60px rgba(124,58,237,0.4)">
                    <img src="<?= h($hero['thumbnail']) ?>" alt="<?= h($hero['title']) ?>" class="w-full">
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TRENDING SECTION -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="section-title">🔥 Trending</h2>
        <a href="<?= $BASE ?>/anime-list.php?sort=popular" class="text-sm text-primary-light hover:text-primary font-medium transition-colors">Lihat Semua →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4">
        <?php foreach ($trendingAnime as $anime): ?>
        <a href="<?= $BASE ?>/watch.php?anime=<?= $anime['id'] ?>" class="anime-card group">
            <div class="thumbnail-wrapper">
                <img src="<?= h($anime['thumbnail']) ?>" alt="<?= h($anime['title']) ?>" loading="lazy">
                <div class="overlay"></div>
                <div class="play-btn">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    </div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="badge <?= $anime['status']==='ongoing'?'badge-ongoing':'badge-completed' ?>"><?= $anime['status']==='ongoing'?'ON':'DONE' ?></span>
                </div>
                <div class="absolute top-2 right-2 flex items-center gap-0.5 bg-black/50 backdrop-blur-sm rounded-full px-2 py-0.5">
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
</section>

<!-- TOP RATED -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="section-title">⭐ Rating Tertinggi</h2>
        <a href="<?= $BASE ?>/anime-list.php?sort=rating" class="text-sm text-primary-light hover:text-primary font-medium transition-colors">Lihat Semua →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($topRated as $i => $anime): ?>
        <a href="<?= $BASE ?>/watch.php?anime=<?= $anime['id'] ?>" class="glass rounded-2xl p-4 flex gap-4 hover:border-primary/40 transition-all hover:scale-[1.01]">
            <div class="flex-shrink-0 relative w-16 h-24 rounded-xl overflow-hidden">
                <img src="<?= h($anime['thumbnail']) ?>" alt="<?= h($anime['title']) ?>" class="w-full h-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                    <span class="text-2xl font-black text-white/80"><?= $i+1 ?></span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-sm text-white truncate mb-1"><?= h($anime['title']) ?></h3>
                <div class="flex items-center gap-1 mb-2">
                    <span class="text-amber-400 text-sm">★</span>
                    <span class="text-sm font-bold text-amber-300"><?= h($anime['rating']) ?></span>
                </div>
                <p class="text-xs text-gray-500"><?= h($anime['genres'] ?? '-') ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- LATEST -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="flex items-center justify-between mb-6">
        <h2 class="section-title">🆕 Terbaru</h2>
        <a href="<?= $BASE ?>/anime-list.php" class="text-sm text-primary-light hover:text-primary font-medium transition-colors">Lihat Semua →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <?php foreach ($latestAnime as $anime): ?>
        <a href="<?= $BASE ?>/watch.php?anime=<?= $anime['id'] ?>" class="anime-card group">
            <div class="thumbnail-wrapper">
                <img src="<?= h($anime['thumbnail']) ?>" alt="<?= h($anime['title']) ?>" loading="lazy">
                <div class="overlay"></div>
                <div class="play-btn">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <h3 class="font-semibold text-sm text-white truncate mb-1"><?= h($anime['title']) ?></h3>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span><?= h($anime['year']) ?></span>
                    <span class="flex items-center gap-0.5"><span class="text-amber-400">★</span><?= h($anime['rating']) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- GENRE BANNER -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <h2 class="section-title mb-6">🎭 Browse Genre</h2>
    <div class="flex flex-wrap gap-3">
        <?php
        $genreColors = ['from-purple-600 to-blue-600','from-rose-600 to-orange-600','from-emerald-600 to-teal-600','from-amber-600 to-yellow-500','from-pink-600 to-rose-600','from-sky-600 to-cyan-500','from-indigo-600 to-purple-600','from-lime-600 to-green-600'];
        foreach ($genres as $i => $g):
            $color = $genreColors[$i % count($genreColors)];
        ?>
        <a href="<?= $BASE ?>/genre.php?slug=<?= h($g['slug']) ?>"
           class="bg-gradient-to-r <?= $color ?> text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:scale-105 hover:shadow-lg transition-all">
            <?= h($g['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
