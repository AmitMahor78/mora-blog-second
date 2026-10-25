<?php
session_start();

if (
    !isset($_SESSION['user_id'], $_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

// CSRF token
if (empty($_SESSION['posts_csrf_token'])) {
    $_SESSION['posts_csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// Handle Approve / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';
    $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['posts_csrf_token'], $token)
    ) {
        $error = "Invalid request. Please refresh the page.";

    } elseif (
        !$postId ||
        !in_array($action, ['approve', 'reject'], true)
    ) {
        $error = "Invalid post or action.";

    } else {

        $newStatus = ($action === 'approve')
            ? 'published'
            : 'rejected';

        // Only pending posts can be approved or rejected.
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE posts
             SET status = ?
             WHERE id = ? AND status = 'pending'"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $newStatus, $postId);

            if (mysqli_stmt_execute($stmt)) {

                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $message = ($action === 'approve')
                        ? "Post approved and published successfully."
                        : "Post rejected successfully.";
                } else {
                    $error = "Post not found or it is no longer pending.";
                }

            } else {
                $error = "Unable to update post. Check your database status column.";
            }

            mysqli_stmt_close($stmt);

        } else {
            $error = "Unable to prepare the database query.";
        }
    }
}

// Fetch posts from registered users and existing authors
$query = "
    SELECT
        p.id,
        p.title,
        p.status,
        p.created_at,
        c.name AS category_name,
        COALESCE(ru.name, u.name, 'Admin') AS author_name,
        p.registered_user_id
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    LEFT JOIN registered_users ru ON p.registered_user_id = ru.id
    ORDER BY
        CASE WHEN p.status = 'pending' THEN 0 ELSE 1 END,
        p.created_at DESC
";

$result = mysqli_query($conn, $query);

if (!$result && !$error) {
    $error = "Unable to load posts. Please check your database columns.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Posts - Admin Panel</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        .topbar {
            background: #222;
            color: white;
            padding: 18px 30px;
            display: flex;
            margin-left: 260px;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h2 {
            font-size: 22px;
        }

        .topbar a {
            color: white;
            text-decoration: none;
            background: #e74c3c;
            padding: 9px 15px;
            border-radius: 5px;
        }

        .container {
            margin-left: 260px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 28px;
        }

        .btn {
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 10px 16px;
            border-radius: 5px;
            display: inline-block;
        }

        .btn:hover {
            background: #0056b3;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f1f1;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 4px;
            font-size: 13px;
            text-transform: capitalize;
        }

        .published {
            background: #d4edda;
            color: #155724;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .draft {
            background: #e2e3e5;
            color: #383d41;
        }

        .rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .edit, .delete, .approve, .reject {
            display: inline-block;
            padding: 7px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            margin: 2px;
        }

        .edit {
            background: #28a745;
        }

        .delete {
            background: #dc3545;
        }

        .approve {
            background: #198754;
        }

        .reject {
            background: #b02a37;
        }

        .actions {
            min-width: 250px;
        }

        .actions form {
            display: inline-block;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 18px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .failure {
            background: #f8d7da;
            color: #721c24;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

        @media (max-width: 768px) {
            .topbar {
                margin-left: 0;
                padding: 15px;
            }

            .container {
                margin-left: 0;
                padding: 15px;
            }

            .header h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="topbar">
    <h2>Admin Panel - Posts</h2>
    <a href="../logout.php">Logout</a>
</div>

<div class="container">

    <div class="header">
        <h1>Manage Posts</h1>

        <a href="create.php" class="btn">
            + Create New Post
        </a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert failure">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($post = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $status = strtolower((string) $post['status']);

                    $allowedStatuses = [
                        'published',
                        'pending',
                        'draft',
                        'rejected'
                    ];

                    $statusClass = in_array(
                        $status,
                        $allowedStatuses,
                        true
                    ) ? $status : 'draft';
                    ?>

                    <tr>
                        <td><?= (int) $post['id'] ?></td>

                        <td>
                            <?= htmlspecialchars($post['title'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $post['category_name'] ?? 'N/A'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $post['author_name'] ?? 'N/A'
                            ) ?>

                            <?php if (!empty($post['registered_user_id'])): ?>
                                <br>
                                <small>Registered User</small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="status <?= $statusClass ?>">
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </span>
                        </td>

                        <td>
                            <?= !empty($post['created_at'])
                                ? htmlspecialchars(
                                    date(
                                        'M d, Y',
                                        strtotime($post['created_at'])
                                    )
                                )
                                : 'N/A' ?>
                        </td>

                        <td class="actions">

                            <?php if ($status === 'pending'): ?>

                                <!-- Approve button -->
                                <form method="POST"
                                      onsubmit="return confirm('Approve and publish this post?');">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= htmlspecialchars($_SESSION['posts_csrf_token']) ?>">

                                    <input type="hidden"
                                           name="post_id"
                                           value="<?= (int) $post['id'] ?>">

                                    <input type="hidden"
                                           name="action"
                                           value="approve">

                                    <button type="submit" class="approve">
                                        Approve
                                    </button>
                                </form>

                                <!-- Reject button -->
                                <form method="POST"
                                      onsubmit="return confirm('Reject this post?');">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= htmlspecialchars($_SESSION['posts_csrf_token']) ?>">

                                    <input type="hidden"
                                           name="post_id"
                                           value="<?= (int) $post['id'] ?>">

                                    <input type="hidden"
                                           name="action"
                                           value="reject">

                                    <button type="submit" class="reject">
                                        Reject
                                    </button>
                                </form>

                            <?php endif; ?>

                            <!-- Existing Edit option -->
                            <a href="edit.php?id=<?= (int) $post['id'] ?>"
                               class="edit">
                                Edit
                            </a>

                            <!-- Existing Delete option -->
                            <a href="delete.php?id=<?= (int) $post['id'] ?>"
                               class="delete"
                               onclick="return confirm('Are you sure you want to delete this post?');">
                                Delete
                            </a>

                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" class="empty">
                        No posts found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
