<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";
include "../../includes/functions.php";

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    header("Location: index.php");
    exit;
}


/* Fetch post */

$query = "
    SELECT *
    FROM posts
    WHERE id = $postId
";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit;
}

$post = mysqli_fetch_assoc($result);


/* Categories */

$categoryQuery = "
    SELECT id, name
    FROM categories
    ORDER BY name ASC
";

$categoryResult = mysqli_query($conn, $categoryQuery);


/* Authors */

$authorQuery = "
    SELECT id, name
    FROM users
    WHERE role = 'author'
    ORDER BY name ASC
";

$authorResult = mysqli_query($conn, $authorQuery);

$error = "";


/* Update post */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title']);
    $categoryId = intval($_POST['category_id']);
    $authorId = intval($_POST['author_id']);
    $content = trim($_POST['content']);
    $status = $_POST['status'];

    if ($title === "" || $categoryId <= 0 || $authorId <= 0 || $content === "") {

        $error = "Please fill all required fields.";

    } else {

        $newImagePath = $post['image'];
        $uploadedNewImage = false;


        /* New image upload */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {

                $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

                $fileName = $_FILES['image']['name'];
                $fileSize = $_FILES['image']['size'];
                $tmpName = $_FILES['image']['tmp_name'];

                $extension = strtolower(
                    pathinfo($fileName, PATHINFO_EXTENSION)
                );

                if (!in_array($extension, $allowedTypes)) {

                    $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

                } elseif ($fileSize > 5 * 1024 * 1024) {

                    $error = "Image size must be less than 5 MB.";

                } else {

                    $uploadDirectory = "../../uploads/posts/";

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    $newFileName = uniqid("post_", true) . "." . $extension;

                    $uploadPath = $uploadDirectory . $newFileName;

                    if (move_uploaded_file($tmpName, $uploadPath)) {

                        $newImagePath = "uploads/posts/" . $newFileName;

                        $uploadedNewImage = true;

                    } else {

                        $error = "Failed to upload new image.";
                    }
                }

            } else {

                $error = "Image upload failed.";
            }
        }


        if ($error === "") {

            $slug = !empty($post['slug']) ? $post['slug'] : create_unique_slug($conn, 'posts', $title, $postId);
            $excerpt = generate_excerpt($content);

            $titleSafe = mysqli_real_escape_string($conn, $title);
            $slugSafe = mysqli_real_escape_string($conn, $slug);
            $excerptSafe = mysqli_real_escape_string($conn, $excerpt);
            $contentSafe = mysqli_real_escape_string($conn, $content);
            $imageSafe = mysqli_real_escape_string($conn, $newImagePath);
            $statusSafe = mysqli_real_escape_string($conn, $status);

            $updateQuery = "
                UPDATE posts
                SET
                    title = '$titleSafe',
                    slug = '$slugSafe',
                    excerpt = '$excerptSafe',
                    content = '$contentSafe',
                    category_id = $categoryId,
                    author_id = $authorId,
                    image = '$imageSafe',
                    status = '$statusSafe'
                WHERE id = $postId
            ";

            if (mysqli_query($conn, $updateQuery)) {

                /* Delete old uploaded image */

                if (
                    $uploadedNewImage &&
                    !empty($post['image']) &&
                    strpos($post['image'], 'uploads/posts/') === 0
                ) {

                    $oldImage = "../../" . $post['image'];

                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                header("Location: index.php?success=updated");
                exit;

            } else {

                /* Remove newly uploaded image if update failed */

                if ($uploadedNewImage) {

                    $newUploadedFile = "../../" . $newImagePath;

                    if (file_exists($newUploadedFile)) {
                        unlink($newUploadedFile);
                    }
                }

                $error = "Failed to update post.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Post - Admin Panel</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f6f9;
        }

        .topbar {
            background: #222;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
        }

        .topbar a {
            color: white;
            text-decoration: none;
            background: #dc3545;
            padding: 8px 14px;
            border-radius: 5px;
        }

        .container {
            max-width: 900px;
            margin: 35px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }

        h1 {
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            min-height: 250px;
            resize: vertical;
        }

        .current-image {
            margin-top: 10px;
        }

        .current-image img {
            width: 180px;
            border-radius: 5px;
        }

        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back {
            margin-left: 10px;
            text-decoration: none;
            color: #333;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="topbar">

    <strong>Admin Panel</strong>

    <a href="../logout.php">Logout</a>

</div>


<div class="container">

    <h1>Edit Post</h1>


    <?php if ($error !== ""): ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="form-group">

            <label>Post Title</label>

            <input
                type="text"
                name="title"
                value="<?php echo htmlspecialchars($post['title']); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Author</label>

            <select name="author_id" required>

                <?php while ($author = mysqli_fetch_assoc($authorResult)): ?>

                    <option
                        value="<?php echo $author['id']; ?>"
                        <?php
                        echo ($author['id'] == $post['author_id'])
                            ? 'selected'
                            : '';
                        ?>
                    >

                        <?php echo htmlspecialchars($author['name']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div class="form-group">

            <label>Category</label>

            <select name="category_id" required>

                <?php while ($category = mysqli_fetch_assoc($categoryResult)): ?>

                    <option
                        value="<?php echo $category['id']; ?>"
                        <?php
                        echo ($category['id'] == $post['category_id'])
                            ? 'selected'
                            : '';
                        ?>
                    >

                        <?php echo htmlspecialchars($category['name']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div class="form-group">

            <label>Current Image</label>

            <?php if (!empty($post['image'])): ?>

                <div class="current-image">

                    <img
                        src="../../<?php echo htmlspecialchars($post['image']); ?>"
                        alt="Post Image"
                    >

                </div>

            <?php else: ?>

                <p>No image available.</p>

            <?php endif; ?>

        </div>


        <div class="form-group">

            <label>Replace Image (Optional)</label>

            <input
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
            >

        </div>


        <div class="form-group">

            <label>Content</label>

            <textarea
                name="content"
                required
            ><?php echo htmlspecialchars($post['content']); ?></textarea>

        </div>


        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option
                    value="draft"
                    <?php echo ($post['status'] === 'draft') ? 'selected' : ''; ?>
                >
                    Draft
                </option>

                <option
                    value="published"
                    <?php echo ($post['status'] === 'published') ? 'selected' : ''; ?>
                >
                    Published
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn"
        >
            Update Post
        </button>

        <a
            href="index.php"
            class="back"
        >
            Cancel
        </a>

    </form>

</div>

</body>
</html>