
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$name = '';
$price = '';
$image = '';
$category = '';
$description = '';
$status = 'active';
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
    } elseif (!is_numeric($price) || (float)$price < 0 || (float)$price > 99999999.99) {
        $error = "Please enter a valid product price.";
    } elseif (strlen($category) > 100) {
        $error = "Category must not exceed 100 characters.";
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Please select a valid status.";
    } elseif (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {
        $error = "Please select a product image.";
    } else {
        $file = $_FILES['image'];

        if ($file['size'] > 5 * 1024 * 1024) {
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

            if (!isset($allowedTypes[$mime])) {
                $error = "Only JPG, PNG, WEBP and GIF images are allowed.";
            } elseif (@getimagesize($file['tmp_name']) === false) {
                $error = "The uploaded file is not a valid image.";
            } else {
                $uploadDir = "../../uploads/products/";

                if (
                    !is_dir($uploadDir) &&
                    !mkdir($uploadDir, 0755, true) &&
                    !is_dir($uploadDir)
                ) {
                    $error = "Unable to create the image upload directory.";
                } else {
                    $extension = $allowedTypes[$mime];
                    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
                    $destination = $uploadDir . $fileName;

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $error = "Unable to upload the product image.";
                    } else {
                        $image = 'uploads/products/' . $fileName;

                        $stmt = mysqli_prepare(
                            $conn,
                            "INSERT INTO products
                            (name, price, image, category, description, status)
                            VALUES (?, ?, ?, ?, ?, ?)"
                        );

                        if ($stmt) {
                            $numericPrice = (float)$price;

                            mysqli_stmt_bind_param(
                                $stmt,
                                "sdssss",
                                $name,
                                $numericPrice,
                                $image,
                                $category,
                                $description,
                                $status
                            );

                            if (mysqli_stmt_execute($stmt)) {
                                mysqli_stmt_close($stmt);
                                header("Location: index.php?success=created");
                                exit;
                            }

                            mysqli_stmt_close($stmt);
                        }

                        // Remove the uploaded file if the database insert fails.
                        if (is_file($destination)) {
                            unlink($destination);
                        }

                        $image = '';
                        $error = "Unable to save the product. Please try again.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Mora Blog</title>

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

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save { background: #198754; }
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
    <h2>Add Product</h2>
</header>

<main class="container">
    <div class="form-card">
        <h3>Product Details</h3>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="150"
                    value="<?= htmlspecialchars($name); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="price">Price (₹) *</label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    value="<?= htmlspecialchars($price); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Category *</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    maxlength="100"
                    placeholder="e.g. Electronics, Fashion, Books"
                    value="<?= htmlspecialchars($category); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="image">Product Image *</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,.gif"
                    required
                >
                <span class="help">Allowed: JPG, PNG, WEBP, GIF. Maximum size: 5 MB.</span>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Enter product description..."
                ><?= htmlspecialchars($description); ?></textarea>
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

            <button type="submit" class="btn btn-save">Save Product</button>
            <a href="index.php" class="btn btn-back">Cancel</a>

        </form>
    </div>
</main>
</body>
</html>
