<?php
// includes/db.php — Koneksi database, session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host    = 'localhost';
$db      = 'anime_streaming';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('<div style="font-family:sans-serif;background:#1a1a2e;color:#ff6b6b;padding:2rem;border-radius:1rem;margin:2rem;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p>Pastikan XAMPP MySQL sudah berjalan dan sudah menjalankan <a href="/setup.php" style="color:#a78bfa">setup.php</a>.</p>
    </div>');
}
