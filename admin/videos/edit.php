<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($videoId <= 0) {
    header("Location: index.php?error=Invalid video ID");
    exit;
}

$videoQuery = "
    SELECT
        id,
        title,
        description,
        thumbnail,
        video_url,
        category_id,
        author_id,
        status
    FROM videos
    WHERE id = $videoId
    LIMIT 1
";

$videoResult = mysqli_query($conn, $videoQuery);

if (!$videoResult || mysqli_num_rows($videoResult) === 0) {
    header("Location: index.php?error=Video not found");
    exit;
}

$video = mysqli_fetch_assoc($videoResult);


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

$error = "";


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

    } else {

        $newThumbnailPath = $video['thumbnail'];
        $uploadedNewFile = false;
        $newUploadFullPath = "";

        if (
            isset($_FILES['thumbnail']) &&
            $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK
        ) {

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

                $newUploadFullPath = $uploadDir . $uniqueName;

                $newThumbnailPath = "uploads/videos/" . $uniqueName;

                if (move_uploaded_file($tmpName, $newUploadFullPath)) {

                    $uploadedNewFile = true;

                } else {

                    $error = "Failed to upload new thumbnail.";
                }
            }
        }

        if ($error === '') {

            $titleSafe = mysqli_real_escape_string($conn, $title);
            $descriptionSafe = mysqli_real_escape_string($conn, $description);
            $videoUrlSafe = mysqli_real_escape_string($conn, $videoUrl);
            $thumbnailSafe = mysqli_real_escape_string($conn, $newThumbnailPath);
            $statusSafe = mysqli_real_escape_string($conn, $status);

            $categoryValue = $categoryId !== null
                ? $categoryId
                : "NULL";

            $authorValue = $authorId !== null
                ? $authorId
                : "NULL";

            $updateQuery = "
                UPDATE videos
                SET
                    title = '$titleSafe',
                    description = '$descriptionSafe',
                    thumbnail = '$thumbnailSafe',
                    video_url = '$videoUrlSafe',
                    category_id = $categoryValue,
                    author_id = $authorValue,
                    status = '$statusSafe'
                WHERE id = $videoId
            ";

            if (mysqli_query($conn, $updateQuery)) {

                /*
                 * Delete old uploaded thumbnail only after
                 * successful database update.
                 */
                if (
                    $uploadedNewFile &&
                    !empty($video['thumbnail']) &&
                    strpos($video['thumbnail'], 'uploads/videos/') === 0
                ) {

                    $oldFile = "../../" . $video['thumbnail'];

                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                header("Location: index.php?success=Video updated successfully");
                exit;

            } else {

                if (
                    $uploadedNewFile &&
                    $newUploadFullPath !== "" &&
                    file_exists($newUploadFullPath)
                ) {
                    unlink($newUploadFullPath);
                }

                $error = "Database error: " . mysqli_error($conn);
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

    <title>Edit Video - Admin</title>

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
            background: #007bff;
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

        .current-thumbnail {
            margin-top: 10px;
            width: 180px;
            height: 110px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>

</head>

<body>

<div class="container">

    <h1>Edit Video</h1>

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
            value="<?= htmlspecialchars($_POST['title'] ?? $video['title']) ?>"
            required
        >


        <label>Description</label>

        <textarea name="description"><?= htmlspecialchars($_POST['description'] ?? $video['description']) ?></textarea>


        <label>Current Thumbnail</label>

        <?php if (!empty($video['thumbnail'])): ?>

            <br>

            <img
                src="../../<?= htmlspecialchars($video['thumbnail']) ?>"
                class="current-thumbnail"
                alt="Current Thumbnail"
            >

        <?php endif; ?>


        <label>Replace Thumbnail</label>

        <input
            type="file"
            name="thumbnail"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <small>
            Leave empty to keep the current thumbnail.
        </small>


        <label>Video URL</label>

        <input
            type="url"
            name="video_url"
            value="<?= htmlspecialchars($_POST['video_url'] ?? $video['video_url']) ?>"
            required
        >


        <label>Category</label>

        <select name="category_id">

            <option value="">Select Category</option>

            <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>

                <?php
                $selectedCategory = $_POST['category_id'] ?? $video['category_id'];
                ?>

                <option
                    value="<?= (int)$category['id'] ?>"
                    <?= ($selectedCategory == $category['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($category['name']) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <label>Author</label>

        <select name="author_id">

            <option value="">Select Author</option>

            <?php while ($author = mysqli_fetch_assoc($authorsResult)): ?>

                <?php
                $selectedAuthor = $_POST['author_id'] ?? $video['author_id'];
                ?>

                <option
                    value="<?= (int)$author['id'] ?>"
                    <?= ($selectedAuthor == $author['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($author['name']) ?>
                </option>

            <?php endwhile; ?>

        </select>


        <label>Status</label>

        <?php $selectedStatus = $_POST['status'] ?? $video['status']; ?>

        <select name="status">

            <option
                value="active"
                <?= $selectedStatus === 'active' ? 'selected' : '' ?>
            >
                Active
            </option>

            <option
                value="inactive"
                <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>
            >
                Inactive
            </option>

        </select>


        <button type="submit">
            Update Video
        </button>

        <a href="index.php" class="back-btn">
            Back
        </a>

    </form>

</div>

</body>
</html>