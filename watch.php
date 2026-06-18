<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$animeId  = (int)($_GET['anime'] ?? 0);
$episodeId = (int)($_GET['ep'] ?? 0);

if (!$animeId) { header('Location: /test-antigravity/index.php'); exit; }

$anime    = getAnimeById($pdo, $animeId);
if (!$anime) { header('Location: /test-antigravity/index.php'); exit; }

$episodes = getEpisodes($pdo, $animeId);

// Select episode
$currentEp = null;
if ($episodeId) {
    foreach ($episodes as $ep) { if ($ep['id'] == $episodeId) { $currentEp = $ep; break; } }
}
if (!$currentEp && $episodes) $currentEp = $episodes[0];

// Record watch history
if (isLoggedIn() && $currentEp) {
    recordWatch($pdo, $_SESSION['user_id'], $animeId, $currentEp['id']);
}

// Related anime (same genre)
$related = [];
if ($anime['genre_slugs']) {
    $firstSlug = explode(',', $anime['genre_slugs'])[0] ?? '';
    $related = getAnimeList($pdo, ['genre_slug' => $firstSlug, 'sort' => 'popular', 'limit' => 6]);
    $related = array_filter($related, fn($r) => $r['id'] != $animeId);
}

$pageTitle = ($currentEp ? "Ep.{$currentEp['episode_number']} — " : '') . $anime['title'];
$metaDesc  = mb_substr($anime['description'], 0, 160);

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Main Content -->
        <div class="flex-1 min-w-0">

            <!-- Breadcrumb -->
            <nav class="text-xs text-gray-500 mb-4 flex items-center gap-2">
                <a href="/test-antigravity/index.php" class="hover:text-primary">Home</a>
                <span>›</span>
                <a href="/test-antigravity/anime-list.php" class="hover:text-primary">Anime</a>
                <span>›</span>
                <span class="text-gray-300"><?= h($anime['title']) ?></span>
                <?php if ($currentEp): ?>
                <span>›</span>
                <span class="text-gray-300">Ep. <?= $currentEp['episode_number'] ?></span>
                <?php endif; ?>
            </nav>

            <!-- VIDEO PLAYER -->
            <?php if ($currentEp && $currentEp['video_url']):
                $embedUrl   = getEmbedUrl($currentEp['video_url']);
                $vidSource  = getVideoSource($currentEp['video_url']);
                $isGdrive   = ($vidSource === 'googledrive');
                $isYoutube  = ($vidSource === 'youtube');
                // YouTube: tambahkan parameter di query string
                $finalUrl   = $isYoutube ? $embedUrl . '?autoplay=0&rel=0&modestbranding=1' : $embedUrl;
            ?>
            <div class="rounded-2xl overflow-hidden bg-black mb-3 shadow-2xl" style="box-shadow:0 0 40px rgba(124,58,237,0.2)">
                <div class="relative" style="aspect-ratio:16/9">
                    <iframe
                        src="<?= h($finalUrl) ?>"
                        title="<?= h($anime['title']) ?> — Episode <?= $currentEp['episode_number'] ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        allowfullscreen
                        class="absolute inset-0 w-full h-full">
                    </iframe>
                </div>
            </div>
            <!-- Source badge -->
            <!--<div class="flex items-center gap-2 mb-5">
                <?php if ($isYoutube): ?>
                <span class="flex items-center gap-1.5 text-xs font-semibold text-red-400 bg-red-500/10 border border-red-500/20 px-3 py-1 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    YouTube
                </span>
                <?php elseif ($isGdrive): ?>
                <span class="flex items-center gap-1.5 text-xs font-semibold text-blue-400 bg-blue-500/10 border border-blue-500/20 px-3 py-1 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22.396l4-6.929H24l-4 6.929H4.433zm3.566-6.929L4 9.938 8 3l4 6.929-4 5.538zm12.568 0L16.567 9H8L12 2.071l8.567 14.396z"/></svg>
                    Google Drive
                </span>
                <span class="text-xs text-gray-600">Pastikan file Drive diset "Siapa saja yang punya link"</span>
                <?php else: ?>
                <span class="text-xs text-gray-600 bg-white/5 px-3 py-1 rounded-full">🎬 Video eksternal</span>
                <?php endif; ?>
            </div>-->
            <?php else: ?>
            <div class="rounded-2xl bg-card border border-border flex items-center justify-center mb-6" style="aspect-ratio:16/9">
                <div class="text-center text-gray-500">
                    <div class="text-5xl mb-3">🎬</div>
                    <p class="font-semibold">Video belum tersedia</p>
                    <p class="text-sm mt-1">Admin belum menambahkan episode ini.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Episode Nav -->
            <?php if ($currentEp): ?>
            <div class="flex items-center justify-between mb-6 gap-4">
                <?php
                $prevEp = null; $nextEp = null;
                foreach ($episodes as $i => $ep) {
                    if ($ep['id'] == $currentEp['id']) {
                        $prevEp = $episodes[$i-1] ?? null;
                        $nextEp = $episodes[$i+1] ?? null;
                        break;
                    }
                }
                ?>
                <?php if ($prevEp): ?>
                <a href="?anime=<?= $animeId ?>&ep=<?= $prevEp['id'] ?>" class="flex items-center gap-2 border border-border text-sm text-gray-400 hover:border-primary hover:text-primary px-4 py-2 rounded-xl transition-all">
                    ← Ep. <?= $prevEp['episode_number'] ?>
                </a>
                <?php else: ?><div></div><?php endif; ?>

                <span class="text-sm text-gray-400 font-semibold">
                    Episode <?= $currentEp['episode_number'] ?> / <?= $anime['total_episodes'] ?>
                </span>

                <?php if ($nextEp): ?>
                <a href="?anime=<?= $animeId ?>&ep=<?= $nextEp['id'] ?>" class="flex items-center gap-2 border border-border text-sm text-gray-400 hover:border-primary hover:text-primary px-4 py-2 rounded-xl transition-all">
                    Ep. <?= $nextEp['episode_number'] ?> →
                </a>
                <?php else: ?><div></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Anime Info -->
            <div class="glass rounded-2xl p-6 mb-6">
                <div class="flex gap-5">
                    <div class="flex-shrink-0 w-24 rounded-xl overflow-hidden hidden sm:block">
                        <img src="<?= h($anime['thumbnail']) ?>" alt="<?= h($anime['title']) ?>" class="w-full">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4 flex-wrap mb-2">
                            <h1 class="text-xl font-black text-white"><?= h($anime['title']) ?></h1>
                            <span class="badge <?= $anime['status']==='ongoing'?'badge-ongoing':'badge-completed' ?>"><?= $anime['status']==='ongoing'?'Ongoing':'Completed' ?></span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-400 mb-3">
                            <span class="flex items-center gap-1"><span class="text-amber-400">★</span><strong class="text-white"><?= h($anime['rating']) ?></strong></span>
                            <span><?= h($anime['year']) ?></span>
                            <span><?= h($anime['total_episodes']) ?> Episodes</span>
                            <span><?= number_format($anime['views']) ?> views</span>
                        </div>
                        <?php if ($anime['genres']): ?>
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <?php foreach (explode(', ', $anime['genres']) as $g): ?>
                            <span class="text-xs px-2.5 py-1 rounded-full border border-primary/30 text-primary-light"><?= h(trim($g)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div class="text-sm text-gray-400 leading-relaxed text-justify"><?= nl2br(h($anime['description'])) ?></div>
                    </div>
                </div>
            </div>

            <!-- Episode List -->
            <?php if ($episodes): ?>
            <div class="glass rounded-2xl p-5">
                <h2 class="font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Daftar Episode
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-64 overflow-y-auto pr-1">
                    <?php foreach ($episodes as $ep): ?>
                    <a href="?anime=<?= $animeId ?>&ep=<?= $ep['id'] ?>"
                       class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm transition-all <?= ($currentEp && $ep['id']==$currentEp['id']) ? 'bg-primary text-white font-semibold' : 'border border-border text-gray-400 hover:border-primary hover:text-primary' ?>">
                        <svg class="w-3.5 h-3.5 flex-shrink-0 <?= ($currentEp && $ep['id']==$currentEp['id']) ? 'text-white' : 'text-gray-600' ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        Ep. <?= $ep['episode_number'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="lg:w-72 flex-shrink-0 space-y-5">
            <?php if (!isLoggedIn()): ?>
            <!-- Login Prompt -->
            <div class="glass rounded-2xl p-5 border border-primary/20">
                <h3 class="font-bold text-white mb-2">🔐 Login untuk Fitur Lengkap</h3>
                <p class="text-sm text-gray-400 mb-4">Login untuk menyimpan histori tonton dan progress episode kamu.</p>
                <a href="/test-antigravity/login.php" class="btn-primary w-full justify-center">Login Sekarang</a>
            </div>
            <?php endif; ?>

            <!-- Related Anime -->
            <?php if ($related): ?>
            <div class="glass rounded-2xl p-5">
                <h3 class="font-bold text-white mb-4">🎌 Anime Serupa</h3>
                <div class="space-y-3">
                    <?php foreach (array_slice($related, 0, 5) as $r): ?>
                    <a href="/test-antigravity/watch.php?anime=<?= $r['id'] ?>" class="flex gap-3 hover:bg-white/5 rounded-xl p-2 -mx-2 transition-colors">
                        <div class="w-12 h-16 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="<?= h($r['thumbnail']) ?>" alt="<?= h($r['title']) ?>" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-white truncate"><?= h($r['title']) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5"><?= h($r['year']) ?></p>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="text-amber-400 text-xs">★</span>
                                <span class="text-xs text-gray-400"><?= h($r['rating']) ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
