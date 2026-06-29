<?php
$pageTitle = 'Biographie';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="bio-section">
    <?php if (!empty($settings['bio_image'])): ?>
        <div class="bio-image fade-in">
            <img src="<?= baseUrl() . '/' . e($settings['bio_image']) ?>" alt="<?= e($settings['artist_firstname'] ?? '') ?>">
        </div>
    <?php endif; ?>

    <div class="bio-content <?= empty($settings['bio_image']) ? 'bio-content-full' : '' ?>">
        <h1><?= e($settings['artist_firstname'] ?? 'Nicolas') ?> <?= e($settings['artist_lastname'] ?? '') ?></h1>
        <div class="bio-text">
            <?php if (!empty($settings['bio_content'])): ?>
                <?= nl2br(e($settings['bio_content'])) ?>
            <?php else: ?>
                <p>Biographie à venir.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
