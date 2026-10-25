<?php

// Check if running in local development (XAMPP / Windows) or on a live Linux server
$is_local_machine = (
    strpos(__DIR__, 'xampp') !== false 
    || in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])
    || php_sapi_name() === 'cli'
);

if ($is_local_machine) {
    // Local XAMPP Settings
    $host     = "localhost";
    $user     = "root";
    $password = "Amit@7879";
    $database = "mora-blog-second";

    $conn = @mysqli_connect($host, $user, $password, $database);
    if (!$conn) {
        // Fallback to mora_blog if mora-blog-second is not selected
        $database = "mora_blog";
        $conn = @mysqli_connect($host, $user, $password, $database);
    }
} else {
    // Live Server Settings (InfinityFree)
    $host     = "sql305.infinityfree.com";
    $user     = "if0_43165092";
    $password = "Amit7879"; // NOTE: On InfinityFree, this MUST be your vPanel Hosting Password
    $database = "if0_43165092_morablogsecond";

    $conn = @mysqli_connect($host, $user, $password, $database);
}

if (!$conn) {
    // Show exact readable error so you know what is wrong instead of a blank HTTP 500
    die("<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:20px; border:2px solid #dc3545; border-radius:8px; background:#fff5f5;'>
        <h3 style='color:#dc3545; margin-top:0;'>Database Connection Failed</h3>
        <p><strong>Error:</strong> " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8') . "</p>
        <p><strong>Tip:</strong> If the error says <em>Access denied for user</em>, your database password is incorrect. On InfinityFree, the MySQL password is your <strong>vPanel Hosting Account Password</strong> (found under Account Details in your client area), NOT your website login password.</p>
    </div>");
}

mysqli_set_charset($conn, "utf8mb4");

?>