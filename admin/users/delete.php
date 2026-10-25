
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM registered_users WHERE id = ?"
);

if (!$stmt) {
    die("Unable to prepare delete query.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
$success = mysqli_stmt_execute($stmt);
$deletedRows = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);

if ($success && $deletedRows > 0) {
    header("Location: index.php?success=deleted");
    exit;
}

if ($success) {
    header("Location: index.php");
    exit;
}

die("Unable to delete the registered user.");
?>
