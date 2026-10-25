<?php

include "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../contact.php");
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$company = trim($_POST['company'] ?? '');
$request_type = trim($_POST['request_type'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if ($name === '' || $email === '' || $message === '') {
    header("Location: ../contact.php?status=error&msg=required");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../contact.php?status=error&msg=email");
    exit;
}

// Insert into database
$sql = "INSERT INTO contact_messages
        (name, email, company, request_type, message)
        VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header("Location: ../contact.php?status=error&msg=database");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $name,
    $email,
    $company,
    $request_type,
    $message
);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: ../contact.php?status=success");
    exit;

} else {

    mysqli_stmt_close($stmt);

    header("Location: ../contact.php?status=error&msg=database");
    exit;
}