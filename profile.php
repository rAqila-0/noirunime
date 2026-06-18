<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$user    = getCurrentUser();
$userId  = $_SESSION['user_id'];
$success = '';
$error   = '';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gagal upload file.';
    } elseif (!in_array($file['type'], $allowed)) {
        $error = 'Format file harus JPG, PNG, GIF, atau WebP.';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = 'Ukuran file maksimal 2MB.';
    } else {
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $dir      = __DIR__ . '/uploads/avatars/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            // Delete old avatar
            if ($user['avatar'] && file_exists($dir . $user['avatar'])) {
                @unlink($dir . $user['avatar']);
            }
            $pdo->prepare("UPDATE users SET avatar=? WHERE id=?")->execute([$filename, $userId]);
            $_SESSION['avatar'] = $filename;
            $success = 'Foto profil berhasil diubah!';
            $user = getCurrentUser(); // refresh
        } else {
            $error = 'Gagal menyimpan file.';
        }
    }
}

// Handle username update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    $newUsername = trim($_POST['new_username'] ?? '');
    if (strlen($newUsername) < 3) {
        $error = 'Username minimal 3 karakter.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username=? AND id!=?");
        $check->execute([$newUsername, $userId]);
        if ($check->fetch()) {
            $error = 'Username sudah digunakan orang lain.';
        } else {
            $pdo->prepare("UPDATE users SET username=? WHERE id=?")->execute([$newUsername, $userId]);
            $_SESSION['username'] = $newUsername;
            $success = 'Username berhasil diubah!';
            $user = getCurrentUser();
        }
    }
}

$history       = getWatchHistory($pdo, $userId, 20);
$totalSeconds  = getTotalWatchTime($pdo, $userId);
$totalAnimeWatched = $pdo->prepare("SELECT COUNT(DISTINCT anime_id) FROM watch_history WHERE user_id=?");
$totalAnimeWatched->execute([$userId]);
$totalAnimeWatched = (int)$totalAnimeWatched->fetchColumn();

$pageTitle = 'Profil — ' . h($user['username']);
$metaDesc  = 'Halaman profil pengguna AnimeStream.';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Flash -->
    <?php if ($success): ?>
    <div class="flash-msg mb-6 flex items-center gap-2 text-sm bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <?= h($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flash-msg mb-6 flex items-center gap-2 text-sm bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <?= h($error) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- LEFT — Profile Card -->
        <div class="space-y-5">
            <!-- Avatar Card -->
            <div class="glass rounded-2xl p-6 text-center">
                <div class="relative inline-block mb-4">
                    <img id="avatar-preview"
                         src="<?= avatarUrl($user['avatar']) ?>"
                         alt="Avatar"
                         class="w-28 h-28 rounded-full object-cover mx-auto border-4 border-primary/40 shadow-xl">
                    <label for="avatar-input" class="absolute bottom-0 right-0 w-8 h-8 bg-primary rounded-full flex items-center justify-center cursor-pointer hover:bg-primary-dark transition-colors shadow-lg" title="Ganti foto profil">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </label>
                </div>
                <h2 class="text-xl font-black text-white"><?= h($user['username']) ?></h2>
                <p class="text-sm text-gray-500 mb-1"><?= h($user['email']) ?></p>
                <span class="badge <?= $user['role']==='admin'?'bg-amber-500/20 text-amber-400 border-amber-400/30':'bg-purple-500/20 text-purple-400 border-purple-400/30' ?>">
                    <?= ucfirst($user['role']) ?>
                </span>
                <p class="text-xs text-gray-600 mt-3">Bergabung <?= date('d M Y', strtotime($user['created_at'])) ?></p>

                <!-- Upload Form -->
                <form method="POST" enctype="multipart/form-data" id="avatar-form" class="mt-4">
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden">
                    <button type="submit" id="avatar-submit" class="hidden btn-primary w-full text-sm py-2">
                        Simpan Foto Profil
                    </button>
                </form>
            </div>

            <!-- Stats Card -->
            <div class="glass rounded-2xl p-5">
                <h3 class="font-bold text-white mb-4">📊 Statistik Nonton</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Total Waktu Nonton</span>
                        <span class="font-bold text-white text-sm"><?= formatDuration($totalSeconds) ?></span>
                    </div>
                    <div class="progress-bar w-full opacity-40"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Anime Ditonton</span>
                        <span class="font-bold text-white text-sm"><?= $totalAnimeWatched ?> judul</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Episode Ditonton</span>
                        <span class="font-bold text-white text-sm"><?= count($history) ?>+ episode</span>
                    </div>
                </div>
            </div>

            <!-- Change Username -->
            <div class="glass rounded-2xl p-5">
                <h3 class="font-bold text-white mb-4">✏️ Ubah Username</h3>
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="update_username" value="1">
                    <input type="text" name="new_username" id="new-username"
                           value="<?= h($user['username']) ?>"
                           class="form-input text-sm" minlength="3" maxlength="50">
                    <button type="submit" class="btn-primary w-full text-sm py-2.5">Simpan Username</button>
                </form>
            </div>
        </div>

        <!-- RIGHT — Watch History -->
        <div class="lg:col-span-2">
            <h2 class="section-title mb-6">🕐 Histori Nonton</h2>

            <?php if ($history): ?>
            <div class="space-y-3">
                <?php foreach ($history as $h_item): ?>
                <a href="/test-antigravity/watch.php?anime=<?= $h_item['anime_id'] ?>&ep=<?= $h_item['episode_id'] ?>"
                   class="glass rounded-2xl p-4 flex gap-4 hover:border-primary/40 transition-all hover:scale-[1.01] block">
                    <div class="w-16 h-22 rounded-xl overflow-hidden flex-shrink-0" style="min-height:5.5rem">
                        <img src="<?= h($h_item['thumbnail']) ?>" alt="<?= h($h_item['anime_title']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-white text-sm truncate"><?= h($h_item['anime_title']) ?></h3>
                        <p class="text-xs text-gray-400 mt-0.5">Episode <?= $h_item['episode_number'] ?> — <?= h($h_item['ep_title'] ?? '') ?></p>
                        <p class="text-xs text-gray-600 mt-1">Durasi: <?= formatDuration($h_item['duration']) ?></p>
                        <p class="text-xs text-gray-600">Ditonton: <?= date('d M Y, H:i', strtotime($h_item['watched_at'])) ?></p>
                    </div>
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 rounded-full border border-primary/40 flex items-center justify-center text-primary">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="glass rounded-2xl p-12 text-center">
                <div class="text-5xl mb-4">🎬</div>
                <h3 class="text-lg font-bold text-white mb-2">Belum ada histori</h3>
                <p class="text-gray-500 text-sm mb-6">Mulai tonton anime favoritmu dan histori akan muncul di sini.</p>
                <a href="/test-antigravity/anime-list.php" class="btn-primary">Jelajahi Anime</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Avatar preview before upload
const avatarInput  = document.getElementById('avatar-input');
const avatarPreview = document.getElementById('avatar-preview');
const avatarSubmit = document.getElementById('avatar-submit');

if (avatarInput) {
    avatarInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                avatarPreview.src = e.target.result;
                avatarSubmit.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
