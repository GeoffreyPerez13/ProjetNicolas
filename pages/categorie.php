<?php
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: ../');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$category = getCategoryBySlug($slug);
if (!$category) {
    header('Location: ../');
    exit;
}

$oeuvres = getOeuvresByCategory($category['id']);
$layout = $category['mosaic_layout'] ?? 'grid';
$pageTitle = $category['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><?= e($category['name']) ?></h1>
    <?php if (!empty($category['description'])): ?>
        <p><?= e($category['description']) ?></p>
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
                            <span>
                                <?= e($oeuvre['technique'] ?? '') ?>
                                <?= $oeuvre['year'] ? ' — ' . e($oeuvre['year']) : '' ?>
                                <?= !empty($oeuvre['dimensions']) ? '<br>' . e($oeuvre['dimensions']) : '' ?>
                            </span>
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
<?php else: ?>
    <div class="empty-state">
        <p>Aucune oeuvre dans cette cat&eacute;gorie pour le moment.</p>
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
