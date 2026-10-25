
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

// First, retrieve the product image path.
$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM products WHERE id = ?"
);

if (!$stmt) {
    die("Unable to prepare product query.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: index.php");
    exit;
}

// Delete the product record using a prepared statement.
$deleteStmt = mysqli_prepare(
    $conn,
    "DELETE FROM products WHERE id = ?"
);

if (!$deleteStmt) {
    die("Unable to prepare delete query.");
}

mysqli_stmt_bind_param($deleteStmt, "i", $id);
$success = mysqli_stmt_execute($deleteStmt);
$deletedRows = mysqli_stmt_affected_rows($deleteStmt);

mysqli_stmt_close($deleteStmt);

if ($success && $deletedRows > 0) {
    // Delete only images stored in the product uploads directory.
    $image = str_replace('\\', '/', (string)$product['image']);

    if (str_starts_with($image, 'uploads/products/')) {
        $imagePath = "../../" . $image;

        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: index.php?success=deleted");
    exit;
}

header("Location: index.php");
exit;
?>
