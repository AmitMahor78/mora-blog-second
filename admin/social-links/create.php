
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$platform = '';
$url = '';
$status = 'active';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platform = trim($_POST['platform'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($platform === '' || $url === '') {
        $error = "Platform name and URL are required.";
    } elseif (strlen($platform) > 50) {
        $error = "Platform name must not exceed 50 characters.";
    } elseif (strlen($url) > 500) {
        $error = "URL must not exceed 500 characters.";
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "Please enter a valid URL.";
    } elseif (!in_array(
        strtolower((string)parse_url($url, PHP_URL_SCHEME)),
        ['http', 'https'],
        true
    )) {
        $error = "Only HTTP and HTTPS URLs are allowed.";
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Please select a valid status.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO social_links (platform, url, status)
             VALUES (?, ?, ?)"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $platform,
                $url,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: index.php?success=created");
                exit;
            } else {
                $error = "Unable to save the social link.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Database query could not be prepared.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Social Link | Mora Blog</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .topbar {
            margin-left: 260px;
            padding: 20px 25px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        .topbar h2 { margin: 0; font-size: 23px; }

        .container {
            margin-left: 260px;
            padding: 25px;
        }

        .form-card {
            max-width: 700px;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 15px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save { background: #198754; }
        .btn-back { background: #6c757d; }

        .error {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 5px;
            color: #842029;
            background: #f8d7da;
        }

        .help {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #666;
        }

        @media (max-width: 768px) {
            .topbar, .container { margin-left: 220px; }
        }

        @media (max-width: 480px) {
            .topbar, .container {
                margin-left: 200px;
                padding: 15px 10px;
            }

            .form-card { padding: 18px; }
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<header class="topbar">
    <h2>Add Social Link</h2>
</header>

<main class="container">
    <div class="form-card">

        <h3>Social Link Details</h3>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST">

            <div class="form-group">
                <label for="platform">Platform Name</label>
                <input
                    type="text"
                    id="platform"
                    name="platform"
                    maxlength="50"
                    placeholder="e.g. Facebook, Instagram, YouTube"
                    value="<?= htmlspecialchars($platform); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="url">Social Media URL</label>
                <input
                    type="url"
                    id="url"
                    name="url"
                    maxlength="500"
                    placeholder="https://www.instagram.com/yourpage"
                    value="<?= htmlspecialchars($url); ?>"
                    required
                >
                <span class="help">URL must start with http:// or https://.</span>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>
                        Active
                    </option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : ''; ?>>
                        Inactive
                    </option>
                </select>
            </div>

            <button type="submit" class="btn btn-save">Save Social Link</button>
            <a href="index.php" class="btn btn-back">Cancel</a>

        </form>
    </div>
</main>
</body>
</html>
