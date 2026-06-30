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
    $year = trim($_POST['year'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $event_type = $_POST['event_type'] ?? 'exposition';
    $display_order = intval($_POST['display_order'] ?? 0);

    if (empty($year) || empty($title)) {
        $error = 'L\'année et le titre sont obligatoires.';
    } else {
        if ($_POST['form_action'] === 'add') {
            $stmt = $db->prepare("INSERT INTO timeline_events (year, title, description, location, event_type, display_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$year, $title, $description, $location, $event_type, $display_order]);
            setFlash('success', 'Événement ajouté.');
        } else {
            $stmt = $db->prepare("UPDATE timeline_events SET year = ?, title = ?, description = ?, location = ?, event_type = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$year, $title, $description, $location, $event_type, $display_order, $_POST['id']]);
            setFlash('success', 'Événement mis à jour.');
        }
        header('Location: timeline.php');
        exit;
    }
}

if ($action === 'delete' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $db->prepare("DELETE FROM timeline_events WHERE id = ?")->execute([$id]);
    setFlash('success', 'Événement supprimé.');
    header('Location: timeline.php');
    exit;
}

$event = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM timeline_events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if (!$event) { header('Location: timeline.php'); exit; }
}

$allEvents = $db->query("SELECT * FROM timeline_events ORDER BY year DESC, display_order ASC")->fetchAll();
$eventTypes = [
    'exposition' => 'Exposition',
    'formation' => 'Formation',
    'prix' => 'Prix / Distinction',
    'residence' => 'Résidence',
    'publication' => 'Publication',
    'autre' => 'Autre'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline / CV — Administration</title>
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
                    <h1>Timeline / CV</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg>
                        Nouvel événement
                    </a>
                </div>

                <div class="admin-card">
                    <?php if (empty($allEvents)): ?>
                        <p style="text-align:center;color:var(--admin-text-light);padding:2rem;">Aucun événement.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Année</th>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Lieu</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allEvents as $ev): ?>
                                <tr>
                                    <td><strong><?= e($ev['year']) ?></strong></td>
                                    <td><?= e($ev['title']) ?></td>
                                    <td><span style="font-size:0.75rem;"><?= e($eventTypes[$ev['event_type']] ?? $ev['event_type']) ?></span></td>
                                    <td style="font-size:0.85rem;"><?= e($ev['location'] ?: '—') ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?= $ev['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <form method="POST" action="?action=delete&id=<?= $ev['id'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
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
                    <h1><?= $action === 'add' ? 'Nouvel événement' : 'Modifier l\'événement' ?></h1>
                    <a href="timeline.php" class="btn btn-secondary">Retour</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <form method="POST" class="admin-form">
                        <?= csrfInput() ?>
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($event): ?>
                            <input type="hidden" name="id" value="<?= $event['id'] ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Année <span class="required">*</span></label>
                                <input type="text" name="year" class="form-control" required value="<?= e($event['year'] ?? '') ?>" placeholder="Ex: 2024">
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="event_type" class="form-control">
                                    <?php foreach ($eventTypes as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= ($event['event_type'] ?? 'exposition') === $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Titre <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" required value="<?= e($event['title'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" name="location" class="form-control" value="<?= e($event['location'] ?? '') ?>" placeholder="Ex: Galerie XYZ, Paris">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= e($event['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Ordre d'affichage</label>
                            <input type="number" name="display_order" class="form-control" value="<?= e($event['display_order'] ?? 0) ?>" style="max-width:200px;">
                        </div>

                        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="timeline.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
