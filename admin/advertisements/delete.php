```php
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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?error=" . urlencode("Invalid advertisement ID."));
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM advertisements WHERE id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ad = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$ad) {
    header("Location: index.php?error=" . urlencode("Advertisement not found."));
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM advertisements WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Do not delete the original static website image.
    if (strpos($ad['image'], 'uploads/advertisements/') === 0) {
        $imagePath = "../../" . $ad['image'];

        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: index.php?success=" .
           urlencode("Advertisement deleted successfully."));
    exit;
}

mysqli_stmt_close($stmt);

header("Location: index.php?error=" .
       urlencode("Could not delete the advertisement."));
exit;