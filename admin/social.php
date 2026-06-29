<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

$platforms = ['Instagram', 'Facebook', 'Twitter', 'YouTube', 'LinkedIn', 'TikTok', 'Behance', 'Autre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platform = trim($_POST['platform'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($platform) || empty($url)) {
        $error = 'La plateforme et l\'URL sont obligatoires.';
    } else {
        if ($_POST['form_action'] === 'add') {
            $stmt = $db->prepare("INSERT INTO social_links (platform, url, icon, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$platform, $url, strtolower($platform), $display_order, $is_active]);
            setFlash('success', 'Lien ajouté.');
        } else {
            $stmt = $db->prepare("UPDATE social_links SET platform = ?, url = ?, icon = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$platform, $url, strtolower($platform), $display_order, $is_active, $_POST['id']]);
            setFlash('success', 'Lien mis à jour.');
        }
        header('Location: social.php');
        exit;
    }
}

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM social_links WHERE id = ?")->execute([$id]);
    setFlash('success', 'Lien supprimé.');
    header('Location: social.php');
    exit;
}

$link = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM social_links WHERE id = ?");
    $stmt->execute([$id]);
    $link = $stmt->fetch();
    if (!$link) { header('Location: social.php'); exit; }
}

$allLinks = $db->query("SELECT * FROM social_links ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réseaux sociaux — Administration</title>
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="admin-main">
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="admin-topbar">
                    <h1>Réseaux sociaux</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouveau lien
                    </a>
                </div>

                <div class="admin-card">
                    <?php if (empty($allLinks)): ?>
                        <p style="text-align:center;color:var(--admin-text-light);padding:2rem;">Aucun lien social.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Plateforme</th>
                                    <th>URL</th>
                                    <th>Ordre</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allLinks as $l): ?>
                                <tr>
                                    <td><strong><?= e($l['platform']) ?></strong></td>
                                    <td style="font-size:0.8rem;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <a href="<?= e($l['url']) ?>" target="_blank"><?= e($l['url']) ?></a>
                                    </td>
                                    <td><?= $l['display_order'] ?></td>
                                    <td><span class="status-badge <?= $l['is_active'] ? 'active' : 'inactive' ?>"><?= $l['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?= $l['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <a href="?action=delete&id=<?= $l['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <div class="admin-topbar">
                    <h1><?= $action === 'add' ? 'Nouveau lien social' : 'Modifier le lien' ?></h1>
                    <a href="social.php" class="btn btn-secondary">Retour</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($link): ?>
                            <input type="hidden" name="id" value="<?= $link['id'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Plateforme <span class="required">*</span></label>
                            <select name="platform" class="form-control" required>
                                <option value="">— Choisir —</option>
                                <?php foreach ($platforms as $p): ?>
                                    <option value="<?= $p ?>" <?= ($link['platform'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>URL <span class="required">*</span></label>
                            <input type="url" name="url" class="form-control" required value="<?= e($link['url'] ?? '') ?>" placeholder="https://instagram.com/...">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="display_order" class="form-control" value="<?= e($link['display_order'] ?? 0) ?>">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" <?= ($link['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Actif
                                </label>
                            </div>
                        </div>

                        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="social.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
