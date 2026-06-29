<?php
$footerSettings = getFooterSettings();
$socialLinks = getSocialLinks();
?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-grid">
                <div class="footer-col">
                    <p class="footer-artist-name">
                        <?= e($settings['artist_firstname'] ?? 'Nicolas') ?> <?= e($settings['artist_lastname'] ?? '') ?>
                    </p>
                    <?php if (!empty($settings['studio_name']) && ($footerSettings['show_studio_name'] ?? 1)): ?>
                        <p class="footer-studio"><?= e($settings['studio_name']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($socialLinks) && ($footerSettings['show_social_links'] ?? 1)): ?>
                <div class="footer-col">
                    <div class="footer-social">
                        <?php foreach ($socialLinks as $link): ?>
                            <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" class="social-link" title="<?= e($link['platform']) ?>">
                                <?= getSocialIcon($link['platform']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="footer-col footer-col-right">
                    <p class="footer-copyright"><?= e($footerSettings['copyright_text'] ?? '© Tous droits réservés') ?></p>
                    <?php if (!empty($footerSettings['developer_name'])): ?>
                        <p class="footer-developer">
                            Site par
                            <?php if (!empty($footerSettings['developer_url'])): ?>
                                <a href="<?= e($footerSettings['developer_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($footerSettings['developer_name']) ?></a>
                            <?php else: ?>
                                <?= e($footerSettings['developer_name']) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($footerSettings['custom_html'])): ?>
                <div class="footer-custom">
                    <?= $footerSettings['custom_html'] ?>
                </div>
            <?php endif; ?>
        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" aria-label="Retour en haut">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
    </button>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" id="lightboxClose" aria-label="Fermer">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"></line><line x1="6" x2="18" y1="6" y2="18"></line></svg>
        </button>
        <button class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Précédent">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <button class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Suivant">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        <div class="lightbox-content">
            <img id="lightboxImg" src="" alt="">
            <div class="lightbox-info">
                <p id="lightboxCounter" class="lightbox-counter"></p>
                <h3 id="lightboxTitle"></h3>
                <p id="lightboxMeta"></p>
            </div>
        </div>
    </div>

    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>

<?php
function getSocialIcon($platform) {
    $platform = strtolower($platform);
    $icons = [
        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line></svg>',
        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>',
        'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"></path><path d="m10 15 5-3-5-3z"></path></svg>',
        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect width="4" height="12" x="2" y="9"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
        'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>',
        'behance' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12.5h7.5M1 7h6.5a3 3 0 0 1 0 6H1V7zm0 6h7a3.5 3.5 0 0 1 0 7H1v-7zm14-7h6m-3 0v10m2.5-5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"></path></svg>',
    ];
    return $icons[$platform] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" x2="22" y1="12" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
}
?>
