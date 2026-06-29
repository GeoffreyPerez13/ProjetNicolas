<?php
$pageTitle = 'Contact';
require_once __DIR__ . '/../includes/functions.php';

$formSent = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $formError = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Veuillez entrer une adresse email valide.';
    } else {
        $siteSettings = getSettings();
        $to = $siteSettings['site_name'] ?? 'contact';
        $headers = "From: {$name} <{$email}>\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $emailSubject = !empty($subject) ? $subject : "Message de {$name} via le site";
        $emailBody = "Nom: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";

        // For now, just mark as sent (configure mail server for production)
        $formSent = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="contact-section">
    <h1>Contact</h1>
    <p class="contact-intro">
        N'hésitez pas à me contacter pour toute question, demande de renseignements ou proposition de collaboration.
    </p>

    <?php if ($formSent): ?>
        <div class="form-success">
            Votre message a bien été envoyé. Merci !
        </div>
    <?php else: ?>
        <?php if (!empty($formError)): ?>
            <div class="form-error"><?= e($formError) ?></div>
        <?php endif; ?>

        <form class="contact-form" method="POST" action="">
            <div class="form-group">
                <label for="name">Nom *</label>
                <input type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="subject">Sujet</label>
                <input type="text" id="subject" name="subject" value="<?= e($_POST['subject'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" required><?= e($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Envoyer</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
