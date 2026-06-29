<?php
require_once __DIR__ . '/../config/database.php';

// Get site settings
function getSettings() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM settings LIMIT 1");
    return $stmt->fetch() ?: [];
}

// Get footer settings
function getFooterSettings() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM footer_settings LIMIT 1");
    return $stmt->fetch() ?: [];
}

// Get social links
function getSocialLinks() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM social_links WHERE is_active = 1 ORDER BY display_order ASC");
    return $stmt->fetchAll();
}

// Get active categories
function getCategories() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
    return $stmt->fetchAll();
}

// Get single category by slug
function getCategoryBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// Get oeuvres by category
function getOeuvresByCategory($categoryId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM oeuvres WHERE category_id = ? AND is_active = 1 ORDER BY display_order ASC, year DESC");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

// Get single oeuvre by slug
function getOeuvreBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT o.*, c.name as category_name, c.slug as category_slug FROM oeuvres o LEFT JOIN categories c ON o.category_id = c.id WHERE o.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// Get all active expositions
function getExpositions() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM expositions WHERE is_active = 1 ORDER BY display_order ASC, date_start DESC");
    return $stmt->fetchAll();
}

// Get featured expositions for banner
function getFeaturedExpositions() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM expositions WHERE is_active = 1 AND is_featured = 1 ORDER BY display_order ASC, date_start DESC");
    return $stmt->fetchAll();
}

// Get single exposition by slug
function getExpositionBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM expositions WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// Get oeuvres for an exposition
function getOeuvresByExposition($expositionId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT o.* FROM oeuvres o INNER JOIN exposition_oeuvres eo ON o.id = eo.oeuvre_id WHERE eo.exposition_id = ? AND o.is_active = 1 ORDER BY eo.display_order ASC");
    $stmt->execute([$expositionId]);
    return $stmt->fetchAll();
}

// Get next/previous exposition
function getAdjacentExposition($currentId, $direction = 'next') {
    $db = getDB();
    $op = $direction === 'next' ? '>' : '<';
    $order = $direction === 'next' ? 'ASC' : 'DESC';
    $stmt = $db->prepare("SELECT * FROM expositions WHERE is_active = 1 AND display_order $op (SELECT display_order FROM expositions WHERE id = ?) ORDER BY display_order $order LIMIT 1");
    $stmt->execute([$currentId]);
    return $stmt->fetch();
}

// Get timeline events
function getTimelineEvents() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM timeline_events ORDER BY year DESC, display_order ASC");
    return $stmt->fetchAll();
}

// Get banner slides
function getBannerSlides() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM banner_slides WHERE is_active = 1 ORDER BY display_order ASC");
    return $stmt->fetchAll();
}

// Generate slug from string
function generateSlug($string) {
    $slug = mb_strtolower(trim($string), 'UTF-8');
    // Transliterate special characters
    $transliteration = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'ç' => 'c', 'ñ' => 'n', 'ß' => 'ss',
        'œ' => 'oe', 'Œ' => 'oe', 'æ' => 'ae',
    ];
    $slug = strtr($slug, $transliteration);
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// Upload image
function uploadImage($file, $folder = 'oeuvres') {
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.'];
    }

    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        return ['error' => 'Le fichier est trop volumineux (max 10MB).'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => 'uploads/' . $folder . '/' . $filename];
    }

    return ['error' => 'Erreur lors de l\'upload du fichier.'];
}

// Delete image file
function deleteImage($path) {
    $fullPath = __DIR__ . '/../' . $path;
    if (file_exists($fullPath) && is_file($fullPath)) {
        unlink($fullPath);
    }
}

// Sanitize output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Get base URL
function baseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // Navigate to project root
    $basePath = '/ProjetNico';
    return $protocol . '://' . $host . $basePath;
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Require admin login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . baseUrl() . '/admin/login.php');
        exit;
    }
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get Google Fonts URL from settings
function getGoogleFontsUrl($settings) {
    $heading = str_replace(' ', '+', $settings['font_heading'] ?? 'Cormorant Garamond');
    $body = str_replace(' ', '+', $settings['font_body'] ?? 'Inter');
    return "https://fonts.googleapis.com/css2?family={$heading}:wght@300;400;500;600;700&family={$body}:wght@300;400;500;600&display=swap";
}
