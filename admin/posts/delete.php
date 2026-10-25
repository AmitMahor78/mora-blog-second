<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    header("Location: index.php");
    exit;
}


/* Get post image before deleting */

$query = "
    SELECT image
    FROM posts
    WHERE id = $postId
";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit;
}

$post = mysqli_fetch_assoc($result);


/* Delete post */

$deleteQuery = "
    DELETE FROM posts
    WHERE id = $postId
";

if (mysqli_query($conn, $deleteQuery)) {

    /* Delete uploaded image */

    if (
        !empty($post['image']) &&
        strpos($post['image'], 'uploads/posts/') === 0
    ) {

        $imagePath = "../../" . $post['image'];

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: index.php?success=deleted");
    exit;

}


header("Location: index.php?error=delete_failed");
exit;
?>