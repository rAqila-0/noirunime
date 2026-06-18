<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$opts = [
    'search' => $search,
    'limit'  => $limit,
    'offset' => $offset,
    'sort'   => 'latest'
];

$animeList = getAnimeList($pdo, $opts);

// For simple pagination
$totalAnime = $pdo->prepare("SELECT COUNT(*) FROM anime WHERE title LIKE ?");
$totalAnime->execute(['%' . $search . '%']);
$totalCount = (int)$totalAnime->fetchColumn();
$totalPages = ceil($totalCount / $limit);

$pageTitle = 'Kelola Anime';
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="/test-antigravity/admin/index.php" class="text-gray-500 hover:text-white transition-colors text-sm">← Dashboard</a>
            <span class="text-gray-700">/</span>
            <h1 class="text-2xl font-black text-white">📋 Kelola Anime</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500"><?= number_format($totalCount) ?> anime</span>
            <a href="/test-antigravity/admin/anime-add.php" class="btn-primary py-2 px-4 text-xs">
                + Tambah Anime
            </a>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" class="mb-6">
        <div class="relative max-w-sm">
            <input type="text" name="q" value="<?= h($search) ?>" placeholder="Cari judul anime..."
                   class="form-input pl-4 pr-10 py-2.5 text-sm">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </div>
    </form>

    <!-- Anime Table -->
    <div class="glass rounded-2xl overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3">Anime</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Status</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Rating</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">Tahun</th>
                    <th class="text-right text-xs text-gray-500 uppercase tracking-wider px-5 py-3 whitespace-nowrap w-px">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php if (empty($animeList)): ?>
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                        Tidak ada anime yang ditemukan.
                    </td>
                </tr>
                <?php endif; ?>
                <?php foreach ($animeList as $anime): ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="<?= h($anime['thumbnail'] ?? '') ?>" alt="" class="w-9 h-12 rounded-lg object-cover flex-shrink-0 border border-border">
                            <div class="min-w-0 max-w-[150px] sm:max-w-[200px] md:max-w-xs">
                                <p class="font-semibold text-white truncate" title="<?= h($anime['title'] ?? '') ?>"><?= h($anime['title'] ?? '') ?></p>
                                <p class="text-[10px] text-gray-500 truncate mt-0.5"><?= h($anime['genres'] ?? '-') ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <span class="badge <?= $anime['status']==='ongoing'?'badge-ongoing':'badge-completed' ?>">
                            <?= ucfirst($anime['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <span class="text-amber-400 font-bold">★ <?= $anime['rating'] ?></span>
                    </td>
                    <td class="px-5 py-3 hidden lg:table-cell text-gray-400">
                        <?= h($anime['year'] ?? '-') ?>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap w-px">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/test-antigravity/admin/episode-add.php?anime=<?= $anime['id'] ?>" 
                                class="text-xs text-blue-400 hover:text-blue-300 border border-blue-400/30 px-2 py-1 rounded-lg transition-colors" title="Tambah Episode">
                                +Ep
                            </a>
                            <a href="/test-antigravity/admin/anime-edit.php?id=<?= $anime['id'] ?>" 
                                class="text-xs text-emerald-400 hover:text-emerald-300 border border-emerald-400/30 px-2 py-1 rounded-lg transition-colors">
                                Edit
                            </a>
                            <a href="/test-antigravity/admin/anime-delete.php?id=<?= $anime['id'] ?>" 
                                class="text-xs text-red-400 hover:text-red-300 border border-red-400/30 px-2 py-1 rounded-lg transition-colors" 
                                data-confirm="Hapus anime '<?= h($anime['title'] ?? '') ?>'? Semua episode akan ikut terhapus.">
                                Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors <?= $p===$page ? 'bg-primary text-white' : 'border border-border text-gray-400 hover:border-primary hover:text-primary' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
