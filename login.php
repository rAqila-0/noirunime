<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect kalau sudah login
if (isLoggedIn()) {
    header('Location: /test-antigravity/index.php');
    exit;
}

$error   = '';
$success = '';
$mode    = $_GET['mode'] ?? 'login'; // 'login' atau 'register'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';

    if ($mode === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $error = 'Username/email dan password wajib diisi.';
        } elseif (loginUser($identifier, $password)) {
            $redirect = $_GET['redirect'] ?? '/test-antigravity/index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Username/email atau password salah.';
        }

    } elseif ($mode === 'register') {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (!$username || !$email || !$password) {
            $error = 'Semua kolom wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($password !== $password2) {
            $error = 'Password dan konfirmasi password tidak cocok.';
        } else {
            // Cek duplikat
            $check = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
            $check->execute([$username, $email]);
            if ($check->fetch()) {
                $error = 'Username atau email sudah digunakan.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)")
                    ->execute([$username, $email, $hash]);
                $success = 'Akun berhasil dibuat! Silakan login.';
                $mode = 'login';
            }
        }
    }
}

$pageTitle = $mode === 'register' ? 'Daftar Akun Baru' : 'Login';
$metaDesc  = 'Login atau daftar akun AnimeStream untuk menikmati anime favoritmu.';
include __DIR__ . '/includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/test-antigravity/index.php" class="inline-flex items-center gap-2 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-black">A</div>
                <span class="font-black text-2xl bg-gradient-to-r from-primary-light to-accent bg-clip-text text-transparent">AnimeStream</span>
            </a>
            <h1 class="text-2xl font-black text-white"><?= $mode==='register' ? 'Buat Akun Baru' : 'Selamat Datang Kembali' ?></h1>
            <p class="text-gray-500 text-sm mt-1"><?= $mode==='register' ? 'Daftar gratis dan mulai nonton!' : 'Login untuk melanjutkan menonton.' ?></p>
        </div>

        <!-- Card -->
        <div class="glass rounded-2xl p-8 shadow-2xl">

            <!-- Tab Switch -->
            <div class="flex rounded-xl bg-black/30 p-1 mb-6">
                <a href="?mode=login" class="flex-1 text-center py-2 text-sm font-semibold rounded-lg transition-all <?= $mode==='login' ? 'bg-primary text-white shadow' : 'text-gray-400 hover:text-white' ?>">Login</a>
                <a href="?mode=register" class="flex-1 text-center py-2 text-sm font-semibold rounded-lg transition-all <?= $mode==='register' ? 'bg-primary text-white shadow' : 'text-gray-400 hover:text-white' ?>">Daftar</a>
            </div>

            <!-- Flash Messages -->
            <?php if ($error): ?>
            <div class="flash-msg mb-4 flex items-center gap-2 text-sm bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <?= h($error) ?>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="flash-msg mb-4 flex items-center gap-2 text-sm bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <?= h($success) ?>
            </div>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <?php if ($mode === 'login'): ?>
            <form method="POST" class="space-y-4" id="form-login">
                <input type="hidden" name="mode" value="login">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Username atau Email</label>
                    <input type="text" name="identifier" id="login-identifier"
                           value="<?= h($_POST['identifier'] ?? '') ?>"
                           class="form-input" placeholder="Masukkan username atau email" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="login-password"
                               class="form-input pr-10" placeholder="Masukkan password" required>
                        <button type="button" onclick="togglePassword('login-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" id="btn-submit-login" class="btn-primary w-full mt-6 py-3">
                    Login ke Akun
                </button>
                <p class="text-center text-xs text-gray-500 mt-4">
                    Belum punya akun? <a href="?mode=register" class="text-primary-light hover:text-primary font-semibold">Daftar sekarang</a>
                </p>
                <!-- Demo hint -->
                <div class="mt-4 p-3 rounded-xl bg-white/3 border border-white/8 text-xs text-gray-500 text-center">
                    Demo: <span class="text-gray-400 font-medium">admin / admin123</span> &nbsp;|&nbsp; <span class="text-gray-400 font-medium">demouser / user123</span>
                </div>
            </form>

            <!-- REGISTER FORM -->
            <?php else: ?>
            <form method="POST" class="space-y-4" id="form-register">
                <input type="hidden" name="mode" value="register">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Username</label>
                    <input type="text" name="username" id="reg-username"
                           value="<?= h($_POST['username'] ?? '') ?>"
                           class="form-input" placeholder="Pilih username unik" required minlength="3" maxlength="50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" id="reg-email"
                           value="<?= h($_POST['email'] ?? '') ?>"
                           class="form-input" placeholder="email@kamu.com" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="reg-password"
                               class="form-input pr-10" placeholder="Minimal 6 karakter" required minlength="6">
                        <button type="button" onclick="togglePassword('reg-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password2" id="reg-password2"
                           class="form-input" placeholder="Ulangi password" required>
                </div>
                <button type="submit" id="btn-submit-register" class="btn-primary w-full mt-6 py-3">
                    Buat Akun Sekarang
                </button>
                <p class="text-center text-xs text-gray-500 mt-4">
                    Sudah punya akun? <a href="?mode=login" class="text-primary-light hover:text-primary font-semibold">Login di sini</a>
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
