<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";
include "../../includes/functions.php";

$message = "";
$error = "";

/* Fetch categories */
$categoryQuery = "SELECT id, name FROM categories ORDER BY name ASC";
$categoryResult = mysqli_query($conn, $categoryQuery);

/* Fetch authors */
$authorQuery = "
    SELECT id, name
    FROM users
    WHERE role = 'author'
    ORDER BY name ASC
";

$authorResult = mysqli_query($conn, $authorQuery);


/* Create post */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title']);
    $categoryId = intval($_POST['category_id']);
    $authorId = intval($_POST['author_id']);
    $content = trim($_POST['content']);
    $status = $_POST['status'];

    if ($title === "" || $categoryId <= 0 || $authorId <= 0 || $content === "") {

        $error = "Please fill all required fields.";

    } else {

        $imagePath = "";

        /* Image upload */

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

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

                        $imagePath = "uploads/posts/" . $newFileName;

                    } else {

                        $error = "Failed to upload image.";
                    }
                }

            } else {

                $error = "Image upload failed.";
            }
        }


        /* Insert post */

        if ($error === "") {

            $slug = create_unique_slug($conn, 'posts', $title);
            $excerpt = generate_excerpt($content);

            $titleSafe = mysqli_real_escape_string($conn, $title);
            $slugSafe = mysqli_real_escape_string($conn, $slug);
            $excerptSafe = mysqli_real_escape_string($conn, $excerpt);
            $contentSafe = mysqli_real_escape_string($conn, $content);
            $imageSafe = mysqli_real_escape_string($conn, $imagePath);
            $statusSafe = mysqli_real_escape_string($conn, $status);

            $query = "
                INSERT INTO posts
                (
                    title,
                    slug,
                    excerpt,
                    content,
                    category_id,
                    author_id,
                    image,
                    status
                )
                VALUES
                (
                    '$titleSafe',
                    '$slugSafe',
                    '$excerptSafe',
                    '$contentSafe',
                    $categoryId,
                    $authorId,
                    '$imageSafe',
                    '$statusSafe'
                )
            ";

            if (mysqli_query($conn, $query)) {

                header("Location: index.php?success=created");
                exit;

            } else {

                if ($imagePath !== "") {

                    $uploadedFile = "../../" . $imagePath;

                    if (file_exists($uploadedFile)) {
                        unlink($uploadedFile);
                    }
                }

                $error = "Failed to create post.";
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

    <title>Create Post - Admin Panel</title>

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
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
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

        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back {
            display: inline-block;
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

    <h1>Create New Post</h1>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">

            <label>Post Title</label>

            <input
                type="text"
                name="title"
                required
            >

        </div>


        <div class="form-group">

            <label>Author</label>

            <select name="author_id" required>

                <option value="">Select Author</option>

                <?php while ($author = mysqli_fetch_assoc($authorResult)): ?>

                    <option value="<?php echo $author['id']; ?>">

                        <?php echo htmlspecialchars($author['name']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div class="form-group">

            <label>Category</label>

            <select name="category_id" required>

                <option value="">Select Category</option>

                <?php while ($category = mysqli_fetch_assoc($categoryResult)): ?>

                    <option value="<?php echo $category['id']; ?>">

                        <?php echo htmlspecialchars($category['name']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div class="form-group">

            <label>Post Image</label>

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
            ></textarea>

        </div>


        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option value="draft">Draft</option>

                <option value="published">
                    Published
                </option>

            </select>

        </div>


        <button type="submit" class="btn">
            Create Post
        </button>

        <a href="index.php" class="back">
            Cancel
        </a>

    </form>

</div>

</body>
</html>