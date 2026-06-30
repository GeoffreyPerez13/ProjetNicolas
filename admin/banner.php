<?php
require_once __DIR__ . '/../includes/functions.php';
secureSessionStart();
sendSecurityHeaders();
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'delete') {
    verifyCsrfToken();
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $image = $_POST['existing_image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'banner');
        if (isset($upload['error'])) {
            $error = $upload['error'];
        } else {
            if (!empty($image)) deleteImage($image);
            $image = $upload['filename'];
        }
    }

    if (empty($image) && $_POST['form_action'] === 'add') {
        $error = 'Une image est obligatoire.';
    }

    if (empty($error)) {
        if ($_POST['form_action'] === 'add') {
            $stmt = $db->prepare("INSERT INTO banner_slides (title, subtitle, image, link_url, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $image, $link_url, $display_order, $is_active]);
            setFlash('success', 'Slide ajoutée.');
        } else {
            $stmt = $db->prepare("UPDATE banner_slides SET title = ?, subtitle = ?, image = ?, link_url = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$title, $subtitle, $image, $link_url, $display_order, $is_active, $_POST['id']]);
            setFlash('success', 'Slide mise à jour.');
        }
        header('Location: banner.php');
        exit;
    }
}

if ($action === 'delete' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $slide = $db->prepare("SELECT * FROM banner_slides WHERE id = ?");
    $slide->execute([$id]);
    $slide = $slide->fetch();
    if ($slide) {
        if (!empty($slide['image'])) deleteImage($slide['image']);
        $db->prepare("DELETE FROM banner_slides WHERE id = ?")->execute([$id]);
        setFlash('success', 'Slide supprimée.');
    }
    header('Location: banner.php');
    exit;
}

$slide = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM banner_slides WHERE id = ?");
    $stmt->execute([$id]);
    $slide = $stmt->fetch();
    if (!$slide) { header('Location: banner.php'); exit; }
}

$allSlides = $db->query("SELECT * FROM banner_slides ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bannière — Administration</title>
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
                    <h1>Bannière d'accueil</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle slide
                    </a>
                </div>

                <div class="admin-card">
                    <?php if (empty($allSlides)): ?>
                        <p style="text-align:center;color:var(--admin-text-light);padding:2rem;">Aucune slide. Le site affichera un texte par défaut.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Sous-titre</th>
                                    <th>Ordre</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allSlides as $s): ?>
                                <tr>
                                    <td><img src="<?= baseUrl() . '/' . e($s['image']) ?>" class="thumb" alt=""></td>
                                    <td><?= e($s['title'] ?: '—') ?></td>
                                    <td style="font-size:0.8rem;"><?= e($s['subtitle'] ?: '—') ?></td>
                                    <td><?= $s['display_order'] ?></td>
                                    <td><span class="status-badge <?= $s['is_active'] ? 'active' : 'inactive' ?>"><?= $s['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <form method="POST" action="?action=delete&id=<?= $s['id'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                                                <?= csrfInput() ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                            </form>
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
                    <h1><?= $action === 'add' ? 'Nouvelle slide' : 'Modifier la slide' ?></h1>
                    <a href="banner.php" class="btn btn-secondary">Retour</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <?= csrfInput() ?>
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($slide): ?>
                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <input type="hidden" name="existing_image" value="<?= e($slide['image'] ?? '') ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Image <span class="required">*</span></label>
                            <input type="file" name="image" class="form-control" accept="image/*" <?= $action === 'add' ? 'required' : '' ?>>
                            <p class="form-help">Format recommandé : 1920x1080px minimum</p>
                            <?php if (!empty($slide['image'])): ?>
                                <div class="current-image">
                                    <img src="<?= baseUrl() . '/' . e($slide['image']) ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Titre (superposé sur l'image)</label>
                            <input type="text" name="title" class="form-control" value="<?= e($slide['title'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Sous-titre</label>
                            <input type="text" name="subtitle" class="form-control" value="<?= e($slide['subtitle'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Lien (URL)</label>
                            <input type="text" name="link_url" class="form-control" value="<?= e($slide['link_url'] ?? '') ?>" placeholder="Ex: /pages/exposition.php?slug=...">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="display_order" class="form-control" value="<?= e($slide['display_order'] ?? 0) ?>">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" <?= ($slide['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Actif
                                </label>
                            </div>
                        </div>

                        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="banner.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
