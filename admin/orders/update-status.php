<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Validate order ID
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId < 1) {
    header("Location: index.php?error=invalid_order");
    exit;
}

// Validate CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['order_status_csrf'] ?? '';

if (
    !is_string($csrfToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $csrfToken)
) {
    http_response_code(403);
    exit("Invalid security token. Please go back and try again.");
}

// Allowed statuses
$allowedStatuses = [
    'pending',
    'processing',
    'shipped',
    'delivered',
    'cancelled'
];

$newStatus = strtolower(trim($_POST['status'] ?? ''));

if (!in_array($newStatus, $allowedStatuses, true)) {
    header("Location: view.php?id=" . $orderId . "&error=invalid_status");
    exit;
}

// Check that the order exists
$checkStmt = mysqli_prepare(
    $conn,
    "SELECT id FROM orders WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($checkStmt, "i", $orderId);
mysqli_stmt_execute($checkStmt);

$checkResult = mysqli_stmt_get_result($checkStmt);
$orderExists = mysqli_fetch_assoc($checkResult);

mysqli_stmt_close($checkStmt);

if (!$orderExists) {
    header("Location: index.php?error=order_not_found");
    exit;
}

// Update order status
$updateStmt = mysqli_prepare(
    $conn,
    "UPDATE orders SET status = ? WHERE id = ?"
);

if (!$updateStmt) {
    exit("Unable to prepare status update.");
}

mysqli_stmt_bind_param(
    $updateStmt,
    "si",
    $newStatus,
    $orderId
);

if (mysqli_stmt_execute($updateStmt)) {
    mysqli_stmt_close($updateStmt);

    header(
        "Location: view.php?id=" . $orderId . "&success=status_updated"
    );
    exit;
}

mysqli_stmt_close($updateStmt);

header("Location: view.php?id=" . $orderId . "&error=update_failed");
exit;