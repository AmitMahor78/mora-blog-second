
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, email, company, request_type, message, status, created_at
     FROM contact_messages
     WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$contact = $stmt->get_result()->fetch_assoc();

if (!$contact) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';

    if (!in_array($newStatus, ['new', 'read', 'replied'], true)) {
        $error = 'Please select a valid status.';
    } else {
        $update = $conn->prepare(
            "UPDATE contact_messages SET status = ? WHERE id = ?"
        );
        $update->bind_param("si", $newStatus, $id);

        if ($update->execute()) {
            header("Location: view.php?id=" . $id);
            exit;
        }

        $error = 'Unable to update the message status.';
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Message Details</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .topbar {
            margin-left: 260px;
            padding: 20px 25px;
            background: white;
            border-bottom: 1px solid #ddd;
        }

        .container {
            margin-left: 260px;
            padding: 25px;
        }

        .card {
            max-width: 850px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0000000d;
        }

        .detail {
            padding: 14px 0;
            border-bottom: 1px solid #eee;
            overflow-wrap: anywhere;
        }

        .detail strong {
            display: inline-block;
            width: 150px;
            vertical-align: top;
        }

        .message {
            white-space: pre-wrap;
            line-height: 1.7;
        }

        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: 0;
            border-radius: 5px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .error {
            color: #b91c1c;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .topbar, .container { margin-left: 220px; }
        }

        @media (max-width: 480px) {
            .topbar, .container {
                margin-left: 200px;
                padding: 15px 10px;
            }

            .detail strong {
                display: block;
                width: auto;
                margin-bottom: 7px;
            }
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<div class="topbar">
    <h2>Contact Message Details</h2>
</div>

<div class="container">
    <div class="card">

        <div class="detail">
            <strong>Message ID:</strong>
            <?= (int)$contact['id'] ?>
        </div>

        <div class="detail">
            <strong>Name:</strong>
            <?= e($contact['name']) ?>
        </div>

        <div class="detail">
            <strong>Email:</strong>
            <?= e($contact['email']) ?>
        </div>

        <div class="detail">
            <strong>Company:</strong>
            <?= e($contact['company'] ?: 'Not provided') ?>
        </div>

        <div class="detail">
            <strong>Request Type:</strong>
            <?= e($contact['request_type'] ?: 'Not specified') ?>
        </div>

        <div class="detail">
            <strong>Received At:</strong>
            <?= e($contact['created_at']) ?>
        </div>

        <div class="detail">
            <strong>Message:</strong>
            <div class="message"><?= e($contact['message']) ?></div>
        </div>

        <div class="detail">
            <strong>Current Status:</strong>
            <?= ucfirst(e($contact['status'])) ?>
        </div>

        <h3>Update Message Status</h3>

        <?php if ($error !== ''): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <select name="status" required>
                <option value="new" <?= $contact['status'] === 'new' ? 'selected' : '' ?>>
                    New
                </option>
                <option value="read" <?= $contact['status'] === 'read' ? 'selected' : '' ?>>
                    Read
                </option>
                <option value="replied" <?= $contact['status'] === 'replied' ? 'selected' : '' ?>>
                    Replied
                </option>
            </select>

            <button type="submit" class="btn">Update Status</button>
        </form>

        <a href="index.php" class="btn" style="margin-top:20px;">Back to Messages</a>
    </div>
</div>

</body>
</html>
