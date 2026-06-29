<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

$catCount = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$oeuvreCount = $db->query("SELECT COUNT(*) FROM oeuvres")->fetchColumn();
$expoCount = $db->query("SELECT COUNT(*) FROM expositions")->fetchColumn();
$timelineCount = $db->query("SELECT COUNT(*) FROM timeline_events")->fetchColumn();
$bannerCount = $db->query("SELECT COUNT(*) FROM banner_slides")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — Administration</title>
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="admin-main">
            <div class="admin-topbar">
                <h1>Tableau de bord</h1>
                <div class="admin-topbar-actions">
                    <a href="<?= baseUrl() ?>/" target="_blank" class="btn btn-secondary">Voir le site</a>
                </div>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-label">Catégories</div>
                    <div class="stat-card-value"><?= $catCount ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Œuvres</div>
                    <div class="stat-card-value"><?= $oeuvreCount ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Expositions</div>
                    <div class="stat-card-value"><?= $expoCount ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Événements CV</div>
                    <div class="stat-card-value"><?= $timelineCount ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Slides bannière</div>
                    <div class="stat-card-value"><?= $bannerCount ?></div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Actions rapides</h2>
                </div>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                    <a href="<?= baseUrl() ?>/admin/categories.php?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle catégorie
                    </a>
                    <a href="<?= baseUrl() ?>/admin/oeuvres.php?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle œuvre
                    </a>
                    <a href="<?= baseUrl() ?>/admin/expositions.php?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle exposition
                    </a>
                    <a href="<?= baseUrl() ?>/admin/settings.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        Paramètres du site
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
