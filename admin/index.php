<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$stats   = getStats($pdo);
$latest  = getAnimeList($pdo, ['sort' => 'latest', 'limit' => 6]);
$users   = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard Admin';
$metaDesc  = 'Admin dashboard AnimeStream.';
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Title -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-white">⚙️ Dashboard Admin</h1>
            <p class="text-gray-500 text-sm mt-1">Selamat datang, <?= h($_SESSION['username']) ?>!</p>
        </div>
        <a href="/test-antigravity/admin/anime-add.php" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Anime
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <?php
        $statCards = [
            ['label'=>'Total Anime',   'value'=>$stats['anime'],    'icon'=>'🎌', 'color'=>'from-purple-600 to-blue-600'],
            ['label'=>'Total User',    'value'=>$stats['users'],    'icon'=>'👤', 'color'=>'from-emerald-600 to-teal-600'],
            ['label'=>'Total Episode', 'value'=>$stats['episodes'], 'icon'=>'🎬', 'color'=>'from-rose-600 to-orange-600'],
            ['label'=>'Total Views',   'value'=>number_format($stats['views']), 'icon'=>'👁️', 'color'=>'from-amber-600 to-yellow-500'],
        ];
        foreach ($statCards as $s): ?>
        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="text-3xl mb-2"><?= $s['icon'] ?></div>
            <div class="text-2xl font-black text-white mb-0.5"><?= $s['value'] ?></div>
            <div class="text-sm text-gray-400"><?= $s['label'] ?></div>
            <div class="absolute -bottom-4 -right-4 text-6xl opacity-10"><?= $s['icon'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Anime Table -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">📋 Kelola Anime</h2>
                <a href="/test-antigravity/admin/anime-add.php" class="text-xs text-primary-light hover:text-primary font-medium transition-colors">+ Tambah Baru</a>
            </div>
            <div class="glass rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Anime</th>
                            <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3 hidden sm:table-cell">Status</th>
                            <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3 hidden md:table-cell">Rating</th>
                            <th class="text-right text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($latest as $anime): ?>
                        <tr class="hover:bg-white/3 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?= h($anime['thumbnail']) ?>" alt="" class="w-8 h-12 rounded-lg object-cover flex-shrink-0">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white truncate max-w-[140px]"><?= h($anime['title']) ?></p>
                                        <p class="text-xs text-gray-500"><?= h($anime['year']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="badge <?= $anime['status']==='ongoing'?'badge-ongoing':'badge-completed' ?>"><?= $anime['status'] ?></span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="flex items-center gap-1 text-amber-400 text-sm font-semibold">★ <?= $anime['rating'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/test-antigravity/admin/episode-add.php?anime=<?= $anime['id'] ?>" class="text-xs text-blue-400 hover:text-blue-300 border border-blue-400/30 px-2 py-1 rounded-lg transition-colors" title="Tambah Episode">+Ep</a>
                                    <a href="/test-antigravity/admin/anime-edit.php?id=<?= $anime['id'] ?>" class="text-xs text-emerald-400 hover:text-emerald-300 border border-emerald-400/30 px-2 py-1 rounded-lg transition-colors">Edit</a>
                                    <a href="/test-antigravity/admin/anime-delete.php?id=<?= $anime['id'] ?>" class="text-xs text-red-400 hover:text-red-300 border border-red-400/30 px-2 py-1 rounded-lg transition-colors" data-confirm="Yakin hapus anime '<?= h($anime['title']) ?>'?">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-border">
                    <a href="/test-antigravity/admin/anime.php" class="text-xs text-primary-light hover:text-primary transition-colors">Lihat semua anime →</a>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">👥 User Terbaru</h2>
                <a href="/test-antigravity/admin/users.php" class="text-xs text-primary-light hover:text-primary font-medium transition-colors">Semua →</a>
            </div>
            <div class="glass rounded-2xl p-4 space-y-3">
                <?php foreach ($users as $u): ?>
                <div class="flex items-center gap-3">
                    <img src="<?= avatarUrl($u['avatar']) ?>" alt="" class="w-9 h-9 rounded-full object-cover border border-border">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-white truncate"><?= h($u['username']) ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= h($u['email']) ?></p>
                    </div>
                    <span class="badge <?= $u['role']==='admin'?'bg-amber-500/20 text-amber-400 border-amber-400/30':'bg-purple-500/20 text-purple-400 border-purple-400/30' ?> text-xs">
                        <?= $u['role'] ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
