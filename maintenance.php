
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$siteName = 'Mora Blog';

$stmt = $conn->prepare(
    "SELECT site_name FROM site_settings WHERE id = 1 LIMIT 1"
);

if ($stmt) {
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!empty($row['site_name'])) {
        $siteName = $row['site_name'];
    }

    $stmt->close();
}

function maintenanceEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance | <?= maintenanceEscape($siteName) ?></title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #252b36;
            text-align: center;
        }

        .maintenance-card {
            width: 100%;
            max-width: 600px;
            padding: 45px 30px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0,0,0,.08);
        }

        .maintenance-icon {
            font-size: 54px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0 0 16px;
            font-size: clamp(28px, 5vw, 40px);
        }

        p {
            color: #626b78;
            line-height: 1.8;
        }

        a {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 22px;
            border-radius: 7px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main class="maintenance-card">
        <div class="maintenance-icon" aria-hidden="true">🛠️</div>

        <h1>We'll Be Back Soon!</h1>

        <p>
            <?= maintenanceEscape($siteName) ?> is temporarily undergoing
            maintenance to improve your experience.
        </p>

        <p>Thank you for your patience. Please visit us again shortly.</p>

       
    </main>
</body>
</html>
