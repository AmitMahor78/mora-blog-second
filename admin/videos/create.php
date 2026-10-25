<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$error = "";

$categoriesQuery = "
    SELECT id, name
    FROM categories
    ORDER BY name ASC
";

$categoriesResult = mysqli_query($conn, $categoriesQuery);

$authorsQuery = "
    SELECT id, name
    FROM users
    WHERE role = 'author'
    ORDER BY name ASC
";

$authorsResult = mysqli_query($conn, $authorsQuery);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $authorId = !empty($_POST['author_id']) ? (int)$_POST['author_id'] : null;
    $status = $_POST['status'] ?? 'active';

    if ($title === '') {
        $error = "Video title is required.";
    } elseif ($videoUrl === '') {
        $error = "Video URL is required.";
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Invalid status.";
    } elseif (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
        $error = "Thumbnail image is required.";
    } else {

        $uploadDir = "../../uploads/videos/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        $originalName = $_FILES['thumbnail']['name'];
        $tmpName = $_FILES['thumbnail']['tmp_name'];
        $fileSize = $_FILES['thumbnail']['size'];

        $extension = strtolower(
            pathinfo($originalName, PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowedExtensions, true)) {

            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } elseif ($fileSize > 5 * 1024 * 1024) {

            $error = "Thumbnail size must be less than 5MB.";

        } else {

            $uniqueName = uniqid('video_', true) . '.' . $extension;

            $uploadPath = $uploadDir . $uniqueName;

            $dbThumbnailPath = "uploads/videos/" . $uniqueName;

            if (move_uploaded_file($tmpName, $uploadPath)) {

                $titleSafe = mysqli_real_escape_string($conn, $title);
                $descriptionSafe = mysqli_real_escape_string($conn, $description);
                $videoUrlSafe = mysqli_real_escape_string($conn, $videoUrl);
                $thumbnailSafe = mysqli_real_escape_string($conn, $dbThumbnailPath);
                $statusSafe = mysqli_real_escape_string($conn, $status);

                $categoryValue = $categoryId !== null
                    ? $categoryId
                    : "NULL";

                $authorValue = $authorId !== null
                    ? $authorId
                    : "NULL";

                $insertQuery = "
                    INSERT INTO videos
                    (
                        title,
                        description,
                        thumbnail,
                        video_url,
                        category_id,
                        author_id,
                        status
                    )
                    VALUES
                    (
                        '$titleSafe',
                        '$descriptionSafe',
                        '$thumbnailSafe',
                        '$videoUrlSafe',
                        $categoryValue,
                        $authorValue,
                        '$statusSafe'
                    )
                ";

                if (mysqli_query($conn, $insertQuery)) {

                    header("Location: index.php?success=Video added successfully");
                    exit;

                } else {

                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }

                    $error = "Database error: " . mysqli_error($conn);
                }

            } else {

                $error = "Failed to upload thumbnail.";
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

    <title>Add Video - Admin</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        button,
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #28a745;
            color: white;
        }

        .back-btn {
            background: #6c757d;
            color: white;
        }

        .error {
            color: #dc3545;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Add Video</h1>

    <?php if ($error !== ''): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Video Title</label>

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
            required
        >


        <label>Description</label>

        <textarea name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>


        <label>Thumbnail</label>

        <input
            type="file"
            name="thumbnail"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >


        <label>Video URL</label>

        <input
            type="url"
            name="video_url"
            value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>"
            placeholder="https://www.youtube.com/watch?v=..."
            required
        >


        <label>Category</label>

        <select name="category_id">

            <option value="">Select Category</option>

            <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>

                <option
                    value="<?= (int)$category['id'] ?>"
                    <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($category['name']) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <label>Author</label>

        <select name="author_id">

            <option value="">Select Author</option>

            <?php while ($author = mysqli_fetch_assoc($authorsResult)): ?>

                <option
                    value="<?= (int)$author['id'] ?>"
                    <?= (isset($_POST['author_id']) && $_POST['author_id'] == $author['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($author['name']) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <label>Status</label>

        <select name="status">

            <option value="active">Active</option>

            <option
                value="inactive"
                <?= (($_POST['status'] ?? '') === 'inactive') ? 'selected' : '' ?>
            >
                Inactive
            </option>

        </select>


        <button type="submit">
            Add Video
        </button>

        <a href="index.php" class="back-btn">
            Back
        </a>

    </form>

</div>

</body>
</html>