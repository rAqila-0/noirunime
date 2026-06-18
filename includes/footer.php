<?php // includes/footer.php ?>
</main>

<!-- FOOTER -->
<footer class="mt-16 border-t border-border bg-card/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <!-- Brand -->
            <div>
                <a href="<?= $BASE ?>/index.php" class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-black text-sm">A</div>
                    <span class="font-black text-xl bg-gradient-to-r from-primary-light to-accent bg-clip-text text-transparent">AnimeStream</span>
                </a>
                <p class="text-sm text-gray-500 leading-relaxed">Nonton anime favorit kamu secara gratis. Kualitas HD, subtitle Indonesia, update terbaru setiap hari.</p>
            </div>
            <!-- Links -->
            <div>
                <h4 class="font-semibold text-sm text-gray-300 mb-3 uppercase tracking-wider">Navigasi</h4>
                <ul class="space-y-2">
                    <li><a href="<?= $BASE ?>/index.php" class="text-sm text-gray-500 hover:text-primary transition-colors">Home</a></li>
                    <li><a href="<?= $BASE ?>/anime-list.php" class="text-sm text-gray-500 hover:text-primary transition-colors">List Anime</a></li>
                    <li><a href="<?= $BASE ?>/anime-list.php?sort=popular" class="text-sm text-gray-500 hover:text-primary transition-colors">Terpopuler</a></li>
                    <li><a href="<?= $BASE ?>/anime-list.php?status=ongoing" class="text-sm text-gray-500 hover:text-primary transition-colors">Sedang Tayang</a></li>
                </ul>
            </div>
            <!-- Genres -->
            <div>
                <h4 class="font-semibold text-sm text-gray-300 mb-3 uppercase tracking-wider">Genre Populer</h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (array_slice($genres, 0, 8) as $g): ?>
                    <a href="<?= $BASE ?>/genre.php?slug=<?= h($g['slug']) ?>" class="text-xs px-2.5 py-1 rounded-full border border-border text-gray-400 hover:border-primary hover:text-primary transition-colors">
                        <?= h($g['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="border-t border-border pt-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-gray-600">© <?= date('Y') ?> AnimeStream. Dibuat dengan ❤️ untuk para weeb.</p>
            <p class="text-xs text-gray-600">Website ini hanya untuk tujuan edukasi/demo.</p>
        </div>
    </div>
</footer>

<script src="<?= $BASE ?>/assets/js/app.js"></script>
</body>
</html>
