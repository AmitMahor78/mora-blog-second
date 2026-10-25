
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$query = "SELECT id, name, email, status, can_post, created_at
          FROM registered_users
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Unable to fetch registered users.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users | Mora Blog</title>

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
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        .topbar h2 { margin: 0; font-size: 23px; }

        .container {
            margin-left: 260px;
            padding: 25px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .page-heading h3 { margin: 0; }

        .table-wrapper {
            overflow-x: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        table {
            width: 100%;
            min-width: 750px;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #212529;
            color: #fff;
        }

        tr:hover { background: #f8f9fa; }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .active {
            background: #d1e7dd;
            color: #0f5132;
        }

        .inactive {
            background: #f8d7da;
            color: #842029;
        }

        .badge-permission {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-permission.allowed {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-permission.denied {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .btn-toggle {
            display: inline-block;
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            transition: opacity .15s ease;
        }

        .btn-toggle.grant {
            background: #10b981;
        }

        .btn-toggle.revoke {
            background: #f59e0b;
        }

        .btn-toggle:hover {
            opacity: 0.88;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-edit { background: #0d6efd; }
        .btn-delete { background: #dc3545; }

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .actions form { margin: 0; }

        .message {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 5px;
            background: #d1e7dd;
            color: #0f5132;
        }

        .empty {
            text-align: center;
            padding: 25px;
        }

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

<header class="topbar">
    <h2>Registered Users Management</h2>
</header>

<main class="container">

    <div class="page-heading">
        <h3>Website Registered Users</h3>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="message">
            <?php
            $messages = [
                'updated' => 'User details updated successfully.',
                'deleted' => 'Registered user deleted successfully.',
                'permission_granted' => 'Posting permission granted successfully.',
                'permission_revoked' => 'Posting permission revoked successfully.'
            ];

            $key = $_GET['success'];

            echo htmlspecialchars(
                $messages[$key] ?? 'Operation completed successfully.'
            );
            ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Account Status</th>
                    <th>Posting Permission</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int)$row['id']; ?></td>

                        <td><?= htmlspecialchars($row['name']); ?></td>

                        <td><?= htmlspecialchars($row['email']); ?></td>

                        <td>
                            <span class="status <?= $row['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </td>

                        <td>
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <?php if ((int)($row['can_post'] ?? 0) === 1): ?>
                                    <span class="badge-permission allowed">✓ Allowed</span>
                                    <form
                                        action="toggle-permission.php"
                                        method="POST"
                                        style="display:inline; margin:0;"
                                        onsubmit="return confirm('Revoke posting permission from <?= htmlspecialchars(addslashes($row['name'])); ?>?');"
                                    >
                                        <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                                        <input type="hidden" name="action" value="disable">
                                        <button type="submit" class="btn-toggle revoke" title="Revoke post permission">
                                            Revoke
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge-permission denied">✕ Disabled</span>
                                    <form
                                        action="toggle-permission.php"
                                        method="POST"
                                        style="display:inline; margin:0;"
                                        onsubmit="return confirm('Grant posting permission to <?= htmlspecialchars(addslashes($row['name'])); ?>?');"
                                    >
                                        <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                                        <input type="hidden" name="action" value="enable">
                                        <button type="submit" class="btn-toggle grant" title="Grant post permission">
                                            Grant
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td><?= htmlspecialchars($row['created_at']); ?></td>

                        <td>
                            <div class="actions">
                                <a
                                    class="btn btn-edit"
                                    href="edit.php?id=<?= (int)$row['id']; ?>"
                                >Edit User</a>

                                <form
                                    action="delete.php"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this registered user?');"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$row['id']; ?>"
                                    >
                                    <button type="submit" class="btn btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>

            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">
                        No registered users found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>
