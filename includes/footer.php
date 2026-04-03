<?php
$footerNav = [
    'Navigate' => ['Home' => 'index.php', 'Universities' => 'universities.php', 'Z-Score Finder' => 'finder.php'],
    'Resources' => ['Gallery' => 'gallery.php', 'About' => 'about.php', 'Sign In' => 'login.php']
];
?>
    </main>
    <footer class="page-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand-section">
                    <div class="footer-logo">
                        <div class="footer-logo-mark"></div>
                        <span class="footer-logo-text">Uni-DMS</span>
                    </div>
                    <p class="footer-description">Sri Lanka's calm authority for the university decision. Empowers students with data-driven insights for a brighter future.</p>
                    <div class="footer-socials">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                        </a>
                    </div>
                </div>

                <?php foreach ($footerNav as $heading => $links): ?>
                    <div class="footer-nav-col">
                        <h4 class="footer-heading"><?php echo htmlspecialchars($heading); ?></h4>
                        <ul class="footer-links-list">
                            <?php foreach ($links as $label => $href): ?>
                                <li><a class="footer-link" href="<?php echo $href; ?>"><?php echo htmlspecialchars($label); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <div class="footer-subscribe-section">
                    <h4 class="footer-heading">Stay Updated</h4>
                    <p class="footer-subscribe-text">Get the latest university updates and guidance directly in your inbox.</p>
                    <form class="footer-subscribe-form" onsubmit="event.preventDefault();">
                        <input type="email" placeholder="Your email address" class="footer-subscribe-input" required>
                        <button type="submit" class="footer-subscribe-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">&copy; <?php echo date('Y'); ?> <span class="accent">Uni-DMS</span>. Guiding Sri Lanka's next generation with calm authority.</p>
                    <div class="footer-legal-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Help Center</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>
<script src="js/main.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>
