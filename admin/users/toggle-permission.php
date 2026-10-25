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
$action = $_POST['action'] ?? '';

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT can_post FROM registered_users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: index.php");
    exit;
}

if ($action === 'enable') {
    $newVal = 1;
} elseif ($action === 'disable') {
    $newVal = 0;
} else {
    $newVal = ((int)$user['can_post'] === 1) ? 0 : 1;
}

$updateStmt = $conn->prepare("UPDATE registered_users SET can_post = ? WHERE id = ?");
$updateStmt->bind_param("ii", $newVal, $id);
$updateStmt->execute();
$updateStmt->close();

$msg = ($newVal === 1) ? 'permission_granted' : 'permission_revoked';
header("Location: index.php?success=" . $msg);
exit;
