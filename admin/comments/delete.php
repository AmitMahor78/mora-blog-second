<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";


$commentId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;


if ($commentId <= 0) {
    header("Location: index.php");
    exit;
}


/*
    Check whether comment exists
*/
$checkQuery = "
    SELECT id
    FROM comments
    WHERE id = $commentId
    LIMIT 1
";

$checkResult = mysqli_query($conn, $checkQuery);


if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
    header("Location: index.php");
    exit;
}


/*
    Delete comment
*/
$deleteQuery = "
    DELETE FROM comments
    WHERE id = $commentId
";


if (mysqli_query($conn, $deleteQuery)) {

    header("Location: index.php?success=deleted");
    exit;
}


header("Location: index.php?error=delete_failed");
exit;
?>