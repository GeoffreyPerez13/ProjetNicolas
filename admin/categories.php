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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $mosaic_layout = $_POST['mosaic_layout'] ?? 'grid';
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $slug = generateSlug($name);

    if (empty($name)) {
        $error = 'Le nom est obligatoire.';
    } else {
        $cover_image = $_POST['existing_image'] ?? '';

        if (!empty($_FILES['cover_image']['name'])) {
            $upload = uploadImage($_FILES['cover_image'], 'categories');
            if (isset($upload['error'])) {
                $error = $upload['error'];
            } else {
                if (!empty($cover_image)) deleteImage($cover_image);
                $cover_image = $upload['filename'];
            }
        }

        if (empty($error)) {
            if ($_POST['form_action'] === 'add') {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, description, cover_image, mosaic_layout, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $cover_image, $mosaic_layout, $display_order, $is_active]);
                setFlash('success', 'Catégorie créée avec succès.');
            } else {
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, cover_image = ?, mosaic_layout = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $cover_image, $mosaic_layout, $display_order, $is_active, $_POST['id']]);
                setFlash('success', 'Catégorie mise à jour.');
            }
            header('Location: categories.php');
            exit;
        }
    }
}

// Handle delete (POST only for CSRF safety)
if ($action === 'delete' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $cat = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $cat->execute([$id]);
    $cat = $cat->fetch();
    if ($cat) {
        if (!empty($cat['cover_image'])) deleteImage($cat['cover_image']);
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        setFlash('success', 'Catégorie supprimée.');
    }
    header('Location: categories.php');
    exit;
}

// Load category for editing
$category = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    if (!$category) {
        header('Location: categories.php');
        exit;
    }
}

$allCategories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM oeuvres WHERE category_id = c.id) as oeuvre_count FROM categories c ORDER BY c.display_order ASC, c.name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories — Administration</title>
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
                    <h1>Catégories</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle catégorie
                    </a>
                </div>

                <div class="admin-card">
                    <?php if (empty($allCategories)): ?>
                        <p style="text-align:center;color:var(--admin-text-light);padding:2rem;">Aucune catégorie. Créez-en une !</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Nom</th>
                                    <th>Layout</th>
                                    <th>Œuvres</th>
                                    <th>Ordre</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allCategories as $cat): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($cat['cover_image'])): ?>
                                            <img src="<?= baseUrl() . '/' . e($cat['cover_image']) ?>" class="thumb" alt="">
                                        <?php else: ?>
                                            <span style="color:var(--admin-text-light);font-size:0.75rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= e($cat['name']) ?></strong></td>
                                    <td><span style="font-size:0.75rem;"><?= e($cat['mosaic_layout']) ?></span></td>
                                    <td><?= $cat['oeuvre_count'] ?></td>
                                    <td><?= $cat['display_order'] ?></td>
                                    <td><span class="status-badge <?= $cat['is_active'] ? 'active' : 'inactive' ?>"><?= $cat['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <form method="POST" action="?action=delete&id=<?= $cat['id'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer cette catégorie ?')">
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
                    <h1><?= $action === 'add' ? 'Nouvelle catégorie' : 'Modifier : ' . e($category['name']) ?></h1>
                    <a href="categories.php" class="btn btn-secondary">Retour</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <?= csrfInput() ?>
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($category): ?>
                            <input type="hidden" name="id" value="<?= $category['id'] ?>">
                            <input type="hidden" name="existing_image" value="<?= e($category['cover_image'] ?? '') ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Nom <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?= e($category['name'] ?? $_POST['name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= e($category['description'] ?? $_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Image de couverture</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <?php if (!empty($category['cover_image'])): ?>
                                <div class="current-image">
                                    <img src="<?= baseUrl() . '/' . e($category['cover_image']) ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Type de mosaïque</label>
                            <div class="layout-preview-grid">
                                <?php
                                $layouts = [
                                    'grid' => 'Grille',
                                    'masonry' => 'Masonry',
                                    'fullwidth' => 'Pleine largeur',
                                    'alternating' => 'Alternée',
                                    'mosaic' => 'Mosaïque'
                                ];
                                $currentLayout = $category['mosaic_layout'] ?? 'grid';
                                foreach ($layouts as $key => $label):
                                ?>
                                <label class="layout-option <?= $currentLayout === $key ? 'selected' : '' ?>">
                                    <input type="radio" name="mosaic_layout" value="<?= $key ?>" <?= $currentLayout === $key ? 'checked' : '' ?>>
                                    <div class="layout-icon">
                                        <div class="layout-icon-<?= $key ?>">
                                            <?php if ($key === 'grid'): ?>
                                                <span></span><span></span><span></span><span></span><span></span><span></span>
                                            <?php elseif ($key === 'masonry'): ?>
                                                <span></span><span></span><span></span><span></span><span></span><span></span>
                                            <?php elseif ($key === 'fullwidth'): ?>
                                                <span></span><span></span><span></span>
                                            <?php elseif ($key === 'alternating'): ?>
                                                <div class="alt-row"><span></span><span></span></div>
                                                <div class="alt-row"><span></span><span></span></div>
                                                <div class="alt-row"><span></span><span></span></div>
                                            <?php elseif ($key === 'mosaic'): ?>
                                                <span></span><span></span><span></span><span></span><span></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="layout-label"><?= $label ?></div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="display_order" class="form-control" value="<?= e($category['display_order'] ?? 0) ?>">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Actif (visible sur le site)
                                </label>
                            </div>
                        </div>

                        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="categories.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.layout-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.layout-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
            });
        });
    </script>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
