<?php
// Comprehensive Diagnostic Script for Live InfinityFree Deployment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mora Blog - Live Diagnostic</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f0f2f5; padding: 30px 15px; margin: 0; }
        .box { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 25px 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #1a1d20; border-bottom: 2px solid #eee; padding-bottom: 12px; }
        .item { padding: 12px 15px; border-radius: 6px; margin: 12px 0; font-size: 14px; }
        .ok { background: #d1e7dd; color: #0f5132; border-left: 5px solid #198754; }
        .bad { background: #f8d7da; color: #842029; border-left: 5px solid #dc3545; }
        .warn { background: #fff3cd; color: #664d03; border-left: 5px solid #ffc107; }
        code { background: rgba(0,0,0,0.06); padding: 2px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
<div class="box">
    <h2>Mora Blog Deployment Diagnostic</h2>

    <!-- 1. PHP Version -->
    <div class="item ok">
        <strong>1. PHP Environment:</strong> PHP <?php echo PHP_VERSION; ?> running on <?php echo php_uname('s'); ?>
    </div>

    <!-- 2. Vendor / PHPMailer Check -->
    <?php
    $vendorPath = __DIR__ . '/vendor/autoload.php';
    if (file_exists($vendorPath)) {
        try {
            require_once $vendorPath;
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                echo '<div class="item ok"><strong>2. Composer & PHPMailer:</strong> Installed and autoloaded properly.</div>';
            } else {
                echo '<div class="item warn"><strong>2. Composer Autoload:</strong> Found, but PHPMailer class is missing inside vendor.</div>';
            }
        } catch (\Throwable $e) {
            echo '<div class="item bad"><strong>2. Vendor Autoload Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        echo '<div class="item bad"><strong>2. Vendor Missing:</strong> <code>vendor/autoload.php</code> was not found! Please upload the <code>vendor/</code> folder to your server.</div>';
    }
    ?>

    <!-- 3. Database Check -->
    <?php
    $host = "sql305.infinityfree.com";
    $user = "if0_43165092";
    $password = "Amit7879";
    $database = "if0_43165092_morablogsecond";

    echo "<p style='color:#666; font-size:13px; margin: 15px 0 5px;'>Testing MySQL connection to <code>{$host}</code> as <code>{$user}</code>...</p>";

    $conn = @mysqli_connect($host, $user, $password, $database);

    if (!$conn) {
        $dbErr = mysqli_connect_error();
        echo '<div class="item bad">';
        echo '<strong>3. Database Connection: FAILED</strong><br>';
        echo '<strong>Error Details:</strong> ' . htmlspecialchars($dbErr) . '<br><br>';

        if (stripos($dbErr, 'access denied') !== false) {
            echo '<strong>Why this happens:</strong> The password <code>' . htmlspecialchars($password) . '</code> was rejected by InfinityFree.<br>';
            echo '<strong>How to fix:</strong> Go to <strong>InfinityFree Client Area &rarr; Manage Account &rarr; Account Details &rarr; Password</strong>. Copy that generated vPanel password and paste it into <code>includes/db.php</code>.';
        } elseif (stripos($dbErr, 'Unknown database') !== false) {
            echo '<strong>Why this happens:</strong> The database <code>' . htmlspecialchars($database) . '</code> does not exist.<br>';
            echo '<strong>How to fix:</strong> Go to InfinityFree Control Panel &rarr; MySQL Databases &rarr; Create Database.';
        }
        echo '</div>';
    } else {
        echo '<div class="item ok"><strong>3. Database Connection: SUCCESS!</strong> Connected to <code>' . htmlspecialchars($database) . '</code></div>';

        // Check tables
        $tablesRes = @mysqli_query($conn, "SHOW TABLES LIKE 'registered_users'");
        if ($tablesRes && mysqli_num_rows($tablesRes) > 0) {
            echo '<div class="item ok"><strong>4. Database Tables:</strong> Table <code>registered_users</code> exists.</div>';
        } else {
            echo '<div class="item bad"><strong>4. Database Tables: Missing!</strong> Table <code>registered_users</code> does not exist.<br>Please import <code>mora_blog_database.sql</code> into phpMyAdmin on InfinityFree.</div>';
        }
        mysqli_close($conn);
    }
    ?>

    <p style="margin-top:25px; text-align:center;">
        <a href="test-email.php" style="color:#0d6efd; text-decoration:none; font-weight:bold;">&rarr; Test Live Email Delivery (test-email.php)</a>
    </p>
</div>
</body>
</html>