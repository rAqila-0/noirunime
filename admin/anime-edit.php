<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id    = (int)($_GET['id'] ?? 0);
$anime = getAnimeById($pdo, $id);
if (!$anime) { header('Location: /test-antigravity/admin/index.php'); exit; }

$genres  = getGenres($pdo);
$error   = ''; $success = '';

// Current anime genres
$agStmt = $pdo->prepare("SELECT genre_id FROM anime_genres WHERE anime_id=?");
$agStmt->execute([$id]);
$currentGenreIds = array_column($agStmt->fetchAll(), 'genre_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail   = trim($_POST['thumbnail'] ?? '');
    $status      = $_POST['status'] ?? 'ongoing';
    $totalEp     = (int)($_POST['total_episodes'] ?? 0);
    $year        = (int)($_POST['year'] ?? date('Y'));
    $rating      = (float)($_POST['rating'] ?? 0);
    $genreIds    = $_POST['genres'] ?? [];

    if (!$title) { $error = 'Judul wajib diisi.'; }
    else {
        $pdo->prepare("UPDATE anime SET title=?,description=?,thumbnail=?,status=?,total_episodes=?,year=?,rating=? WHERE id=?")
            ->execute([$title,$description,$thumbnail,$status,$totalEp,$year,$rating,$id]);

        $pdo->prepare("DELETE FROM anime_genres WHERE anime_id=?")->execute([$id]);
        if ($genreIds) {
            $gStmt = $pdo->prepare("INSERT INTO anime_genres (anime_id,genre_id) VALUES (?,?)");
            foreach ($genreIds as $gId) $gStmt->execute([$id, (int)$gId]);
        }
        $currentGenreIds = array_map('intval', $genreIds);
        $success = "Anime berhasil diperbarui!";
        $anime = getAnimeById($pdo, $id);
    }
}

$pageTitle = 'Edit: ' . $anime['title'];
include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-8">
        <a href="/test-antigravity/admin/index.php" class="text-gray-500 hover:text-white transition-colors">← Dashboard</a>
        <span class="text-gray-700">/</span>
        <h1 class="text-2xl font-black text-white">✏️ Edit Anime</h1>
    </div>

    <?php if ($success): ?>
    <div class="flash-msg mb-6 text-sm bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3">✅ <?= h($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flash-msg mb-6 text-sm bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3">❌ <?= h($error) ?></div>
    <?php endif; ?>

    <div class="flex gap-4 mb-6">
        <a href="/test-antigravity/admin/episode-add.php?anime=<?= $id ?>" class="flex items-center gap-2 text-sm border border-blue-400/40 text-blue-400 px-4 py-2 rounded-xl hover:bg-blue-400/10 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Episode
        </a>
        <a href="/test-antigravity/watch.php?anime=<?= $id ?>" class="flex items-center gap-2 text-sm border border-border text-gray-400 px-4 py-2 rounded-xl hover:border-gray-500 hover:text-white transition-colors" target="_blank">
            👁️ Preview
        </a>
    </div>

    <form method="POST" class="glass rounded-2xl p-8 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Judul Anime *</label>
                <input type="text" name="title" class="form-input" value="<?= h($anime['title']) ?>" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                <textarea name="description" rows="4" class="form-input"><?= h($anime['description']) ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">URL Thumbnail</label>
                <input type="url" name="thumbnail" class="form-input" value="<?= h($anime['thumbnail']) ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="form-input">
                    <option value="ongoing"   <?= $anime['status']==='ongoing'?'selected':'' ?>>Ongoing</option>
                    <option value="completed" <?= $anime['status']==='completed'?'selected':'' ?>>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Total Episode</label>
                <input type="number" name="total_episodes" class="form-input" value="<?= h($anime['total_episodes']) ?>" min="0">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Tahun</label>
                <input type="number" name="year" class="form-input" value="<?= h($anime['year']) ?>" min="1990" max="2030">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Rating</label>
                <input type="number" name="rating" class="form-input" value="<?= h($anime['rating']) ?>" min="0" max="10" step="0.1">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Genre</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($genres as $g): ?>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="genres[]" value="<?= $g['id'] ?>"
                               class="rounded border-border text-primary"
                               <?= in_array((int)$g['id'], $currentGenreIds) ? 'checked' : '' ?>>
                        <span class="text-sm text-gray-300"><?= h($g['name']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary flex-1">Simpan Perubahan</button>
            <a href="/test-antigravity/admin/index.php" class="flex-1 text-center border border-border text-gray-400 py-3 rounded-xl hover:border-gray-500 hover:text-white transition-all text-sm font-semibold">Batal</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
