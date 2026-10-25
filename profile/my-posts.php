<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

// Check whether the user is logged in.
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int) $_SESSION['registered_user_id'];

$userName = $_SESSION['registered_user_name'] ?? 'User';

// Generate CSRF token for delete requests.
if (empty($_SESSION['my_posts_csrf_token'])) {
    $_SESSION['my_posts_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['my_posts_csrf_token'];

$error = '';
$success = '';

// Handle delete request.
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'delete') {

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrfToken, $submittedToken)) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if (!$postId || $postId < 1) {
            $error = 'Invalid post selected.';
        } else {
            // Only delete a post belonging to the logged-in user.
            $deleteStmt = mysqli_prepare(
                $conn,
                "DELETE FROM posts
                 WHERE id = ? AND registered_user_id = ?"
            );

            if ($deleteStmt) {
                mysqli_stmt_bind_param(
                    $deleteStmt,
                    "ii",
                    $postId,
                    $userId
                );

                if (mysqli_stmt_execute($deleteStmt)) {
                    if (mysqli_stmt_affected_rows($deleteStmt) > 0) {
                        $success = 'Post deleted successfully.';
                    } else {
                        $error = 'Post not found or you cannot delete it.';
                    }
                } else {
                    $error = 'Unable to delete the post. Please try again.';
                }

                mysqli_stmt_close($deleteStmt);
            } else {
                $error = 'Unable to prepare the delete request.';
            }
        }
    }
}

// Show success message after creating a post.
if (isset($_GET['created']) && $_GET['created'] === '1') {
    $success = 'Your post was submitted successfully and is awaiting admin approval.';
}


if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $success = 'Post updated successfully. It is now pending admin approval.';
}

// Fetch the logged-in user's posts.
$sql = "SELECT
            id,
            title,
            slug,
            image,
            status,
            created_at
        FROM posts
        WHERE registered_user_id = ?
        ORDER BY created_at DESC, id DESC";

$stmt = mysqli_prepare($conn, $sql);
$posts = [];

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $postId,
        $postTitle,
        $postSlug,
        $postImage,
        $postStatus,
        $postCreatedAt
    );

    while (mysqli_stmt_fetch($stmt)) {
        $posts[] = [
            'id' => $postId,
            'title' => $postTitle,
            'slug' => $postSlug,
            'image' => $postImage,
            'status' => $postStatus,
            'created_at' => $postCreatedAt
        ];
    }

    mysqli_stmt_close($stmt);
} else {
    $error = 'Unable to load your posts. Please check the database schema.';
}

// Count post statuses.
$totalPosts = count($posts);
$pendingCount = 0;
$publishedCount = 0;
$rejectedCount = 0;
$draftCount = 0;

foreach ($posts as $post) {
    $status = strtolower((string) $post['status']);

    if ($status === 'pending') {
        $pendingCount++;
    } elseif ($status === 'published') {
        $publishedCount++;
    } elseif ($status === 'rejected') {
        $rejectedCount++;
    } elseif ($status === 'draft') {
        $draftCount++;
    }
}

function e($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function statusClass($status) {
    switch (strtolower((string) $status)) {
        case 'published':
            return 'published';
        case 'pending':
            return 'pending';
        case 'rejected':
            return 'rejected';
        case 'draft':
            return 'draft';
        default:
            return 'unknown';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts | Mora Blog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        .navbar {
            background: #172337;
            color: white;
            padding: 17px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .container {
            width: 92%;
            max-width: 1200px;
            margin: 35px auto;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .muted {
            color: #666;
            margin: 0;
            line-height: 1.5;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #1769e0;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            opacity: 0.9;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-card p {
            color: #666;
            margin: 0 0 10px;
            font-size: 14px;
        }

        .stat-card h2 {
            margin: 0;
            font-size: 27px;
        }

        .alert {
            padding: 13px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .alert-success {
            background: #dff5e6;
            color: #176534;
        }

        .alert-error {
            background: #ffe5e5;
            color: #a32121;
        }

        .posts-panel {
            background: white;
            border-radius: 9px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .posts-panel h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 21px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        th {
            background: #f8f9fb;
            color: #444;
            font-size: 13px;
        }

        td {
            font-size: 14px;
        }

        .post-title {
            font-weight: bold;
            max-width: 280px;
            overflow-wrap: anywhere;
        }

        .post-image {
            width: 65px;
            height: 48px;
            object-fit: cover;
            border-radius: 5px;
        }

        .no-image {
            width: 65px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eee;
            color: #777;
            font-size: 11px;
            border-radius: 5px;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .published {
            background: #dff5e6;
            color: #176534;
        }

        .pending {
            background: #fff0c2;
            color: #825900;
        }

        .rejected {
            background: #ffe0e0;
            color: #a32121;
        }

        .draft, .unknown {
            background: #e8eaf0;
            color: #444;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .edit-button {
            background: #1769e0;
        }

        .delete-button {
            background: #d93636;
        }

        .delete-form {
            margin: 0;
        }

        .empty-state {
            text-align: center;
            padding: 45px 15px;
        }

        .empty-state h3 {
            margin-top: 0;
        }

        .footer {
            text-align: center;
            padding: 25px 15px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .container {
                margin: 22px auto;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-card h2 {
                font-size: 23px;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .posts-panel {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="brand" href="../index.php">Mora Blog</a>

    <nav class="nav-links">
        <a href="../index.php">Home</a>
        <a href="../shop.php">Shop</a>
        <a href="../cart.php">Cart</a>
        <a href="../profile.php">My Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <div class="page-heading">
        <div>
            <h1>My Posts</h1>
            <p class="muted">
                Welcome, <?= e($userName) ?>.
                Manage your blog posts and check their status.
            </p>
        </div>

        <a class="button" href="create-post.php">
            + Create New Post
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <section class="stats">
        <div class="stat-card">
            <p>Total Posts</p>
            <h2><?= $totalPosts ?></h2>
        </div>

        <div class="stat-card">
            <p>Published</p>
            <h2><?= $publishedCount ?></h2>
        </div>

        <div class="stat-card">
            <p>Pending</p>
            <h2><?= $pendingCount ?></h2>
        </div>

        <div class="stat-card">
            <p>Rejected</p>
            <h2><?= $rejectedCount ?></h2>
        </div>

        <div class="stat-card">
            <p>Drafts</p>
            <h2><?= $draftCount ?></h2>
        </div>
    </section>

    <section class="posts-panel">
        <h2>Your Posts</h2>

        <?php if (count($posts) > 0): ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Post Title</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <?php if (!empty($post['image'])): ?>
                                    <img
                                        class="post-image"
                                        src="../<?= e(ltrim($post['image'], '/')) ?>"
                                        alt="<?= e($post['title']) ?>"
                                    >
                                <?php else: ?>
                                    <div class="no-image">No image</div>
                                <?php endif; ?>
                            </td>

                            <td class="post-title">
                                <?= e($post['title']) ?>
                            </td>

                            <td>
                                <span class="status <?= e(statusClass($post['status'])) ?>">
                                    <?= e($post['status']) ?>
                                </span>
                            </td>

                            <td>
                                <?= e(date(
                                    'd M Y',
                                    strtotime($post['created_at'])
                                )) ?>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        class="button edit-button"
                                        href="edit-post.php?id=<?= (int) $post['id'] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        class="delete-form"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this post?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="post_id"
                                            value="<?= (int) $post['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($csrfToken) ?>"
                                        >

                                        <button
                                            class="button delete-button"
                                            type="submit"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="empty-state">
                <h3>No posts yet</h3>
                <p class="muted">
                    You have not created any posts yet. Start by sharing your first article.
                </p>
                <br>
                <a class="button" href="create-post.php">
                    Create Your First Post
                </a>
            </div>

        <?php endif; ?>
    </section>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Mora Blog. All rights reserved.
</footer>

</body>
</html>