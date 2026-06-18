<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$animeId = (int)($_GET['anime'] ?? 0);
$anime   = getAnimeById($pdo, $animeId);
if (!$anime) { header('Location: /test-antigravity/admin/index.php'); exit; }

$episodes = getEpisodes($pdo, $animeId);
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $epNum    = (int)($_POST['episode_number'] ?? 0);
    $epTitle  = trim($_POST['title'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $duration = (int)($_POST['duration'] ?? 1440);

    if (!$epNum || !$videoUrl) {
        $error = 'Nomor episode dan URL video wajib diisi.';
    } else {
        // Check duplicate episode number
        $check = $pdo->prepare("SELECT id FROM episodes WHERE anime_id=? AND episode_number=?");
        $check->execute([$animeId, $epNum]);
        if ($check->fetch()) {
            $error = "Episode $epNum sudah ada untuk anime ini.";
        } else {
            $pdo->prepare("INSERT INTO episodes (anime_id, episode_number, title, video_url, duration) VALUES (?,?,?,?,?)")
                ->execute([$animeId, $epNum, $epTitle, $videoUrl, $duration]);
            $success = "Episode $epNum berhasil ditambahkan!";
            $episodes = getEpisodes($pdo, $animeId);
        }
    }
}

$pageTitle = 'Episode — ' . $anime['title'];
include __DIR__ . '/../includes/header.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-8 flex-wrap">
        <a href="/test-antigravity/admin/index.php" class="text-gray-500 hover:text-white transition-colors text-sm">← Dashboard</a>
        <span class="text-gray-700">/</span>
        <a href="/test-antigravity/admin/anime-edit.php?id=<?= $animeId ?>" class="text-gray-500 hover:text-white transition-colors text-sm truncate max-w-xs"><?= h($anime['title']) ?></a>
        <span class="text-gray-700">/</span>
        <h1 class="text-2xl font-black text-white">🎬 Kelola Episode</h1>
    </div>

    <?php if ($success): ?>
    <div class="flash-msg mb-5 text-sm bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3">✅ <?= h($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flash-msg mb-5 text-sm bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3">❌ <?= h($error) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Add Episode Form -->
        <div>
            <h2 class="section-title mb-5">➕ Tambah Episode</h2>
            <form method="POST" class="glass rounded-2xl p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nomor Episode *</label>
                    <input type="number" name="episode_number" id="ep-number" class="form-input" placeholder="1" min="1"
                           value="<?= count($episodes) + 1 ?>" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Judul Episode</label>
                    <input type="text" name="title" class="form-input" placeholder="Contoh: The Beginning">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">URL Video *</label>

                    <!-- Source Tabs -->
                    <div class="flex gap-1 bg-black/30 rounded-xl p-1 mb-3">
                        <button type="button" id="tab-yt" onclick="switchTab('youtube')"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold transition-all tab-active-yt">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            YouTube
                        </button>
                        <button type="button" id="tab-gd" onclick="switchTab('gdrive')"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold transition-all tab-active-gd">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22.396l4-6.929H24l-4 6.929H4.433zm3.566-6.929L4 9.938 8 3l4 6.929-4 5.538zm12.568 0L16.567 9H8L12 2.071l8.567 14.396z"/></svg>
                            Google Drive
                        </button>
                    </div>

                    <input type="url" name="video_url" id="video-url" class="form-input" required
                           placeholder="https://www.youtube.com/watch?v=...">

                    <!-- YouTube hint -->
                    <div id="hint-youtube" class="mt-2 p-3 rounded-xl bg-red-500/8 border border-red-500/15 text-xs text-gray-400 space-y-1">
                        <p class="font-semibold text-red-400">📺 Format YouTube yang diterima:</p>
                        <p class="font-mono text-gray-500">youtube.com/watch?v=<span class="text-gray-300">VIDEO_ID</span></p>
                        <p class="font-mono text-gray-500">youtu.be/<span class="text-gray-300">VIDEO_ID</span></p>
                        <p class="font-mono text-gray-500">youtube.com/embed/<span class="text-gray-300">VIDEO_ID</span></p>
                    </div>

                    <!-- Google Drive hint -->
                    <div id="hint-gdrive" class="hidden mt-2 p-3 rounded-xl bg-blue-500/8 border border-blue-500/15 text-xs text-gray-400 space-y-1">
                        <p class="font-semibold text-blue-400">🗂️ Cara menggunakan Google Drive:</p>
                        <ol class="list-decimal ml-4 space-y-1 text-gray-400">
                            <li>Upload video ke Google Drive</li>
                            <li>Klik kanan → <strong class="text-gray-300">Bagikan</strong> → Ubah ke <strong class="text-gray-300">"Siapa saja yang memiliki link"</strong></li>
                            <li>Salin link dan tempel di sini</li>
                        </ol>
                        <p class="mt-1 font-semibold text-gray-500">Format yang diterima:</p>
                        <p class="font-mono text-gray-500">drive.google.com/file/d/<span class="text-gray-300">FILE_ID</span>/view</p>
                        <p class="font-mono text-gray-500">drive.google.com/open?id=<span class="text-gray-300">FILE_ID</span></p>
                        <p class="mt-1 text-amber-400">⚠️ Pastikan izin berbagi sudah diset "Siapa saja dengan link" agar video bisa diputar.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Durasi (detik)</label>
                    <input type="number" name="duration" class="form-input" placeholder="1440" value="1440" min="0">
                    <p class="text-xs text-gray-600 mt-1">1440 = 24 menit, 1800 = 30 menit, 2700 = 45 menit</p>
                </div>
                <button type="submit" class="btn-primary w-full">Tambah Episode</button>
            </form>
        </div>

        <!-- Episode List -->
        <div>
            <h2 class="section-title mb-5">📋 Daftar Episode (<?= count($episodes) ?>)</h2>
            <?php if ($episodes): ?>
            <div class="glass rounded-2xl overflow-hidden">
                <div class="max-h-96 overflow-y-auto">
                    <?php foreach ($episodes as $ep): ?>
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-border last:border-0 hover:bg-white/3 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold text-primary-light"><?= $ep['episode_number'] ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">
                                Ep. <?= $ep['episode_number'] ?> <?= $ep['title'] ? '— ' . h($ep['title']) : '' ?>
                            </p>
                            <p class="text-xs text-gray-500 truncate flex items-center gap-1">
                                <?php
                                $src = getVideoSource($ep['video_url']);
                                if ($src === 'youtube'):
                                ?>
                                <span class="text-red-400">▶ YT</span>
                                <?php elseif ($src === 'googledrive'): ?>
                                <span class="text-blue-400">🗂 GDrive</span>
                                <?php endif; ?>
                                <span class="truncate"><?= h($ep['video_url']) ?></span>
                            </p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <a href="/test-antigravity/watch.php?anime=<?= $animeId ?>&ep=<?= $ep['id'] ?>" target="_blank"
                               class="text-xs text-blue-400 hover:text-blue-300 border border-blue-400/30 px-2 py-1 rounded-lg transition-colors">👁️</a>
                            <a href="/test-antigravity/admin/episode-delete.php?id=<?= $ep['id'] ?>&anime=<?= $animeId ?>"
                               class="text-xs text-red-400 hover:text-red-300 border border-red-400/30 px-2 py-1 rounded-lg transition-colors"
                               data-confirm="Hapus episode <?= $ep['episode_number'] ?>?">🗑️</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="glass rounded-2xl p-8 text-center">
                <div class="text-4xl mb-3">🎬</div>
                <p class="text-gray-500 text-sm">Belum ada episode. Tambahkan episode pertama!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let currentTab = 'youtube';

function switchTab(tab) {
    currentTab = tab;
    const tabYt  = document.getElementById('tab-yt');
    const tabGd  = document.getElementById('tab-gd');
    const hintYt = document.getElementById('hint-youtube');
    const hintGd = document.getElementById('hint-gdrive');
    const urlInput = document.getElementById('video-url');

    if (tab === 'youtube') {
        tabYt.classList.add('bg-red-500', 'text-white', 'shadow');
        tabYt.classList.remove('text-gray-400');
        tabGd.classList.remove('bg-blue-500', 'text-white', 'shadow');
        tabGd.classList.add('text-gray-400');
        hintYt.classList.remove('hidden');
        hintGd.classList.add('hidden');
        urlInput.placeholder = 'https://www.youtube.com/watch?v=...';
    } else {
        tabGd.classList.add('bg-blue-500', 'text-white', 'shadow');
        tabGd.classList.remove('text-gray-400');
        tabYt.classList.remove('bg-red-500', 'text-white', 'shadow');
        tabYt.classList.add('text-gray-400');
        hintGd.classList.remove('hidden');
        hintYt.classList.add('hidden');
        urlInput.placeholder = 'https://drive.google.com/file/d/.../view';
    }
}

// Init: YouTube tab active by default
switchTab('youtube');

// Auto-detect sumber dari URL yang di-paste
document.getElementById('video-url').addEventListener('paste', function(e) {
    setTimeout(() => {
        const val = this.value;
        if (val.includes('drive.google.com')) {
            switchTab('gdrive');
        } else if (val.includes('youtube.com') || val.includes('youtu.be')) {
            switchTab('youtube');
        }
    }, 50);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
