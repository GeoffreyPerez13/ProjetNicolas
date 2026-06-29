<?php
$pageTitle = 'Page introuvable';
require_once __DIR__ . '/includes/header.php';
?>

<div class="empty-state" style="min-height:60vh;display:flex;flex-direction:column;align-items:center;justify-content:center;">
    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 16s-1.5-2-4-2-4 2-4 2"></path><line x1="9" x2="9.01" y1="9" y2="9"></line><line x1="15" x2="15.01" y1="9" y2="9"></line></svg>
    <h1 style="font-size:4rem;font-weight:200;margin:1rem 0 0.5rem;font-family:var(--font-heading);">404</h1>
    <p style="margin-bottom:2rem;">Cette page n'existe pas ou a été déplacée.</p>
    <a href="<?= baseUrl() ?>/" class="btn">Retour à l'accueil</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
