```php
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?error=" . urlencode("Invalid advertisement ID."));
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, image, link, status
     FROM advertisements WHERE id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ad = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$ad) {
    header("Location: index.php?error=" . urlencode("Advertisement not found."));
    exit;
}

$error = "";
$title = $ad['title'];
$link = $ad['link'];
$status = $ad['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($title === '') {
        $error = "Advertisement title is required.";
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Invalid status.";
    } elseif ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
        $error = "Please enter a valid URL, including https://.";
    } else {
        $newImage = $ad['image'];
        $newFullPath = "";
        $uploadedNew = false;

        if (isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error = "Image upload failed.";
            } else {
                $file = $_FILES['image'];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($extension, $allowed, true)) {
                    $error = "Only JPG, JPEG, PNG, WEBP and GIF images are allowed.";
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $error = "Image size must not exceed 5 MB.";
                } elseif (getimagesize($file['tmp_name']) === false) {
                    $error = "The selected file is not a valid image.";
                } else {
                    $uploadDir = "../../uploads/advertisements/";

                    if (!is_dir($uploadDir) &&
                        !mkdir($uploadDir, 0755, true) &&
                        !is_dir($uploadDir)) {
                        $error = "Could not create upload directory.";
                    } else {
                        $imageName = bin2hex(random_bytes(16)) . "." . $extension;
                        $newFullPath = $uploadDir . $imageName;
                        $newImage = "uploads/advertisements/" . $imageName;

                        if (move_uploaded_file($file['tmp_name'], $newFullPath)) {
                            $uploadedNew = true;
                        } else {
                            $error = "Could not save the uploaded image.";
                        }
                    }
                }
            }
        }

        if ($error === '') {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE advertisements
                 SET title = ?, image = ?, link = ?, status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt, "ssssi", $title, $newImage, $link, $status, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                // Delete the previous image only if it was stored
                // in this module's upload directory.
                if ($uploadedNew &&
                    strpos($ad['image'], 'uploads/advertisements/') === 0) {
                    $oldPath = "../../" . $ad['image'];

                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                header("Location: index.php?success=" .
                       urlencode("Advertisement updated successfully."));
                exit;
            }

            mysqli_stmt_close($stmt);

            if ($uploadedNew && is_file($newFullPath)) {
                unlink($newFullPath);
            }

            $error = "Database error while updating advertisement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Advertisement</title>

<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6fb; }

.topbar {
    margin-left: 260px; padding: 20px 25px; background: #fff;
    border-bottom: 1px solid #e5e7eb;
}
.topbar h1 { margin: 0; font-size: 24px; }
.container { margin-left: 260px; padding: 25px; }

.form-card {
    max-width: 850px; background: #fff; padding: 25px;
    border-radius: 12px; box-shadow: 0 3px 12px rgba(0,0,0,.05);
}

label { display: block; font-weight: 600; margin: 17px 0 7px; }

input[type="text"], input[type="url"], input[type="file"], select {
    width: 100%; padding: 12px; border: 1px solid #d1d5db;
    border-radius: 7px; background: #fff;
}

.preview {
    display: block; width: 220px; max-width: 100%;
    max-height: 150px; object-fit: contain;
    background: #f1f5f9; margin-top: 10px; border-radius: 6px;
}

.btn {
    display: inline-block; border: 0; border-radius: 6px;
    padding: 11px 17px; margin-top: 20px; cursor: pointer;
    text-decoration: none; color: #fff; font-size: 14px;
}
.save-btn { background: #2563eb; }
.back-btn { background: #64748b; }

.error {
    padding: 12px; background: #fee2e2; color: #991b1b;
    border-radius: 6px; margin-bottom: 15px;
}

@media(max-width: 768px) {
    .topbar, .container { margin-left: 220px; }
}
@media(max-width: 480px) {
    .topbar, .container { margin-left: 200px; padding: 12px; }
}
</style>
</head>
<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<header class="topbar">
    <h1>Edit Advertisement</h1>
</header>

<main class="container">
    <div class="form-card">

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="title">Advertisement Title</label>
            <input id="title" type="text" name="title"
                   value="<?= htmlspecialchars($title) ?>" required>

            <label>Current Image</label>
            <?php if (!empty($ad['image'])): ?>
                <img class="preview"
                     src="../../<?= htmlspecialchars($ad['image']) ?>"
                     alt="Current advertisement">
            <?php endif; ?>

            <label for="image">Replace Image (optional)</label>
            <input id="image" type="file" name="image"
                   accept=".jpg,.jpeg,.png,.webp,.gif">

            <label for="link">Advertisement Link</label>
            <input id="link" type="url" name="link"
                   value="<?= htmlspecialchars($link) ?>"
                   placeholder="https://example.com">

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button class="btn save-btn" type="submit">Update Advertisement</button>
            <a class="btn back-btn" href="index.php">Back</a>
        </form>
    </div>
</main>

</body>
</html>