<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'List Anime';
$metaDesc  = 'Temukan semua anime favoritmu — filter berdasarkan genre, status, dan urutan.';

// Filter params
$sort   = $_GET['sort']   ?? 'latest';
$status = $_GET['status'] ?? '';
$genre  = $_GET['genre']  ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 16;
$offset = ($page - 1) * $limit;

$animeList = getAnimeList($pdo, [
    'sort'       => $sort,
    'status'     => $status,
    'genre_slug' => $genre,
    'limit'      => $limit,
    'offset'     => $offset,
]);

// Total count for pagination
$countWhere = [];
$countParams = [];
if ($status) { $countWhere[] = 'status=?'; $countParams[] = $status; }
if ($genre)  { $countWhere[] = 'EXISTS (SELECT 1 FROM anime_genres ag JOIN genres g ON ag.genre_id=g.id WHERE ag.anime_id=id AND g.slug=?)'; $countParams[] = $genre; }
$countSQL = 'SELECT COUNT(*) FROM anime' . ($countWhere ? ' WHERE ' . implode(' AND ', $countWhere) : '');
$totalCount = (int)$pdo->prepare($countSQL)->execute($countParams) ? $pdo->prepare($countSQL) : 0;
$countStmt = $pdo->prepare($countSQL);
$countStmt->execute($countParams);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalCount / $limit);

$genres  = getGenres($pdo);
include __DIR__ . '/includes/header.php';
?>

<?php
// Helper to build filter URL
function filterUrl($overrides = []) {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') unset($params[$k]);
        else $params[$k] = $v;
    }
    unset($params['page']); 
    return '?' . http_build_query($params);
}

// Labels for display
$sortLabels = ['latest'=>'Terbaru', 'popular'=>'Terpopuler', 'rating'=>'Rating', 'title'=>'A-Z'];
$statusLabels = ['ongoing'=>'Ongoing', 'completed'=>'Completed'];
$currentGenreName = 'Semua Genre';
foreach($genres as $g) { if($g['slug']===$genre) $currentGenreName = $g['name']; }
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-white mb-1">📚 List Anime</h1>
        <p class="text-gray-500 text-sm"><?= number_format($totalCount) ?> anime ditemukan</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-8 p-4 glass rounded-2xl relative z-20">
        <!-- Sort -->
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Urutkan:</label>
            <div class="relative group">
                <button class="bg-white/5 border border-border rounded-xl px-4 py-2 text-sm text-white flex items-center gap-3 hover:border-primary/50 transition-all min-w-[120px] justify-between">
                    <?= $sortLabels[$sort] ?? 'Terbaru' ?>
                    <svg class="w-3 h-3 text-gray-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="absolute top-full left-0 mt-2 w-40 bg-card border border-border rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                    <?php foreach ($sortLabels as $val => $lbl): ?>
                    <a href="<?= filterUrl(['sort' => $val]) ?>" class="block px-4 py-2 text-sm <?= $sort===$val?'text-primary font-bold':'text-gray-400 hover:text-white hover:bg-primary/10' ?> transition-colors">
                        <?= $lbl ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Status:</label>
            <div class="relative group">
                <button class="bg-white/5 border border-border rounded-xl px-4 py-2 text-sm text-white flex items-center gap-3 hover:border-primary/50 transition-all min-w-[120px] justify-between">
                    <?= $statusLabels[$status] ?? 'Semua' ?>
                    <svg class="w-3 h-3 text-gray-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="absolute top-full left-0 mt-2 w-40 bg-card border border-border rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                    <a href="<?= filterUrl(['status' => '']) ?>" class="block px-4 py-2 text-sm <?= !$status?'text-primary font-bold':'text-gray-400 hover:text-white hover:bg-primary/10' ?> transition-colors">Semua</a>
                    <?php foreach ($statusLabels as $val => $lbl): ?>
                    <a href="<?= filterUrl(['status' => $val]) ?>" class="block px-4 py-2 text-sm <?= $status===$val?'text-primary font-bold':'text-gray-400 hover:text-white hover:bg-primary/10' ?> transition-colors">
                        <?= $lbl ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Genre -->
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Genre:</label>
            <div class="relative group">
                <button class="bg-white/5 border border-border rounded-xl px-4 py-2 text-sm text-white flex items-center gap-3 hover:border-primary/50 transition-all min-w-[140px] justify-between">
                    <?= $currentGenreName ?>
                    <svg class="w-3 h-3 text-gray-500 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="absolute top-full left-0 mt-2 w-48 bg-card border border-border rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50 max-h-64 overflow-y-auto scrollbar-thin">
                    <a href="<?= filterUrl(['genre' => '']) ?>" class="block px-4 py-2 text-sm <?= !$genre?'text-primary font-bold':'text-gray-400 hover:text-white hover:bg-primary/10' ?> transition-colors">Semua Genre</a>
                    <?php foreach ($genres as $g): ?>
                    <a href="<?= filterUrl(['genre' => $g['slug']]) ?>" class="block px-4 py-2 text-sm <?= $genre===$g['slug']?'text-primary font-bold':'text-gray-400 hover:text-white hover:bg-primary/10' ?> transition-colors">
                        <?= h($g['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Reset Button -->
        <?php if ($status || $genre || $sort !== 'latest'): ?>
        <a href="?" class="flex items-center gap-2 text-xs text-red-400 hover:text-red-300 font-bold ml-auto px-4 py-2 bg-red-400/5 border border-red-400/10 rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            RESET
        </a>
        <?php endif; ?>
    </div>

    <!-- Grid -->
    <?php if ($animeList): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4 mb-8">
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
                <div class="absolute top-2 left-2">
                    <span class="badge <?= $anime['status']==='ongoing'?'badge-ongoing':'badge-completed' ?>"><?= $anime['status']==='ongoing'?'ON':'DONE' ?></span>
                </div>
                <div class="absolute top-2 right-2 flex items-center gap-0.5 bg-black/60 backdrop-blur-sm rounded-full px-2 py-0.5">
                    <span class="text-amber-400 text-xs">★</span>
                    <span class="text-xs text-white font-semibold"><?= h($anime['rating']) ?></span>
                </div>
            </div>
            <div class="p-3">
                <h3 class="font-semibold text-sm text-white truncate mb-1"><?= h($anime['title']) ?></h3>
                <p class="text-xs text-gray-500"><?= h($anime['year']) ?> • <?= h($anime['total_episodes']) ?> Eps</p>
                <?php if ($anime['genres']): ?>
                <p class="text-xs text-gray-600 truncate mt-0.5"><?= h($anime['genres']) ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>" class="px-4 py-2 rounded-xl border border-border text-sm text-gray-400 hover:border-primary hover:text-primary transition-colors">← Prev</a>
        <?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$p])) ?>"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors <?= $p===$page ? 'bg-primary text-white' : 'border border-border text-gray-400 hover:border-primary hover:text-primary' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>" class="px-4 py-2 rounded-xl border border-border text-sm text-gray-400 hover:border-primary hover:text-primary transition-colors">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center py-20">
        <div class="text-6xl mb-4">🔍</div>
        <h2 class="text-xl font-bold text-white mb-2">Anime tidak ditemukan</h2>
        <p class="text-gray-500 text-sm mb-6">Coba ubah filter atau reset pencarian.</p>
        <a href="anime-list.php" class="btn-primary">Reset Filter</a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
