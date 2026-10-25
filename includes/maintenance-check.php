<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$requestPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '/', PHP_URL_PATH) ?: '/';
$fileName = basename($requestPath);

// Allow admin pages and the login/maintenance pages.
$isAdminPage = strpos($requestPath, '/admin/') !== false;

$allowedPages = [
    'login.php',
    'register.php',
    'maintenance.php'
];

if ($isAdminPage || in_array($fileName, $allowedPages, true)) {
    return;
}

require_once __DIR__ . '/db.php';

$stmt = $conn->prepare(
    "SELECT maintenance_mode FROM site_settings WHERE id = 1 LIMIT 1"
);

if (!$stmt) {
    // Do not take the whole website offline if the check fails.
    return;
}

$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ((int)($result['maintenance_mode'] ?? 0) === 1) {
    header('Location: /mora-blog-second/maintenance.php', true, 302);
    exit;
}
