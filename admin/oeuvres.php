<?php
require_once __DIR__ . '/../includes/functions.php';
secureSessionStart();
sendSecurityHeaders();
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'delete') {
    verifyCsrfToken();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
    $dimensions = trim($_POST['dimensions'] ?? '');
    $technique = trim($_POST['technique'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $slug = generateSlug($title);

    if (empty($title)) {
        $error = 'Le titre est obligatoire.';
    } else {
        $image = $_POST['existing_image'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image'], 'oeuvres');
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
                $stmt = $db->prepare("INSERT INTO oeuvres (title, slug, description, image, category_id, year, dimensions, technique, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $description, $image, $category_id, $year, $dimensions, $technique, $display_order, $is_active]);
                setFlash('success', 'Œuvre créée avec succès.');
            } else {
                $stmt = $db->prepare("UPDATE oeuvres SET title = ?, slug = ?, description = ?, image = ?, category_id = ?, year = ?, dimensions = ?, technique = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $description, $image, $category_id, $year, $dimensions, $technique, $display_order, $is_active, $_POST['id']]);
                setFlash('success', 'Œuvre mise à jour.');
            }
            header('Location: oeuvres.php');
            exit;
        }
    }
}

// Handle delete (POST only for CSRF safety)
if ($action === 'delete' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $oeuvre = $db->prepare("SELECT * FROM oeuvres WHERE id = ?");
    $oeuvre->execute([$id]);
    $oeuvre = $oeuvre->fetch();
    if ($oeuvre) {
        if (!empty($oeuvre['image'])) deleteImage($oeuvre['image']);
        $db->prepare("DELETE FROM oeuvres WHERE id = ?")->execute([$id]);
        setFlash('success', 'Œuvre supprimée.');
    }
    header('Location: oeuvres.php');
    exit;
}

// Load oeuvre for editing
$oeuvre = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM oeuvres WHERE id = ?");
    $stmt->execute([$id]);
    $oeuvre = $stmt->fetch();
    if (!$oeuvre) { header('Location: oeuvres.php'); exit; }
}

$allOeuvres = $db->query("SELECT o.*, c.name as category_name FROM oeuvres o LEFT JOIN categories c ON o.category_id = c.id ORDER BY o.display_order ASC, o.created_at DESC")->fetchAll();
$allCategories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Œuvres — Administration</title>
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
                    <h1>Œuvres</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle œuvre
                    </a>
                </div>

                <div class="admin-card">
                    <?php if (empty($allOeuvres)): ?>
                        <p style="text-align:center;color:var(--admin-text-light);padding:2rem;">Aucune œuvre.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Catégorie</th>
                                    <th>Année</th>
                                    <th>Technique</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allOeuvres as $o): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($o['image'])): ?>
                                            <img src="<?= baseUrl() . '/' . e($o['image']) ?>" class="thumb" alt="">
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= e($o['title']) ?></strong></td>
                                    <td><?= e($o['category_name'] ?? '—') ?></td>
                                    <td><?= e($o['year'] ?? '—') ?></td>
                                    <td><span style="font-size:0.75rem;"><?= e($o['technique'] ?? '—') ?></span></td>
                                    <td><span class="status-badge <?= $o['is_active'] ? 'active' : 'inactive' ?>"><?= $o['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?= $o['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <form method="POST" action="?action=delete&id=<?= $o['id'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer cette œuvre ?')">
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
                    <h1><?= $action === 'add' ? 'Nouvelle œuvre' : 'Modifier : ' . e($oeuvre['title']) ?></h1>
                    <a href="oeuvres.php" class="btn btn-secondary">Retour</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <?= csrfInput() ?>
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($oeuvre): ?>
                            <input type="hidden" name="id" value="<?= $oeuvre['id'] ?>">
                            <input type="hidden" name="existing_image" value="<?= e($oeuvre['image'] ?? '') ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Titre <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" required value="<?= e($oeuvre['title'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Image <span class="required">*</span></label>
                            <input type="file" name="image" class="form-control" accept="image/*" <?= $action === 'add' ? 'required' : '' ?>>
                            <?php if (!empty($oeuvre['image'])): ?>
                                <div class="current-image">
                                    <img src="<?= baseUrl() . '/' . e($oeuvre['image']) ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Catégorie</label>
                            <select name="category_id" class="form-control">
                                <option value="">— Aucune —</option>
                                <?php foreach ($allCategories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($oeuvre['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= e($oeuvre['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Année</label>
                                <input type="number" name="year" class="form-control" min="1900" max="2100" value="<?= e($oeuvre['year'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Technique</label>
                                <input type="text" name="technique" class="form-control" value="<?= e($oeuvre['technique'] ?? '') ?>" placeholder="Ex: Huile sur toile">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Dimensions</label>
                                <input type="text" name="dimensions" class="form-control" value="<?= e($oeuvre['dimensions'] ?? '') ?>" placeholder="Ex: 120 x 80 cm">
                            </div>
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="display_order" class="form-control" value="<?= e($oeuvre['display_order'] ?? 0) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="checkbox" name="is_active" value="1" <?= ($oeuvre['is_active'] ?? 1) ? 'checked' : '' ?>>
                                Actif (visible sur le site)
                            </label>
                        </div>

                        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="oeuvres.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
