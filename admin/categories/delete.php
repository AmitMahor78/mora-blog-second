<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";

$categoryId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($categoryId <= 0) {
    header("Location: index.php");
    exit;
}


/*
    Check whether category exists
*/
$categoryQuery = "
    SELECT id
    FROM categories
    WHERE id = $categoryId
    LIMIT 1
";

$categoryResult = mysqli_query($conn, $categoryQuery);

if (!$categoryResult || mysqli_num_rows($categoryResult) === 0) {
    header("Location: index.php");
    exit;
}


/*
    Check posts inside category
*/
$postQuery = "
    SELECT COUNT(*) AS total_posts
    FROM posts
    WHERE category_id = $categoryId
";

$postResult = mysqli_query($conn, $postQuery);

$postData = mysqli_fetch_assoc($postResult);

$totalPosts = intval($postData['total_posts']);


/*
    Do not delete category if posts exist
*/
if ($totalPosts > 0) {

    header("Location: index.php?error=has_posts");
    exit;
}


/*
    Delete category
*/
$deleteQuery = " 
    DELETE FROM categories
    WHERE id = $categoryId
";

if (mysqli_query($conn, $deleteQuery)) {

    header("Location: index.php?success=deleted");
    exit;

}


header("Location: index.php?error=delete_failed");
exit;
?>