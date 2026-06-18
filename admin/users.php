<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 15;
$offset  = ($page - 1) * $limit;

$whereSQL = $search ? "WHERE username LIKE ? OR email LIKE ?" : "";
$params   = $search ? ["%$search%", "%$search%"] : [];

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSQL");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalCount / $limit);

$stmt = $pdo->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM watch_history wh WHERE wh.user_id=u.id) AS total_watched
    FROM users u $whereSQL ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Handle role toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_role'])) {
    $uid = (int)$_POST['user_id'];
    if ($uid !== $_SESSION['user_id']) { // can't change own role
        $stmt2 = $pdo->prepare("SELECT role FROM users WHERE id=?");
        $stmt2->execute([$uid]);
        $currentRole = $stmt2->fetchColumn();
        $newRole = $currentRole === 'admin' ? 'user' : 'admin';
        $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$newRole, $uid]);
        header('Location: /test-antigravity/admin/users.php?q=' . urlencode($search));
        exit;
    }
}

// Handle delete user
if (isset($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        header('Location: /test-antigravity/admin/users.php');
        exit;
    }
}

$pageTitle = 'Kelola User';
include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="/test-antigravity/admin/index.php" class="text-gray-500 hover:text-white transition-colors text-sm">← Dashboard</a>
            <span class="text-gray-700">/</span>
            <h1 class="text-2xl font-black text-white">👥 Kelola User</h1>
        </div>
        <span class="text-sm text-gray-500"><?= number_format($totalCount) ?> user terdaftar</span>
    </div>

    <!-- Search -->
    <form method="GET" class="mb-6">
        <div class="relative max-w-sm">
            <input type="text" name="q" value="<?= h($search) ?>" placeholder="Cari username atau email..."
                   class="form-input pl-4 pr-10 py-2.5 text-sm">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </div>
    </form>

    <!-- Users Table -->
    <div class="glass rounded-2xl overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3">User</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Email</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Role</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">Tonton</th>
                    <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">Bergabung</th>
                    <th class="text-right text-xs text-gray-500 uppercase tracking-wider px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-white/3 transition-colors <?= $u['id']==$_SESSION['user_id'] ? 'bg-primary/5' : '' ?>">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="<?= avatarUrl($u['avatar']) ?>" alt="" class="w-9 h-9 rounded-full object-cover border border-border flex-shrink-0">
                            <div>
                                <p class="font-semibold text-white"><?= h($u['username']) ?> <?= $u['id']==$_SESSION['user_id'] ? '<span class="text-xs text-primary-light">(Anda)</span>' : '' ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell text-gray-400"><?= h($u['email']) ?></td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <span class="badge <?= $u['role']==='admin'?'bg-amber-500/20 text-amber-400 border-amber-400/30':'bg-purple-500/20 text-purple-400 border-purple-400/30' ?>">
                            <?= $u['role'] ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 hidden lg:table-cell text-gray-400"><?= $u['total_watched'] ?> episode</td>
                    <td class="px-5 py-3 hidden lg:table-cell text-gray-500 text-xs"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td class="px-5 py-3 text-right">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="toggle_role" class="text-xs <?= $u['role']==='admin'?'text-amber-400 border-amber-400/30':'text-blue-400 border-blue-400/30' ?> border px-2 py-1 rounded-lg hover:opacity-80 transition-opacity">
                                    <?= $u['role']==='admin' ? '→ User' : '→ Admin' ?>
                                </button>
                            </form>
                            <a href="?delete=<?= $u['id'] ?>&q=<?= urlencode($search) ?>"
                               class="text-xs text-red-400 hover:text-red-300 border border-red-400/30 px-2 py-1 rounded-lg transition-colors"
                               data-confirm="Hapus user '<?= h($u['username']) ?>'? Semua histori akan ikut terhapus.">Hapus</a>
                        </div>
                        <?php else: ?>
                        <span class="text-xs text-gray-600">—</span>
                        <?php endif; ?>
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
