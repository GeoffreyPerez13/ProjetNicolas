<?php
$pageTitle = 'Accueil';
require_once __DIR__ . '/includes/header.php';

$bannerSlides = getBannerSlides();
$expositions = getExpositions();
$featuredExpos = getFeaturedExpositions();
?>

<?php if (!empty($bannerSlides)): ?>
    <!-- Banner Slider -->
    <section class="hero-banner">
        <div class="banner-slider">
            <?php foreach ($bannerSlides as $i => $slide): ?>
                <div class="banner-slide <?= $i === 0 ? 'active' : '' ?>">
                    <img src="<?= baseUrl() . '/' . e($slide['image']) ?>" alt="<?= e($slide['title'] ?? '') ?>" loading="eager">
                    <div class="banner-slide-overlay">
                        <div class="banner-slide-content">
                            <?php if (!empty($slide['title'])): ?>
                                <h2><?= e($slide['title']) ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($slide['subtitle'])): ?>
                                <p><?= e($slide['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($bannerSlides) > 1): ?>
            <div class="banner-dots">
                <?php foreach ($bannerSlides as $i => $slide): ?>
                    <button class="banner-dot <?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php else: ?>
    <!-- Hero Placeholder -->
    <section class="hero-placeholder">
        <h1><?= e($settings['artist_firstname'] ?? 'Nicolas') ?> <?= e($settings['artist_lastname'] ?? '') ?></h1>
        <p><?= e($settings['studio_name'] ?? 'Atelier Panthera') ?></p>
    </section>
<?php endif; ?>

<!-- Expositions -->
<?php if (!empty($expositions)): ?>
<section class="section">
    <h2 class="section-title">Expositions</h2>
    <div class="expo-grid">
        <?php foreach ($expositions as $expo): ?>
            <a href="<?= baseUrl() ?>/pages/exposition.php?slug=<?= e($expo['slug']) ?>" class="expo-card fade-in">
                <?php if (!empty($expo['cover_image'])): ?>
                    <img src="<?= baseUrl() . '/' . e($expo['cover_image']) ?>" alt="<?= e($expo['title']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="no-image"><?= e($expo['title']) ?></div>
                <?php endif; ?>
                <div class="expo-card-overlay">
                    <div class="expo-card-content">
                        <h3><?= e($expo['title']) ?></h3>
                        <span>
                            <?php if ($expo['date_start']): ?>
                                <?= date('Y', strtotime($expo['date_start'])) ?>
                            <?php endif; ?>
                            <?php if ($expo['location']): ?>
                                &mdash; <?= e($expo['location']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<section class="section">
    <h2 class="section-title">Index</h2>
    <div class="expo-grid">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= baseUrl() ?>/pages/categorie.php?slug=<?= e($cat['slug']) ?>" class="expo-card fade-in">
                <?php if (!empty($cat['cover_image'])): ?>
                    <img src="<?= baseUrl() . '/' . e($cat['cover_image']) ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="no-image"><?= e($cat['name']) ?></div>
                <?php endif; ?>
                <div class="expo-card-overlay">
                    <div class="expo-card-content">
                        <h3><?= e($cat['name']) ?></h3>
                        <?php if (!empty($cat['description'])): ?>
                            <span><?= e(mb_substr($cat['description'], 0, 60)) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (empty($expositions) && empty($categories) && empty($bannerSlides)): ?>
<div class="empty-state">
    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>
    <p>Le site est en cours de construction.<br>Connectez-vous au <a href="<?= baseUrl() ?>/admin/" style="text-decoration:underline;">panneau d'administration</a> pour ajouter du contenu.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
