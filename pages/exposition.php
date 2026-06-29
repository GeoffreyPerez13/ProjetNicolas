<?php
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: ../');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$expo = getExpositionBySlug($slug);
if (!$expo) {
    header('Location: ../');
    exit;
}

$oeuvres = getOeuvresByExposition($expo['id']);
$layout = $expo['mosaic_layout'] ?? 'fullwidth';
$nextExpo = getAdjacentExposition($expo['id'], 'next');
$pageTitle = $expo['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="expo-header">
    <h1><?= e($expo['title']) ?></h1>
    <div class="expo-meta">
        <?php if ($expo['date_start']): ?>
            <?= date('d/m/Y', strtotime($expo['date_start'])) ?>
            <?php if ($expo['date_end']): ?>
                &mdash; <?= date('d/m/Y', strtotime($expo['date_end'])) ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($expo['location']): ?>
            &nbsp;&bull;&nbsp; <?= e($expo['location']) ?>
        <?php endif; ?>
    </div>
    <?php if (!empty($expo['description'])): ?>
        <div class="expo-description"><?= nl2br(e($expo['description'])) ?></div>
    <?php endif; ?>
</div>

<?php if (!empty($oeuvres)): ?>
    <div class="mosaic-grid">
        <div class="layout-<?= e($layout) ?>">
            <?php foreach ($oeuvres as $oeuvre): ?>
                <?php if ($layout === 'alternating'): ?>
                    <div class="oeuvre-item">
                        <div class="oeuvre-item-img">
                            <a href="#" data-lightbox="<?= baseUrl() . '/' . e($oeuvre['image']) ?>" data-title="<?= e($oeuvre['title']) ?>" data-meta="<?= e(($oeuvre['technique'] ?? '') . ($oeuvre['year'] ? ' — ' . $oeuvre['year'] : '')) ?>">
                                <img src="<?= baseUrl() . '/' . e($oeuvre['image']) ?>" alt="<?= e($oeuvre['title']) ?>">
                            </a>
                        </div>
                        <div class="oeuvre-item-info">
                            <h3><?= e($oeuvre['title']) ?></h3>
                            <?php if (!empty($oeuvre['description'])): ?>
                                <p><?= e($oeuvre['description']) ?></p>
                            <?php endif; ?>
                            <span><?= e($oeuvre['technique'] ?? '') ?> <?= $oeuvre['year'] ? '— ' . e($oeuvre['year']) : '' ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="oeuvre-item" data-lightbox="<?= baseUrl() . '/' . e($oeuvre['image']) ?>" data-title="<?= e($oeuvre['title']) ?>" data-meta="<?= e(($oeuvre['technique'] ?? '') . ($oeuvre['year'] ? ' — ' . $oeuvre['year'] : '')) ?>">
                        <img src="<?= baseUrl() . '/' . e($oeuvre['image']) ?>" alt="<?= e($oeuvre['title']) ?>">
                        <div class="oeuvre-item-overlay">
                            <div class="oeuvre-item-info">
                                <h3><?= e($oeuvre['title']) ?></h3>
                                <span><?= e($oeuvre['technique'] ?? '') ?> <?= $oeuvre['year'] ? '— ' . e($oeuvre['year']) : '' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Scroll arrows -->
    <div class="scroll-arrows">
        <button class="scroll-arrow-btn" id="scrollTop" title="Haut de page">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
        </button>
        <button class="scroll-arrow-btn" id="scrollBottom" title="Bas de page">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
    </div>
<?php endif; ?>

<!-- Next exposition -->
<?php if ($nextExpo): ?>
    <div class="next-expo">
        <span class="next-expo-label">Exposition suivante</span>
        <a href="<?= baseUrl() ?>/pages/exposition.php?slug=<?= e($nextExpo['slug']) ?>" class="next-expo-link">
            <?= e($nextExpo['title']) ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
    </div>
<?php endif; ?>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lightboxClose">&times;</button>
    <div class="lightbox-content">
        <img id="lightboxImg" src="" alt="">
        <div class="lightbox-info">
            <h3 id="lightboxTitle"></h3>
            <p id="lightboxMeta"></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
