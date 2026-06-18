<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$error = ''; $success = '';
$genres = getGenres($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail   = trim($_POST['thumbnail'] ?? '');
    $status      = $_POST['status'] ?? 'ongoing';
    $totalEp     = (int)($_POST['total_episodes'] ?? 0);
    $year        = (int)($_POST['year'] ?? date('Y'));
    $rating      = (float)($_POST['rating'] ?? 0);
    $genreIds    = $_POST['genres'] ?? [];

    if (!$title) {
        $error = 'Judul anime wajib diisi.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO anime (title,description,thumbnail,status,total_episodes,year,rating) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$title,$description,$thumbnail,$status,$totalEp,$year,$rating]);
        $animeId = $pdo->lastInsertId();

        if ($genreIds) {
            $gStmt = $pdo->prepare("INSERT INTO anime_genres (anime_id,genre_id) VALUES (?,?)");
            foreach ($genreIds as $gId) $gStmt->execute([$animeId, (int)$gId]);
        }
        $success = "Anime \"$title\" berhasil ditambahkan!";
    }
}

$pageTitle = 'Tambah Anime';
include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-8">
        <a href="/test-antigravity/admin/index.php" class="text-gray-500 hover:text-white transition-colors">← Dashboard</a>
        <span class="text-gray-700">/</span>
        <h1 class="text-2xl font-black text-white">➕ Tambah Anime Baru</h1>
    </div>

    <?php if ($success): ?>
    <div class="flash-msg mb-6 flex items-center gap-2 text-sm bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3">
        ✅ <?= h($success) ?>
        <a href="/test-antigravity/admin/index.php" class="ml-auto text-green-300 underline">← Dashboard</a>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flash-msg mb-6 text-sm bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3">❌ <?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="glass rounded-2xl p-8 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Judul Anime *</label>
                <input type="text" name="title" class="form-input" placeholder="Contoh: Attack on Titan" value="<?= h($_POST['title'] ?? '') ?>" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                <textarea name="description" rows="4" class="form-input" placeholder="Sinopsis anime..."><?= h($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">URL Thumbnail (Gambar Cover)</label>
                <input type="url" name="thumbnail" id="thumb-url" class="form-input" placeholder="https://..." value="<?= h($_POST['thumbnail'] ?? '') ?>">
                <p class="text-xs text-gray-600 mt-1">Salin URL gambar dari internet atau MyAnimeList.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="form-input">
                    <option value="ongoing"   <?= ($_POST['status']??'')==='ongoing'?'selected':'' ?>>Ongoing</option>
                    <option value="completed" <?= ($_POST['status']??'')==='completed'?'selected':'' ?>>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Total Episode</label>
                <input type="number" name="total_episodes" class="form-input" placeholder="12" min="0" value="<?= h($_POST['total_episodes'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Tahun</label>
                <input type="number" name="year" class="form-input" placeholder="2024" min="1990" max="2030" value="<?= h($_POST['year'] ?? date('Y')) ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Rating (0-10)</label>
                <input type="number" name="rating" class="form-input" placeholder="8.5" min="0" max="10" step="0.1" value="<?= h($_POST['rating'] ?? '') ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Genre</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($genres as $g): ?>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="genres[]" value="<?= $g['id'] ?>"
                               class="rounded border-border text-primary" 
                               <?= in_array($g['id'], $_POST['genres'] ?? []) ? 'checked' : '' ?>>
                        <span class="text-sm text-gray-300"><?= h($g['name']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary flex-1">Simpan Anime</button>
            <a href="/test-antigravity/admin/index.php" class="flex-1 text-center border border-border text-gray-400 py-3 rounded-xl hover:border-gray-500 hover:text-white transition-all text-sm font-semibold">Batal</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
