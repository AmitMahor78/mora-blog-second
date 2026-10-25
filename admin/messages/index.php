
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$sql = "SELECT id, name, email, company, request_type, message, status, created_at
        FROM contact_messages
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $term = "%" . $search . "%";
    $params = [$term, $term, $term];
    $types = "sss";
}

if (in_array($status, ['new', 'read', 'replied'], true)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$messages = $stmt->get_result();

$countResult = $conn->query(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'new') AS new_count,
        SUM(status = 'read') AS read_count,
        SUM(status = 'replied') AS replied_count
     FROM contact_messages"
);

$counts = $countResult->fetch_assoc();

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages Management</title>

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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 22px;
        }

        .stat-card, .panel {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0000000d;
        }

        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #666;
        }

        .stat-card p {
            margin: 0;
            font-size: 25px;
            font-weight: bold;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn, button {
            display: inline-block;
            padding: 9px 12px;
            border: none;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .search-btn { background: #2563eb; }
        .reset-btn { background: #64748b; }
        .view-btn { background: #0d9488; }
        .delete-btn { background: #dc2626; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th { background: #f8fafc; }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
            white-space: nowrap;
        }

        .new { background: #fef3c7; color: #92400e; }
        .read { background: #dbeafe; color: #1d4ed8; }
        .replied { background: #dcfce7; color: #166534; }

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
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
    <h2>Contact Messages</h2>
</div>

<div class="container">

    <div class="stats">
        <div class="stat-card">
            <h3>Total Messages</h3>
            <p><?= (int)$counts['total'] ?></p>
        </div>

        <div class="stat-card">
            <h3>New Messages</h3>
            <p><?= (int)($counts['new_count'] ?? 0) ?></p>
        </div>

        <div class="stat-card">
            <h3>Read Messages</h3>
            <p><?= (int)($counts['read_count'] ?? 0) ?></p>
        </div>

        <div class="stat-card">
            <h3>Replied Messages</h3>
            <p><?= (int)($counts['replied_count'] ?? 0) ?></p>
        </div>
    </div>

    <div class="panel">

        <form method="GET" class="filters">
            <input
                type="text"
                name="search"
                placeholder="Search name, email or message"
                value="<?= e($search) ?>"
            >

            <select name="status">
                <option value="">All Statuses</option>
                <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option>
                <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Read</option>
                <option value="replied" <?= $status === 'replied' ? 'selected' : '' ?>>Replied</option>
            </select>

            <button class="search-btn" type="submit">Search / Filter</button>
            <a href="index.php" class="btn reset-btn">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Request Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($messages->num_rows > 0): ?>
                    <?php while ($row = $messages->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['request_type'] ?: '—') ?></td>
                            <td>
                                <span class="badge <?= e($row['status']) ?>">
                                    <?= ucfirst(e($row['status'])) ?>
                                </span>
                            </td>
                            <td><?= e($row['created_at']) ?></td>
                            <td>
                                <div class="actions">
                                    <a
                                        class="btn view-btn"
                                        href="view.php?id=<?= (int)$row['id'] ?>"
                                    >View</a>

                                    <form
                                        method="POST"
                                        action="delete.php"
                                        onsubmit="return confirm('Are you sure you want to delete this message?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row['id'] ?>"
                                        >
                                        <button type="submit" class="delete-btn">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No contact messages found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
