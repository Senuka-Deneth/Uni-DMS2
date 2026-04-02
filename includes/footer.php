<?php
$footerNav = [
    'Navigate' => ['Home' => 'index.php', 'Universities' => 'universities.php', 'Z-Score Finder' => 'finder.php'],
    'Resources' => ['Gallery' => 'gallery.php', 'About' => 'about.php', 'Sign In' => 'login.php']
];
?>
    </main>
    <footer class="page-footer">
        <div class="container footer-top">
            <div class="footer-brand">
                <p class="footer-heading">Uni-DMS</p>
                <span>Sri Lanka's calm authority for the university decision.</span>
            </div>
            <?php foreach ($footerNav as $heading => $links): ?>
                <div class="footer-links">
                    <p class="footer-heading"><?php echo htmlspecialchars($heading); ?></p>
                    <?php foreach ($links as $label => $href): ?>
                        <a class="footer-link" href="<?php echo $href; ?>"><?php echo htmlspecialchars($label); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="container footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Uni-DMS • Guiding Sri Lanka's Class of 2026 and beyond.</p>
            <p>Made for Sri Lankan students with calm authority.</p>
        </div>
    </footer>
</div>
<script src="js/main.js" defer></script>
</body>
</html>
