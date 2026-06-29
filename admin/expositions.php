<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date_start = $_POST['date_start'] ?? null;
    $date_end = $_POST['date_end'] ?? null;
    $mosaic_layout = $_POST['mosaic_layout'] ?? 'fullwidth';
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $slug = generateSlug($title);
    $oeuvre_ids = $_POST['oeuvre_ids'] ?? [];

    if (empty($title)) {
        $error = 'Le titre est obligatoire.';
    } else {
        $cover_image = $_POST['existing_image'] ?? '';

        if (!empty($_FILES['cover_image']['name'])) {
            $upload = uploadImage($_FILES['cover_image'], 'expositions');
            if (isset($upload['error'])) {
                $error = $upload['error'];
            } else {
                if (!empty($cover_image)) deleteImage($cover_image);
                $cover_image = $upload['filename'];
            }
        }

        if (empty($error)) {
            if (empty($date_start)) $date_start = null;
            if (empty($date_end)) $date_end = null;

            if ($_POST['form_action'] === 'add') {
                $stmt = $db->prepare("INSERT INTO expositions (title, slug, description, cover_image, date_start, date_end, location, mosaic_layout, display_order, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $description, $cover_image, $date_start, $date_end, $location, $mosaic_layout, $display_order, $is_active, $is_featured]);
                $expoId = $db->lastInsertId();
            } else {
                $expoId = intval($_POST['id']);
                $stmt = $db->prepare("UPDATE expositions SET title = ?, slug = ?, description = ?, cover_image = ?, date_start = ?, date_end = ?, location = ?, mosaic_layout = ?, display_order = ?, is_active = ?, is_featured = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $description, $cover_image, $date_start, $date_end, $location, $mosaic_layout, $display_order, $is_active, $is_featured, $expoId]);
                $db->prepare("DELETE FROM exposition_oeuvres WHERE exposition_id = ?")->execute([$expoId]);
            }

            // Link oeuvres
            if (!empty($oeuvre_ids)) {
                $stmtLink = $db->prepare("INSERT INTO exposition_oeuvres (exposition_id, oeuvre_id, display_order) VALUES (?, ?, ?)");
                foreach ($oeuvre_ids as $order => $oeuvreId) {
                    $stmtLink->execute([$expoId, intval($oeuvreId), $order]);
                }
            }

            setFlash('success', $_POST['form_action'] === 'add' ? 'Exposition créée.' : 'Exposition mise à jour.');
            header('Location: expositions.php');
            exit;
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $expo = $db->prepare("SELECT * FROM expositions WHERE id = ?");
    $expo->execute([$id]);
    $expo = $expo->fetch();
    if ($expo) {
        if (!empty($expo['cover_image'])) deleteImage($expo['cover_image']);
        $db->prepare("DELETE FROM expositions WHERE id = ?")->execute([$id]);
        setFlash('success', 'Exposition supprimée.');
    }
    header('Location: expositions.php');
    exit;
}

// Load expo for editing
$expo = null;
$expoOeuvreIds = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM expositions WHERE id = ?");
    $stmt->execute([$id]);
    $expo = $stmt->fetch();
    if (!$expo) { header('Location: expositions.php'); exit; }
    $expoOeuvreIds = $db->prepare("SELECT oeuvre_id FROM exposition_oeuvres WHERE exposition_id = ? ORDER BY display_order ASC");
    $expoOeuvreIds->execute([$id]);
    $expoOeuvreIds = $expoOeuvreIds->fetchAll(PDO::FETCH_COLUMN);
}

$allExpos = $db->query("SELECT e.*, (SELECT COUNT(*) FROM exposition_oeuvres WHERE exposition_id = e.id) as oeuvre_count FROM expositions e ORDER BY e.display_order ASC, e.date_start DESC")->fetchAll();
$allOeuvres = $db->query("SELECT * FROM oeuvres ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expositions — Administration</title>
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
                    <h1>Expositions</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvelle exposition
                    </a>
                </div>

                <div class="admin-card">
                    <?php if (empty($allExpos)): ?>
                        <p style="text-align:center;color:var(--admin-text-light);padding:2rem;">Aucune exposition.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Lieu</th>
                                    <th>Date</th>
                                    <th>Œuvres</th>
                                    <th>Layout</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allExpos as $ex): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($ex['cover_image'])): ?>
                                            <img src="<?= baseUrl() . '/' . e($ex['cover_image']) ?>" class="thumb" alt="">
                                        <?php else: ?>
                                            <span style="color:var(--admin-text-light);font-size:0.75rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= e($ex['title']) ?></strong>
                                        <?php if ($ex['is_featured']): ?>
                                            <span class="status-badge active" style="margin-left:0.3rem;">Vedette</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($ex['location'] ?? '—') ?></td>
                                    <td style="font-size:0.75rem;"><?= $ex['date_start'] ? date('d/m/Y', strtotime($ex['date_start'])) : '—' ?></td>
                                    <td><?= $ex['oeuvre_count'] ?></td>
                                    <td><span style="font-size:0.75rem;"><?= e($ex['mosaic_layout']) ?></span></td>
                                    <td><span class="status-badge <?= $ex['is_active'] ? 'active' : 'inactive' ?>"><?= $ex['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?= $ex['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <a href="?action=delete&id=<?= $ex['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
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
                    <h1><?= $action === 'add' ? 'Nouvelle exposition' : 'Modifier : ' . e($expo['title']) ?></h1>
                    <a href="expositions.php" class="btn btn-secondary">Retour</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($expo): ?>
                            <input type="hidden" name="id" value="<?= $expo['id'] ?>">
                            <input type="hidden" name="existing_image" value="<?= e($expo['cover_image'] ?? '') ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Titre <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" required value="<?= e($expo['title'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= e($expo['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Image de couverture</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <?php if (!empty($expo['cover_image'])): ?>
                                <div class="current-image">
                                    <img src="<?= baseUrl() . '/' . e($expo['cover_image']) ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date de début</label>
                                <input type="date" name="date_start" class="form-control" value="<?= e($expo['date_start'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Date de fin</label>
                                <input type="date" name="date_end" class="form-control" value="<?= e($expo['date_end'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" name="location" class="form-control" value="<?= e($expo['location'] ?? '') ?>" placeholder="Ex: Galerie XYZ, Paris">
                        </div>

                        <div class="form-group">
                            <label>Type de mosaïque</label>
                            <div class="layout-preview-grid">
                                <?php
                                $layouts = ['grid' => 'Grille', 'masonry' => 'Masonry', 'fullwidth' => 'Pleine largeur', 'alternating' => 'Alternée', 'mosaic' => 'Mosaïque'];
                                $currentLayout = $expo['mosaic_layout'] ?? 'fullwidth';
                                foreach ($layouts as $key => $label):
                                ?>
                                <label class="layout-option <?= $currentLayout === $key ? 'selected' : '' ?>">
                                    <input type="radio" name="mosaic_layout" value="<?= $key ?>" <?= $currentLayout === $key ? 'checked' : '' ?>>
                                    <div class="layout-icon">
                                        <div class="layout-icon-<?= $key ?>">
                                            <?php if ($key === 'grid'): ?><span></span><span></span><span></span><span></span><span></span><span></span>
                                            <?php elseif ($key === 'masonry'): ?><span></span><span></span><span></span><span></span><span></span><span></span>
                                            <?php elseif ($key === 'fullwidth'): ?><span></span><span></span><span></span>
                                            <?php elseif ($key === 'alternating'): ?><div class="alt-row"><span></span><span></span></div><div class="alt-row"><span></span><span></span></div><div class="alt-row"><span></span><span></span></div>
                                            <?php elseif ($key === 'mosaic'): ?><span></span><span></span><span></span><span></span><span></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="layout-label"><?= $label ?></div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Œuvres de l'exposition</label>
                            <p class="form-help">Sélectionnez les œuvres à inclure dans cette exposition.</p>
                            <?php if (!empty($allOeuvres)): ?>
                                <div style="max-height:300px;overflow-y:auto;border:1px solid var(--admin-border);border-radius:6px;padding:0.75rem;margin-top:0.5rem;">
                                    <?php foreach ($allOeuvres as $o): ?>
                                        <label style="display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0;cursor:pointer;font-size:0.85rem;">
                                            <input type="checkbox" name="oeuvre_ids[]" value="<?= $o['id'] ?>" <?= in_array($o['id'], $expoOeuvreIds) ? 'checked' : '' ?>>
                                            <?php if (!empty($o['image'])): ?>
                                                <img src="<?= baseUrl() . '/' . e($o['image']) ?>" style="width:40px;height:30px;object-fit:cover;border-radius:3px;">
                                            <?php endif; ?>
                                            <?= e($o['title']) ?>
                                            <?php if ($o['year']): ?><span style="color:var(--admin-text-light);">(<?= $o['year'] ?>)</span><?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color:var(--admin-text-light);font-size:0.85rem;">Aucune œuvre disponible. <a href="oeuvres.php?action=add">Créez-en une.</a></p>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="display_order" class="form-control" value="<?= e($expo['display_order'] ?? 0) ?>">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                        <input type="checkbox" name="is_active" value="1" <?= ($expo['is_active'] ?? 1) ? 'checked' : '' ?>>
                                        Actif
                                    </label>
                                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                        <input type="checkbox" name="is_featured" value="1" <?= ($expo['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                        En vedette (bannière d'accueil)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="expositions.php" class="btn btn-secondary">Annuler</a>
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
