<?php
// includes/header.php
$BASE = '/test-antigravity';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$genres = getGenres($pdo);
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h($metaDesc ?? 'AnimeStream — Tonton anime favorit kamu gratis, subtitle Indonesia, HD quality.') ?>">
    <title><?= h($pageTitle ?? 'AnimeStream') ?> <?= isset($pageTitle) ? '— AnimeStream' : '' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark:    '#0a0a12',
                        card:    '#12121e',
                        border:  '#1e1e30',
                        primary: { DEFAULT: '#7c3aed', light: '#a78bfa', dark: '#5b21b6' },
                        accent:  '#3b82f6',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= $BASE ?>/assets/css/custom.css">
</head>
<body class="bg-dark text-white font-sans antialiased min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" style="background:rgba(10,10,18,0.85);backdrop-filter:blur(16px);border-bottom:1px solid rgba(124,58,237,0.15);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 h-16">

            <!-- Logo -->
            <a href="<?= $BASE ?>/index.php" class="flex items-center gap-2 flex-shrink-0">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-black text-sm">A</div>
                <span class="font-black text-xl tracking-tight bg-gradient-to-r from-primary-light to-accent bg-clip-text text-transparent">AnimeStream</span>
            </a>

            <!-- Nav Links -->
            <div class="hidden md:flex items-center gap-1 ml-4">
                <a href="<?= $BASE ?>/index.php" class="nav-link <?= $currentPage==='index'?'nav-active':'' ?>">Home</a>
                <a href="<?= $BASE ?>/anime-list.php" class="nav-link <?= $currentPage==='anime-list'?'nav-active':'' ?>">List Anime</a>

                <!-- Genre Dropdown -->
                <div class="relative group">
                    <button class="nav-link flex items-center gap-1 <?= $currentPage==='genre'?'nav-active':'' ?>">
                        Genre
                        <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="absolute top-full left-0 mt-2 w-48 bg-card border border-border rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                        <?php foreach ($genres as $g): ?>
                        <a href="<?= $BASE ?>/genre.php?slug=<?= h($g['slug']) ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-primary/20 transition-colors">
                            <?= h($g['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 max-w-md mx-4 hidden md:block">
                <form action="<?= $BASE ?>/search.php" method="GET" class="relative">
                    <input type="text" name="q" id="search-input"
                           placeholder="Cari anime..."
                           value="<?= h($_GET['q'] ?? '') ?>"
                           class="w-full bg-white/5 border border-border rounded-full py-2 pl-4 pr-10 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary focus:bg-white/8 transition-all">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>
            </div>

            <!-- User Area -->
            <div class="flex items-center gap-3 ml-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                    <a href="<?= $BASE ?>/admin/index.php" class="hidden md:flex items-center gap-1.5 text-xs font-semibold text-amber-400 border border-amber-400/30 rounded-full px-3 py-1.5 hover:bg-amber-400/10 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Admin
                    </a>
                    <?php endif; ?>
                    <div class="relative group">
                        <button class="flex items-center gap-2 rounded-full hover:ring-2 ring-primary/50 transition-all p-0.5">
                            <img src="<?= avatarUrl($currentUser['avatar'] ?? null) ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover bg-card">
                        </button>
                        <div class="absolute top-full right-0 mt-2 w-48 bg-card border border-border rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                            <div class="px-4 py-2 border-b border-border">
                                <p class="font-semibold text-sm"><?= h($_SESSION['username']) ?></p>
                                <p class="text-xs text-gray-500 capitalize"><?= h($_SESSION['role']) ?></p>
                            </div>
                            <a href="<?= $BASE ?>/profile.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-primary/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profil Saya
                            </a>
                            <a href="<?= $BASE ?>/logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $BASE ?>/login.php" id="btn-login"
                       class="bg-gradient-to-r from-primary to-accent text-white text-sm font-semibold px-5 py-2 rounded-full hover:opacity-90 hover:scale-105 transition-all shadow-lg shadow-primary/30">
                        Login
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-card border-t border-border px-4 py-4 space-y-2">
        <form action="<?= $BASE ?>/search.php" method="GET" class="relative mb-3">
            <input type="text" name="q" placeholder="Cari anime..." class="w-full bg-white/5 border border-border rounded-full py-2 pl-4 pr-10 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>
        <a href="<?= $BASE ?>/index.php" class="block py-2 px-3 rounded-lg hover:bg-white/5 text-gray-300">Home</a>
        <a href="<?= $BASE ?>/anime-list.php" class="block py-2 px-3 rounded-lg hover:bg-white/5 text-gray-300">List Anime</a>
        <?php foreach ($genres as $g): ?>
        <a href="<?= $BASE ?>/genre.php?slug=<?= h($g['slug']) ?>" class="block py-2 px-3 rounded-lg hover:bg-white/5 text-gray-300 text-sm">Genre: <?= h($g['name']) ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div class="h-16"></div>
<main class="flex-1">
