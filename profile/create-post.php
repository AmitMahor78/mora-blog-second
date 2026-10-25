<?php
session_start();
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../includes/db.php';

$userId = (int) $_SESSION['registered_user_id'];
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = mysqli_prepare($conn, "SELECT can_post, status FROM registered_users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $canPost, $accountStatus);
$accountFound = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$accountFound || $accountStatus !== 'active') {
    $_SESSION = [];
    session_destroy();
    header("Location: ../login.php");
    exit;
}
if ((int)$canPost !== 1) {
    http_response_code(403);
    exit('Posting permission has not been enabled for your account. Please contact the administrator.');
}

$categories = [];
$categoryResult = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
if ($categoryResult) {
    while ($category = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $category;
    }
}

$error = '';
$title = '';
$content = '';
$categoryId = '';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Refresh the page and try again.';
    } elseif ($title === '' || $content === '' || $categoryId < 1) {
        $error = 'Please enter the title, content and category.';
    } elseif (mb_strlen($title) > 255) {
        $error = 'Title must be 255 characters or fewer.';
    } else {
        $categoryStmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($categoryStmt, "i", $categoryId);
        mysqli_stmt_execute($categoryStmt);
        mysqli_stmt_store_result($categoryStmt);
        $categoryExists = mysqli_stmt_num_rows($categoryStmt) === 1;
        mysqli_stmt_close($categoryStmt);

        if (!$categoryExists) {
            $error = 'Please select a valid category.';
        } else {
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Image upload failed. Please try again.';
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $error = 'Image size must be 5 MB or less.';
                } else {
                    $tmp = $_FILES['image']['tmp_name'];
                    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
                    $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'image/gif'=>'gif'];
                    if (!isset($allowed[$mime]) || !getimagesize($tmp)) {
                        $error = 'Upload a valid JPG, PNG, WEBP or GIF image.';
                    } else {
                        $uploadDir = __DIR__ . '/../uploads/posts/';
                        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                            $error = 'Could not create the post image directory.';
                        } else {
                            $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
                            if (move_uploaded_file($tmp, $uploadDir . $fileName)) {
                                $imagePath = 'uploads/posts/' . $fileName;
                            } else {
                                $error = 'Could not save the image.';
                            }
                        }
                    }
                }
            }

           
if ($error === '') {

    // Generate a URL-friendly slug from the title.
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

    $slugBase = preg_replace('/[^a-z0-9]+/', '-', $slugBase);
    $slugBase = trim($slugBase, '-');

    if ($slugBase === '') {
        $slugBase = 'post';
    }

    // Ensure the slug is unique.
    $slug = $slugBase;
    $suffix = 2;

    $slugStmt = mysqli_prepare(
        $conn,
        "SELECT id FROM posts WHERE slug = ? LIMIT 1"
    );

    if (!$slugStmt) {
        $error = 'Could not prepare the slug query.';
    } else {
        while (true) {
            mysqli_stmt_bind_param($slugStmt, "s", $slug);
            mysqli_stmt_execute($slugStmt);
            mysqli_stmt_store_result($slugStmt);

            $slugExists = mysqli_stmt_num_rows($slugStmt) > 0;

            mysqli_stmt_free_result($slugStmt);

            if (!$slugExists) {
                break;
            }

            $slug = $slugBase . '-' . $suffix;
            $suffix++;
        }

        mysqli_stmt_close($slugStmt);
    }

    if ($error === '') {
        // User posts start as pending and require admin approval.
        $sql = "INSERT INTO posts
                (title, slug, content, category_id, author_id,
                 registered_user_id, image, status)
                VALUES (?, ?, ?, ?, NULL, ?, ?, 'pending')";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssiis",
                $title,
                $slug,
                $content,
                $categoryId,
                $userId,
                $imagePath
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header("Location: my-posts.php?created=1");
                exit;
            }

            $error = 'Could not save the post. Please try again.';
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Could not prepare the post. Check the posts table schema.';
        }
    }

    // Remove an uploaded image if saving the post failed.
    if (
        $error !== '' &&
        $imagePath !== '' &&
        is_file(__DIR__ . '/../' . $imagePath)
    ) {
        @unlink(__DIR__ . '/../' . $imagePath);
    }
}

        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Post | Mora Blog</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body{background:#f5f7fb}.wrap{max-width:900px;margin:40px auto;padding:0 16px}.panel{background:#fff;padding:26px;border-radius:12px;box-shadow:0 4px 18px #0000000a}
label{display:block;font-weight:600;margin:16px 0 7px}input,select,textarea{width:100%;padding:12px;border:1px solid #d9dee8;border-radius:6px;box-sizing:border-box}textarea{min-height:260px;resize:vertical}.btn{display:inline-block;background:#1769ff;color:#fff!important;padding:11px 17px;border:0;border-radius:6px;text-decoration:none!important;cursor:pointer;margin-top:18px}.btn.light{background:#e9eef8;color:#222!important;margin-left:8px}.error{background:#f8d7da;color:#721c24;padding:12px;border-radius:6px}
</style>
</head>
<body>
<div class="wrap"><div class="panel">
<h1>Create a Blog Post</h1>
<p>Your post will remain pending until the administrator approves it.</p>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<label for="title">Post Title</label>
<input id="title" name="title" maxlength="255" required value="<?= e($title) ?>">
<label for="category_id">Category</label>
<select id="category_id" name="category_id" required>
<option value="">Select category</option>
<?php foreach ($categories as $category): ?>
<option value="<?= (int)$category['id'] ?>" <?= (string)$categoryId === (string)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
<?php endforeach; ?>
</select>
<label for="content">Content</label>
<textarea id="content" name="content" required><?= e($content) ?></textarea>
<label for="image">Featured Image (optional, max 5 MB)</label>
<input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
<button class="btn" type="submit">Submit for Approval</button>
<a class="btn light" href="my-posts.php">Cancel</a>
</form>
</div></div>
</body>
</html>
