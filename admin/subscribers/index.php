
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$sql = "SELECT id, email, status, subscribed_at FROM subscribers WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND email LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if (in_array($status, ['active', 'inactive'], true)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY subscribed_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$subscribers = $stmt->get_result();

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM subscribers");
$totalSubscribers = $totalResult->fetch_assoc()['total'];

$activeResult = $conn->query(
    "SELECT COUNT(*) AS total FROM subscribers WHERE status = 'active'"
);
$activeSubscribers = $activeResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe Management</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .topbar {
            margin-left: 260px;
            padding: 20px 25px;
            background: #fff;
            border-bottom: 1px solid #ddd;
        }

        .container {
            margin-left: 260px;
            padding: 25px;
        }

        .stats {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            flex: 1;
            min-width: 180px;
            box-shadow: 0 2px 8px #0000000d;
        }

        .stat-card h3 {
            margin: 0 0 10px;
            color: #666;
            font-size: 15px;
        }

        .stat-card p {
            margin: 0;
            font-size: 25px;
            font-weight: bold;
        }

        .panel {
            background: #fff;
            padding: 22px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0000000d;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            min-width: 170px;
        }

        button, .btn {
            display: inline-block;
            border: none;
            padding: 10px 13px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            background: #2563eb;
            font-size: 14px;
        }

        .btn-view { background: #0d9488; }
        .btn-delete { background: #dc2626; }
        .btn-reset { background: #64748b; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th, td {
            text-align: left;
            padding: 13px 10px;
            border-bottom: 1px solid #eee;
        }

        th { background: #f8fafc; }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
        }

        .active { background: #dcfce7; color: #166534; }
        .inactive { background: #fee2e2; color: #991b1b; }

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .actions form { margin: 0; }

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
    <h2>Subscribe Management</h2>
</div>

<div class="container">

    <div class="stats">
        <div class="stat-card">
            <h3>Total Subscribers</h3>
            <p><?= (int)$totalSubscribers ?></p>
        </div>

        <div class="stat-card">
            <h3>Active Subscribers</h3>
            <p><?= (int)$activeSubscribers ?></p>
        </div>
    </div>

    <div class="panel">

        <form method="GET" class="filters">
            <input
                type="text"
                name="search"
                placeholder="Search subscriber email"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            >

            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>
                    Active
                </option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>
                    Inactive
                </option>
            </select>

            <button type="submit">Search / Filter</button>
            <a href="index.php" class="btn btn-reset">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Subscribed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($subscribers->num_rows > 0): ?>
                    <?php while ($row = $subscribers->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>

                            <td>
                                <?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <span class="badge <?= htmlspecialchars($row['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['subscribed_at'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        class="btn btn-view"
                                        href="view.php?id=<?= (int)$row['id'] ?>"
                                    >View</a>

                                    <form
                                        action="delete.php"
                                        method="POST"
                                        onsubmit="return confirm('Delete this subscriber?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row['id'] ?>"
                                        >
                                        <button type="submit" class="btn-delete">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No subscribers found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
