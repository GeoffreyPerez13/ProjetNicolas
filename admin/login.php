<?php
require_once __DIR__ . '/../includes/functions.php';
secureSessionStart();
sendSecurityHeaders();

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    if (!checkLoginAttempts()) {
        $remaining = ceil(getRemainingLockoutTime() / 60);
        $error = "Trop de tentatives. Réessayez dans {$remaining} minute(s).";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                resetLoginAttempts();
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['admin_id'] = $user['id'];
                header('Location: index.php');
                exit;
            } else {
                recordFailedLogin();
                $error = 'Identifiants incorrects.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Administration</title>
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/admin.css">
</head>
<body>
    <div class="login-page">
        <div class="login-box">
            <h1>Administration</h1>
            <p>Connectez-vous pour gérer le site.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrfInput() ?>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem;">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>
