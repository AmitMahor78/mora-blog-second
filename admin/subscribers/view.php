
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, email, status, subscribed_at FROM subscribers WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$subscriber = $stmt->get_result()->fetch_assoc();

if (!$subscriber) {
    header("Location: index.php");
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriber Details</title>

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
            background: white;
            padding: 25px;
            border-radius: 8px;
            max-width: 650px;
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
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            background: #2563eb;
            color: white;
            text-decoration: none;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 13px;
        }

        .active { background: #dcfce7; color: #166534; }
        .inactive { background: #fee2e2; color: #991b1b; }

        @media (max-width: 768px) {
            .topbar, .container { margin-left: 220px; }
        }

        @media (max-width: 480px) {
            .topbar, .container {
                margin-left: 200px;
                padding: 15px 10px;
            }
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<div class="topbar">
    <h2>Subscriber Details</h2>
</div>

<div class="container">
    <div class="card">
        <div class="detail">
            <strong>Subscriber ID:</strong>
            <?= (int)$subscriber['id'] ?>
        </div>

        <div class="detail">
            <strong>Email:</strong>
            <?= e($subscriber['email']) ?>
        </div>

        <div class="detail">
            <strong>Status:</strong>
            <span class="badge <?= e($subscriber['status']) ?>">
                <?= ucfirst(e($subscriber['status'])) ?>
            </span>
        </div>

        <div class="detail">
            <strong>Subscribed At:</strong>
            <?= e($subscriber['subscribed_at']) ?>
        </div>

        <a href="index.php" class="btn">Back to Subscribers</a>
    </div>
</div>

</body>
</html>
