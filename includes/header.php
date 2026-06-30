<?php
require_once __DIR__ . '/functions.php';
secureSessionStart();
sendSecurityHeaders();
$settings = getSettings();
$categories = getCategories();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$siteName = e($settings['site_name'] ?? 'Nico Art');
$fullTitle = (isset($pageTitle) ? e($pageTitle) . ' — ' : '') . $siteName;
$metaDescription = isset($pageDescription) ? e($pageDescription) : e(($settings['artist_firstname'] ?? 'Nicolas') . ' ' . ($settings['artist_lastname'] ?? '') . ' — ' . ($settings['studio_name'] ?? 'Artiste'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?= $metaDescription ?>">
    <meta name="theme-color" content="<?= e($settings['primary_color'] ?? '#0a0a0a') ?>">
    <meta property="og:title" content="<?= $fullTitle ?>">
    <meta property="og:description" content="<?= $metaDescription ?>">
    <meta property="og:type" content="website">
    <?php if (isset($ogImage) && $ogImage !== ''): ?>
        <meta property="og:image" content="<?= baseUrl() . '/' . e($ogImage) ?>">
    <?php endif; ?>
    <title><?= $fullTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?= getGoogleFontsUrl($settings) ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/style.css">
    <style>
        :root {
            --color-primary: <?= e($settings['primary_color'] ?? '#0a0a0a') ?>;
            --color-secondary: <?= e($settings['secondary_color'] ?? '#ffffff') ?>;
            --color-accent: <?= e($settings['accent_color'] ?? '#6b7280') ?>;
            --color-bg: <?= e($settings['bg_color'] ?? '#fafafa') ?>;
            --font-heading: '<?= e($settings['font_heading'] ?? 'Cormorant Garamond') ?>', serif;
            --font-body: '<?= e($settings['font_body'] ?? 'Inter') ?>', sans-serif;
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader"><div class="page-loader-spinner"></div></div>
    <!-- Navigation -->
    <header class="site-header" id="siteHeader">
        <div class="header-inner">
            <a href="<?= baseUrl() ?>/" class="site-logo">
                <?= e($settings['artist_firstname'] ?? 'Nicolas') ?> <?= e($settings['artist_lastname'] ?? '') ?>
            </a>

            <nav class="main-nav" id="mainNav">
                <ul class="nav-list">
                    <li><a href="<?= baseUrl() ?>/" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">Accueil</a></li>
                    <?php if (!empty($categories)): ?>
                        <li class="nav-dropdown">
                            <a href="#" class="nav-link nav-dropdown-toggle <?= in_array($currentPage, ['categorie', 'oeuvre', 'exposition']) ? 'active' : '' ?>">Œuvres <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></a>
                            <ul class="dropdown-menu">
                                <?php foreach ($categories as $cat): ?>
                                    <li><a href="<?= baseUrl() ?>/pages/categorie.php?slug=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li><a href="<?= baseUrl() ?>/pages/biographie.php" class="nav-link <?= $currentPage === 'biographie' ? 'active' : '' ?>">Biographie</a></li>
                    <li><a href="<?= baseUrl() ?>/pages/timeline.php" class="nav-link <?= $currentPage === 'timeline' ? 'active' : '' ?>">Parcours</a></li>
                    <li><a href="<?= baseUrl() ?>/pages/contact.php" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
                </ul>
            </nav>

            <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <main class="site-main">
