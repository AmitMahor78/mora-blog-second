<?php
session_start();
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
if (empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Security check failed. Go back and try again.');
}

$userId = (int)$_SESSION['registered_user_id'];
$postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$postId || $postId < 1) {
    header("Location: my-posts.php");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT image FROM posts WHERE id = ? AND registered_user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ii", $postId, $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $imagePath);
$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($found) {
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ? AND registered_user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $postId, $userId);
    mysqli_stmt_execute($stmt);
    $deleted = mysqli_stmt_affected_rows($stmt) === 1;
    mysqli_stmt_close($stmt);

    if ($deleted && str_starts_with((string)$imagePath, 'uploads/posts/')) {
        $fullPath = __DIR__ . '/../' . $imagePath;
        if (is_file($fullPath)) @unlink($fullPath);
    }
}

header("Location: my-posts.php");
exit;
