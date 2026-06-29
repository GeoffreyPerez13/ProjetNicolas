<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$settings = getSettings();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    $artist_firstname = trim($_POST['artist_firstname'] ?? '');
    $artist_lastname = trim($_POST['artist_lastname'] ?? '');
    $studio_name = trim($_POST['studio_name'] ?? '');
    $primary_color = $_POST['primary_color'] ?? '#0a0a0a';
    $secondary_color = $_POST['secondary_color'] ?? '#ffffff';
    $accent_color = $_POST['accent_color'] ?? '#6b7280';
    $bg_color = $_POST['bg_color'] ?? '#fafafa';
    $font_heading = trim($_POST['font_heading'] ?? 'Cormorant Garamond');
    $font_body = trim($_POST['font_body'] ?? 'Inter');
    $bio_content = trim($_POST['bio_content'] ?? '');

    $bio_image = $_POST['existing_bio_image'] ?? '';
    if (!empty($_FILES['bio_image']['name'])) {
        $upload = uploadImage($_FILES['bio_image'], 'bio');
        if (isset($upload['error'])) {
            $error = $upload['error'];
        } else {
            if (!empty($bio_image)) deleteImage($bio_image);
            $bio_image = $upload['filename'];
        }
    }

    if (empty($error)) {
        if ($settings) {
            $stmt = $db->prepare("UPDATE settings SET site_name = ?, artist_firstname = ?, artist_lastname = ?, studio_name = ?, primary_color = ?, secondary_color = ?, accent_color = ?, bg_color = ?, font_heading = ?, font_body = ?, bio_content = ?, bio_image = ? WHERE id = ?");
            $stmt->execute([$site_name, $artist_firstname, $artist_lastname, $studio_name, $primary_color, $secondary_color, $accent_color, $bg_color, $font_heading, $font_body, $bio_content, $bio_image, $settings['id']]);
        } else {
            $stmt = $db->prepare("INSERT INTO settings (site_name, artist_firstname, artist_lastname, studio_name, primary_color, secondary_color, accent_color, bg_color, font_heading, font_body, bio_content, bio_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$site_name, $artist_firstname, $artist_lastname, $studio_name, $primary_color, $secondary_color, $accent_color, $bg_color, $font_heading, $font_body, $bio_content, $bio_image]);
        }
        setFlash('success', 'Paramètres enregistrés.');
        header('Location: settings.php');
        exit;
    }
}

$settings = getSettings();

$fontOptions = [
    'Cormorant Garamond', 'Playfair Display', 'EB Garamond', 'Libre Baskerville',
    'Lora', 'Merriweather', 'Spectral', 'DM Serif Display', 'Bodoni Moda',
    'Inter', 'Poppins', 'Montserrat', 'Raleway', 'Work Sans', 'DM Sans',
    'Nunito Sans', 'Source Sans 3', 'Outfit', 'Space Grotesk', 'Josefin Sans'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres — Administration</title>
    <link rel="stylesheet" href="<?= baseUrl() ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="admin-main">
            <div class="admin-topbar">
                <h1>Paramètres du site</h1>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Identity -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Identité</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-group">
                            <label>Nom du site</label>
                            <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'Nico Art') ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Prénom de l'artiste</label>
                                <input type="text" name="artist_firstname" class="form-control" value="<?= e($settings['artist_firstname'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Nom de l'artiste</label>
                                <input type="text" name="artist_lastname" class="form-control" value="<?= e($settings['artist_lastname'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nom de l'atelier / studio</label>
                            <input type="text" name="studio_name" class="form-control" value="<?= e($settings['studio_name'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Colors -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Couleurs</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Couleur principale (texte)</label>
                                <div class="color-input-group">
                                    <input type="color" name="primary_color" value="<?= e($settings['primary_color'] ?? '#0a0a0a') ?>" onchange="this.nextElementSibling.value=this.value">
                                    <input type="text" class="form-control" value="<?= e($settings['primary_color'] ?? '#0a0a0a') ?>" onchange="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Couleur secondaire (fond header/footer)</label>
                                <div class="color-input-group">
                                    <input type="color" name="secondary_color" value="<?= e($settings['secondary_color'] ?? '#ffffff') ?>" onchange="this.nextElementSibling.value=this.value">
                                    <input type="text" class="form-control" value="<?= e($settings['secondary_color'] ?? '#ffffff') ?>" onchange="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Couleur d'accent (texte secondaire)</label>
                                <div class="color-input-group">
                                    <input type="color" name="accent_color" value="<?= e($settings['accent_color'] ?? '#6b7280') ?>" onchange="this.nextElementSibling.value=this.value">
                                    <input type="text" class="form-control" value="<?= e($settings['accent_color'] ?? '#6b7280') ?>" onchange="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Couleur de fond</label>
                                <div class="color-input-group">
                                    <input type="color" name="bg_color" value="<?= e($settings['bg_color'] ?? '#fafafa') ?>" onchange="this.nextElementSibling.value=this.value">
                                    <input type="text" class="form-control" value="<?= e($settings['bg_color'] ?? '#fafafa') ?>" onchange="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typography -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Typographie</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Police des titres</label>
                                <select name="font_heading" class="form-control">
                                    <?php foreach ($fontOptions as $font): ?>
                                        <option value="<?= $font ?>" <?= ($settings['font_heading'] ?? '') === $font ? 'selected' : '' ?>><?= $font ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Police du corps</label>
                                <select name="font_body" class="form-control">
                                    <?php foreach ($fontOptions as $font): ?>
                                        <option value="<?= $font ?>" <?= ($settings['font_body'] ?? '') === $font ? 'selected' : '' ?>><?= $font ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biography -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Biographie</h2>
                    </div>
                    <div class="admin-form">
                        <div class="form-group">
                            <label>Photo de biographie</label>
                            <input type="file" name="bio_image" class="form-control" accept="image/*">
                            <input type="hidden" name="existing_bio_image" value="<?= e($settings['bio_image'] ?? '') ?>">
                            <?php if (!empty($settings['bio_image'])): ?>
                                <div class="current-image">
                                    <img src="<?= baseUrl() . '/' . e($settings['bio_image']) ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Texte de biographie</label>
                            <textarea name="bio_content" class="form-control" rows="10"><?= e($settings['bio_content'] ?? '') ?></textarea>
                            <p class="form-help">Les retours à la ligne seront préservés.</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
                </div>
            </form>
        </div>
    </div>
    <script src="<?= baseUrl() ?>/assets/js/main.js"></script>
</body>
</html>
