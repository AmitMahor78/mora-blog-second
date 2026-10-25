<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

// Only logged-in registered users can edit their own posts.
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int) $_SESSION['registered_user_id'];
$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$postId || $postId < 1) {
    header("Location: my-posts.php");
    exit;
}

// CSRF protection.
if (empty($_SESSION['edit_post_csrf_token'])) {
    $_SESSION['edit_post_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['edit_post_csrf_token'];

$error = '';
$success = '';

// Fetch this user's post only.
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, slug, content, category_id, image, status
     FROM posts
     WHERE id = ? AND registered_user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Unable to prepare the post query.");
}

mysqli_stmt_bind_param($stmt, "ii", $postId, $userId);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $dbPostId,
    $dbTitle,
    $dbSlug,
    $dbContent,
    $dbCategoryId,
    $dbImage,
    $dbStatus
);

$postFound = mysqli_stmt_fetch($stmt);

if ($postFound) {
    $post = [
        'id' => $dbPostId,
        'title' => $dbTitle,
        'slug' => $dbSlug,
        'content' => $dbContent,
        'category_id' => $dbCategoryId,
        'image' => $dbImage,
        'status' => $dbStatus
    ];
} else {
    $post = null;
}

mysqli_stmt_close($stmt);

if (!$post) {
    http_response_code(404);
    die("Post not found, or you do not have permission to edit it.");
}

// Fetch categories.
$categories = [];

$categoryQuery = mysqli_query(
    $conn,
    "SELECT id, name FROM categories ORDER BY name ASC"
);

if ($categoryQuery) {
    while ($category = mysqli_fetch_assoc($categoryQuery)) {
        $categories[] = $category;
    }
} else {
    $error = "Unable to load categories.";
}

// Form values.
$title = $post['title'];
$content = $post['content'];
$categoryId = (int) $post['category_id'];

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrfToken, $submittedToken)) {
        $error = "Invalid request. Refresh the page and try again.";
    } else {

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $categoryId = filter_input(
            INPUT_POST,
            'category_id',
            FILTER_VALIDATE_INT
        );

        if ($title === '') {
            $error = "Please enter a post title.";
        } elseif (mb_strlen($title) > 255) {
            $error = "The title must not exceed 255 characters.";
        } elseif ($content === '') {
            $error = "Please enter the post content.";
        } elseif (!$categoryId || $categoryId < 1) {
            $error = "Please select a valid category.";
        }

        // Confirm that the selected category exists.
        if ($error === '') {
            $categoryExists = false;

            $checkCategory = mysqli_prepare(
                $conn,
                "SELECT id FROM categories WHERE id = ? LIMIT 1"
            );

            if ($checkCategory) {
                mysqli_stmt_bind_param(
                    $checkCategory,
                    "i",
                    $categoryId
                );

                mysqli_stmt_execute($checkCategory);
                mysqli_stmt_store_result($checkCategory);

                $categoryExists =
                    mysqli_stmt_num_rows($checkCategory) > 0;

                mysqli_stmt_close($checkCategory);
            }

            if (!$categoryExists) {
                $error = "The selected category does not exist.";
            }
        }

        // Generate a URL-friendly slug.
        $slug = '';

        if ($error === '') {
            $slugBase = strtolower($title);

            if (function_exists('iconv')) {
                $converted = iconv(
                    'UTF-8',
                    'ASCII//TRANSLIT//IGNORE',
                    $slugBase
                );

                if ($converted !== false) {
                    $slugBase = $converted;
                }
            }

            $slugBase = preg_replace(
                '/[^a-z0-9]+/',
                '-',
                $slugBase
            );

            $slugBase = trim($slugBase, '-');

            if ($slugBase === '') {
                $slugBase = 'post';
            }

            $slug = $slugBase;
            $suffix = 2;

            $slugStmt = mysqli_prepare(
                $conn,
                "SELECT id FROM posts
                 WHERE slug = ? AND id != ?
                 LIMIT 1"
            );

            if (!$slugStmt) {
                $error = "Unable to prepare the slug query.";
            } else {
                while (true) {
                    mysqli_stmt_bind_param(
                        $slugStmt,
                        "si",
                        $slug,
                        $postId
                    );

                    mysqli_stmt_execute($slugStmt);
                    mysqli_stmt_store_result($slugStmt);

                    $slugExists =
                        mysqli_stmt_num_rows($slugStmt) > 0;

                    mysqli_stmt_free_result($slugStmt);

                    if (!$slugExists) {
                        break;
                    }

                    $slug = $slugBase . '-' . $suffix;
                    $suffix++;
                }

                mysqli_stmt_close($slugStmt);
            }
        }

        // Keep the existing image unless a new image is uploaded.
        $imagePath = $post['image'];
        $newImagePath = '';
        $oldImagePath = $post['image'];

        // Process an optional new image.
        if (
            $error === '' &&
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error = "Image upload failed. Please try again.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = "Image size must not exceed 5 MB.";
            } else {
                $tmpFile = $_FILES['image']['tmp_name'];

                $imageInfo = @getimagesize($tmpFile);
                $mimeType = $imageInfo['mime'] ?? '';

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif'
                ];

                if (
                    !$imageInfo ||
                    !isset($allowedTypes[$mimeType])
                ) {
                    $error = "Upload a valid JPG, PNG, WEBP or GIF image.";
                } else {
                    $uploadDirectory = __DIR__ . '/../uploads/posts/';

                    if (
                        !is_dir($uploadDirectory) &&
                        !mkdir($uploadDirectory, 0755, true)
                    ) {
                        $error = "Unable to create the image upload directory.";
                    } else {
                        $fileName = bin2hex(random_bytes(16))
                            . '.'
                            . $allowedTypes[$mimeType];

                        $destination = $uploadDirectory . $fileName;

                        if (move_uploaded_file($tmpFile, $destination)) {
                            $newImagePath = 'uploads/posts/' . $fileName;
                            $imagePath = $newImagePath;
                        } else {
                            $error = "Unable to save the uploaded image.";
                        }
                    }
                }
            }
        }

        // Save the changes.
        if ($error === '') {

            // Any edited post must be reviewed again by the admin.
            $newStatus = 'pending';

            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE posts
                 SET title = ?,
                     slug = ?,
                     content = ?,
                     category_id = ?,
                     image = ?,
                     status = ?
                 WHERE id = ? AND registered_user_id = ?"
            );

            if (!$updateStmt) {
                $error = "Unable to prepare the update query.";
            } else {
                mysqli_stmt_bind_param(
                    $updateStmt,
                    "sssissii",
                    $title,
                    $slug,
                    $content,
                    $categoryId,
                    $imagePath,
                    $newStatus,
                    $postId,
                    $userId
                );

                if (mysqli_stmt_execute($updateStmt)) {
                    mysqli_stmt_close($updateStmt);

                    // Delete the previous image only after a successful update.
                    if (
                        $newImagePath !== '' &&
                        $oldImagePath !== ''
                    ) {
                        $oldImageFullPath =
                            __DIR__ . '/../' . $oldImagePath;

                        $newImageFullPath =
                            __DIR__ . '/../' . $newImagePath;

                        $normalOldPath = realpath($oldImageFullPath);
                        $normalNewPath = realpath($newImageFullPath);

                        if (
                            $normalOldPath !== false &&
                            $normalNewPath !== false &&
                            $normalOldPath !== $normalNewPath &&
                            strpos(
                                $normalOldPath,
                                realpath(__DIR__ . '/../uploads/posts/')
                            ) === 0 &&
                            is_file($normalOldPath)
                        ) {
                            @unlink($normalOldPath);
                        }
                    }

                    header("Location: my-posts.php?updated=1");
                    exit;

                } else {
                    $error = "Unable to update the post. Please try again.";
                    mysqli_stmt_close($updateStmt);
                }
            }
        }

        // Remove the newly uploaded image if the database update failed.
        if ($error !== '' && $newImagePath !== '') {
            $newImageFullPath = __DIR__ . '/../' . $newImagePath;

            if (is_file($newImageFullPath)) {
                @unlink($newImageFullPath);
            }
        }
    }
}

function editEscape($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Post | Mora Blog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f9;
            color: #222;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #172337;
            padding: 17px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .brand {
            color: white;
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .container {
            max-width: 850px;
            width: 92%;
            margin: 35px auto;
        }

        .heading {
            margin-bottom: 22px;
        }

        .heading h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .muted {
            color: #666;
            line-height: 1.5;
        }

        .form-card {
            background: white;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        input[type="text"],
        select,
        textarea,
        input[type="file"] {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd2dc;
            border-radius: 6px;
            font: inherit;
            background: white;
        }

        textarea {
            min-height: 260px;
            resize: vertical;
            line-height: 1.6;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #b7d3ff;
            border-color: #1769e0;
        }

        .current-image {
            width: 180px;
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
            display: block;
            margin: 10px 0;
            border-radius: 6px;
        }

        .help-text {
            display: block;
            margin-top: 7px;
            color: #666;
            font-size: 12px;
            line-height: 1.5;
        }

        .notice {
            background: #fff4d7;
            color: #785400;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 20px;
            line-height: 1.5;
            font-size: 14px;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .alert-error {
            background: #ffe3e3;
            color: #a32121;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        .button {
            border: none;
            border-radius: 5px;
            padding: 12px 18px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .save-button {
            background: #1769e0;
            color: white;
        }

        .cancel-button {
            background: #e7eaf0;
            color: #222;
        }

        .footer {
            text-align: center;
            padding: 25px 15px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 22px auto;
            }

            .form-card {
                padding: 18px;
            }

            .heading h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="brand" href="../index.php">Mora Blog</a>

    <nav class="nav-links">
        <a href="../index.php">Home</a>
        <a href="../profile.php">My Profile</a>
        <a href="my-posts.php">My Posts</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <div class="heading">
        <h1>Edit Post</h1>
        <p class="muted">
            Update your article details and save your changes.
        </p>
    </div>

    <div class="notice">
        <strong>Admin review:</strong>
        After you save your changes, the post will return to Pending
        status and must be approved by the admin before it appears publicly.
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error">
            <?= editEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="form-card">

        <form method="POST" enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= editEscape($csrfToken) ?>"
            >

            <div class="form-group">
                <label for="title">Post Title</label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="255"
                    value="<?= editEscape($title) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>

                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>

                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= (int) $category['id'] ?>"
                            <?= (int) $categoryId === (int) $category['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= editEscape($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Post Content</label>

                <textarea
                    id="content"
                    name="content"
                    required
                ><?= editEscape($content) ?></textarea>
            </div>

            <div class="form-group">
                <label>Current Image</label>

                <?php if (!empty($post['image'])): ?>
                    <img
                        class="current-image"
                        src="../<?= editEscape(ltrim($post['image'], '/')) ?>"
                        alt="Current post image"
                    >
                <?php else: ?>
                    <p class="muted">No image uploaded for this post.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Upload New Image (Optional)</label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                >

                <span class="help-text">
                    Accepted formats: JPG, PNG, WEBP and GIF.
                    Maximum size: 5 MB. Leave empty to keep the current image.
                </span>
            </div>

            <div class="buttons">
                <button type="submit" class="button save-button">
                    Save Changes
                </button>

                <a href="my-posts.php" class="button cancel-button">
                    Cancel
                </a>
            </div>

        </form>

    </section>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Mora Blog. All rights reserved.
</footer>

</body>
</html>