<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$footerSettings = getFooterSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $copyright_text = trim($_POST['copyright_text'] ?? '');
    $show_social_links = isset($_POST['show_social_links']) ? 1 : 0;
    $show_studio_name = isset($_POST['show_studio_name']) ? 1 : 0;
    $developer_name = trim($_POST['developer_name'] ?? '');
    $developer_url = trim($_POST['developer_url'] ?? '');
    $custom_html = trim($_POST['custom_html'] ?? '');

    if ($footerSettings) {
        $stmt = $db->prepare("UPDATE footer_settings SET copyright_text = ?, show_social_links = ?, show_studio_name = ?, developer_name = ?, developer_url = ?, custom_html = ? WHERE id = ?");
        $stmt->execute([$copyright_text, $show_social_links, $show_studio_name, $developer_name, $developer_url, $custom_html, $footerSettings['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO footer_settings (copyright_text, show_social_links, show_studio_name, developer_name, developer_url, custom_html) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$copyright_text, $show_social_links, $show_studio_name, $developer_name, $developer_url, $custom_html]);
    }

    setFlash('success', 'Footer mis à jour.');
    header('Location: footer.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer — Administration</title>
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="admin-main">
            <div class="admin-topbar">
                <h1>Personnalisation du Footer</h1>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Contenu du footer</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-group">
                            <label>Texte de copyright</label>
                            <input type="text" name="copyright_text" class="form-control" value="<?= e($footerSettings['copyright_text'] ?? '© Tous droits réservés') ?>">
                        </div>

                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="checkbox" name="show_studio_name" value="1" <?= ($footerSettings['show_studio_name'] ?? 1) ? 'checked' : '' ?>>
                                Afficher le nom de l'atelier
                            </label>
                        </div>

                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="checkbox" name="show_social_links" value="1" <?= ($footerSettings['show_social_links'] ?? 1) ? 'checked' : '' ?>>
                                Afficher les liens des réseaux sociaux
                            </label>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Crédit développeur</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nom du développeur</label>
                                <input type="text" name="developer_name" class="form-control" value="<?= e($footerSettings['developer_name'] ?? '') ?>" placeholder="Ex: Mon nom">
                            </div>
                            <div class="form-group">
                                <label>URL du site développeur</label>
                                <input type="url" name="developer_url" class="form-control" value="<?= e($footerSettings['developer_url'] ?? '') ?>" placeholder="https://monsite.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>HTML personnalisé</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-group">
                            <label>Contenu HTML supplémentaire</label>
                            <textarea name="custom_html" class="form-control" rows="5" placeholder="HTML affiché en bas du footer"><?= e($footerSettings['custom_html'] ?? '') ?></textarea>
                            <p class="form-help">Ce contenu sera affiché sous le footer principal. Accepte le HTML.</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
