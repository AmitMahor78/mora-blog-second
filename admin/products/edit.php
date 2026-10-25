
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

function productImageUrl($image)
{
    $image = str_replace('\\', '/', trim((string)$image));

    if ($image === '' || preg_match('#^https?://#i', $image)) {
        return '';
    }

    $marker = '/mora-blog-second/';
    $position = stripos($image, $marker);

    if ($position !== false) {
        $image = substr($image, $position + strlen($marker));
    }

    if (str_starts_with($image, '/') || str_contains($image, '..')) {
        return '';
    }

    return '/' . ltrim($image, '/');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, price, image, category, description, status
     FROM products WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: index.php");
    exit;
}

$name = $product['name'];
$price = $product['price'];
$category = $product['category'];
$description = $product['description'] ?? '';
$status = $product['status'] ?? 'active';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($name === '' || $price === '' || $category === '') {
        $error = "Please fill all required fields.";
    } elseif (strlen($name) > 150) {
        $error = "Product name must not exceed 150 characters.";
    } elseif (
        !is_numeric($price) ||
        (float)$price < 0 ||
        (float)$price > 99999999.99
    ) {
        $error = "Please enter a valid product price.";
    } elseif (strlen($category) > 100) {
        $error = "Category must not exceed 100 characters.";
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Please select a valid status.";
    }

    $newImagePath = null;
    $newImageDestination = null;

    // Image replacement is optional.
    if ($error === '' && isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "There was a problem uploading the image.";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = "Image size must be less than 5 MB.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif'
            ];

            if (!isset($allowedTypes[$mime]) ||
                @getimagesize($file['tmp_name']) === false) {
                $error = "Please upload a valid JPG, PNG, WEBP or GIF image.";
            } else {
                $uploadDir = "../../uploads/products/";

                if (!is_dir($uploadDir) &&
                    !mkdir($uploadDir, 0755, true) &&
                    !is_dir($uploadDir)) {
                    $error = "Unable to create the image upload directory.";
                } else {
                    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
                    $newImageDestination = $uploadDir . $fileName;

                    if (!move_uploaded_file(
                        $file['tmp_name'],
                        $newImageDestination
                    )) {
                        $error = "Unable to upload the new image.";
                    } else {
                        $newImagePath = 'uploads/products/' . $fileName;
                    }
                }
            }
        }
    }

    if ($error === '') {
        $imageToSave = $newImagePath ?? $product['image'];

        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE products
             SET name = ?, price = ?, image = ?, category = ?,
                 description = ?, status = ?
             WHERE id = ?"
        );

        if ($updateStmt) {
            $numericPrice = (float)$price;

            mysqli_stmt_bind_param(
                $updateStmt,
                "sdssssi",
                $name,
                $numericPrice,
                $imageToSave,
                $category,
                $description,
                $status,
                $id
            );

            if (mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);

                // Remove the newly uploaded image if it replaced an older
                // image that belongs to our uploads/products directory.
                if ($newImagePath !== null) {
                    $oldImage = $product['image'];

                    if (str_starts_with($oldImage, 'uploads/products/')) {
                        $oldPath = "../../" . $oldImage;

                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }

                header("Location: index.php?success=updated");
                exit;
            } else {
                $error = "Unable to update the product.";
                mysqli_stmt_close($updateStmt);
            }
        } else {
            $error = "Database query could not be prepared.";
        }
    }

    // Clean up the newly uploaded file if saving failed.
    if ($error !== '' && $newImageDestination !== null &&
        is_file($newImageDestination)) {
        unlink($newImageDestination);
    }
}

$currentImage = productImageUrl($product['image']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Mora Blog</title>

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
            max-width: 800px;
            padding: 25px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 15px;
            font-family: inherit;
        }

        textarea { min-height: 120px; resize: vertical; }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }

        .current-image {
            display: block;
            width: 130px;
            height: 110px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 12px;
            background: white;
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
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 5px;
            color: #842029;
            background: #f8d7da;
        }

        .help {
            display: block;
            margin-top: 6px;
            color: #666;
            font-size: 12px;
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
    <h2>Edit Product</h2>
</header>

<main class="container">
    <div class="form-card">
        <h3>Update Product Details</h3>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int)$id; ?>"
              method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" maxlength="150"
                       value="<?= htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price (₹) *</label>
                <input type="number" id="price" name="price"
                       min="0" max="99999999.99" step="0.01"
                       value="<?= htmlspecialchars((string)$price); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category *</label>
                <input type="text" id="category" name="category"
                       maxlength="100"
                       value="<?= htmlspecialchars($category); ?>" required>
            </div>

            <div class="form-group">
                <label>Current Product Image</label>

                <?php if ($currentImage !== ''): ?>
                    <img class="current-image"
                         src="<?= htmlspecialchars($currentImage); ?>"
                         alt="Current product image">
                <?php else: ?>
                    <p>Current image preview is unavailable.</p>
                <?php endif; ?>

                <label for="image">Replace Image (Optional)</label>
                <input type="file" id="image" name="image"
                       accept=".jpg,.jpeg,.png,.webp,.gif">
                <span class="help">JPG, PNG, WEBP or GIF; maximum 5 MB.</span>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-save">Update Product</button>
            <a href="index.php" class="btn btn-back">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>
