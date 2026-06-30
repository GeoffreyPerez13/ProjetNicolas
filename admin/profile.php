<?php
require_once __DIR__ . '/../includes/functions.php';
secureSessionStart();
sendSecurityHeaders();
requireAdmin();

$db = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les nouveaux mots de passe ne correspondent pas.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            $newHash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $_SESSION['admin_id']]);
            $success = 'Mot de passe modifié avec succès.';
        } else {
            $error = 'Le mot de passe actuel est incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil — Administration</title>
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="admin-main">
            <div class="admin-topbar">
                <h1>Mon profil</h1>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Changer le mot de passe</h2>
                </div>
                <form method="POST" class="admin-form">
                    <?= csrfInput() ?>
                    <div class="form-group">
                        <label>Mot de passe actuel <span class="required">*</span></label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Nouveau mot de passe <span class="required">*</span></label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                        <p class="form-help">Minimum 6 caractères.</p>
                    </div>

                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe <span class="required">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
                </form>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Informations</h2>
                </div>
                <p style="font-size:0.85rem;color:var(--admin-text-light);">
                    Connecté en tant que : <strong><?= e($_SESSION['admin_user'] ?? 'admin') ?></strong>
                </p>
            </div>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
