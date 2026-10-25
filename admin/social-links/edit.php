
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, platform, url, status
     FROM social_links
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$socialLink = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$socialLink) {
    header("Location: index.php");
    exit;
}

$platform = $socialLink['platform'];
$url = $socialLink['url'];
$status = $socialLink['status'];
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
        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE social_links
             SET platform = ?, url = ?, status = ?
             WHERE id = ?"
        );

        if ($updateStmt) {
            mysqli_stmt_bind_param(
                $updateStmt,
                "sssi",
                $platform,
                $url,
                $status,
                $id
            );

            if (mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                header("Location: index.php?success=updated");
                exit;
            } else {
                $error = "Unable to update the social link.";
            }

            mysqli_stmt_close($updateStmt);
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
    <title>Edit Social Link | Mora Blog</title>

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

        .btn-save { background: #0d6efd; }
        .btn-back { background: #6c757d; }

        .error {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 5px;
            color: #842029;
            background: #f8d7da;
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
    <h2>Edit Social Link</h2>
</header>

<main class="container">
    <div class="form-card">

        <h3>Update Social Link</h3>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int)$id; ?>" method="POST">

            <div class="form-group">
                <label for="platform">Platform Name</label>
                <input
                    type="text"
                    id="platform"
                    name="platform"
                    maxlength="50"
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
                    value="<?= htmlspecialchars($url); ?>"
                    required
                >
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

            <button type="submit" class="btn btn-save">Update Social Link</button>
            <a href="index.php" class="btn btn-back">Cancel</a>

        </form>
    </div>
</main>
</body>
</html>
