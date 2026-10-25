
<?php

session_start();
require_once __DIR__ . '/includes/maintenance-check.php';



require_once __DIR__ . '/includes/db.php';

// User must be logged in
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['registered_user_id'];

$userName = $_SESSION['registered_user_name'] ?? 'User';
$userEmail = $_SESSION['registered_user_email'] ?? '';

$message = '';
$canPost = 0;
$totalPosts = 0;
$recentPosts = [];
$cartCount = 0;

// Get latest account details and posting permission
$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email, status, can_post
     FROM registered_users
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    exit('Unable to load your profile. Please try again later.');
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result(
    $stmt,
    $dbName,
    $dbEmail,
    $dbStatus,
    $dbCanPost
);

if (!mysqli_stmt_fetch($stmt) || $dbStatus !== 'active') {
    mysqli_stmt_close($stmt);
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}

mysqli_stmt_close($stmt);

$userName = $dbName;
$userEmail = $dbEmail;
$canPost = (int) $dbCanPost;

// Count this user's posts
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     FROM posts
     WHERE registered_user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $totalPosts);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Get this user's recent posts
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, status, created_at
     FROM posts
     WHERE registered_user_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $recentPosts[] = $row;
}

mysqli_stmt_close($stmt);

// Count items in the session cart, if present
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item)) {
            $cartCount += max(0, (int) ($item['quantity'] ?? 0));
        } else {
            // Supports a cart stored as product_id => quantity
            $cartCount += max(0, (int) $item);
        }
    }
}

if (!function_exists('profileEscape')) {
    function profileEscape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Mora Blog</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modern.css">

    <style>
        body {
            background: #f5f7fb;
        }

        .profile-page {
            max-width: 1150px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .profile-header {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .profile-header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .profile-header p {
            margin: 4px 0;
            color: #666;
        }

        .profile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .profile-btn {
            display: inline-block;
            padding: 11px 17px;
            border-radius: 6px;
            text-decoration: none !important;
            color: #fff !important;
            background: #1769ff;
        }

        .profile-btn.dark {
            background: #222;
        }

        .profile-btn.light {
            background: #e9eef8;
            color: #222 !important;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin: 24px 0;
        }

        .profile-card, .profile-section {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 18px rgba(0,0,0,.04);
        }

        .profile-card span {
            display: block;
            color: #777;
            margin-bottom: 8px;
        }

        .profile-card strong {
            font-size: 28px;
        }

        .profile-section {
            margin-bottom: 24px;
        }

        .profile-section h2 {
            margin: 0 0 18px;
            font-size: 22px;
        }

        .profile-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .profile-link {
            display: block;
            padding: 18px;
            background: #f5f7fb;
            border-radius: 8px;
            color: #222;
            text-decoration: none;
        }

        .profile-link strong {
            display: block;
            margin-bottom: 6px;
        }

        .profile-link span {
            color: #666;
            font-size: 14px;
        }

        .post-table {
            width: 100%;
            border-collapse: collapse;
        }

        .post-table th, .post-table td {
            padding: 13px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .post-table th {
            background: #f7f8fa;
        }

        .status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 20px;
            background: #edf0f5;
            font-size: 12px;
        }

        .empty-message {
            color: #666;
            padding: 15px 0;
        }

        .table-wrap {
            overflow-x: auto;
        }

        @media (max-width: 700px) {
            .profile-stats, .profile-links {
                grid-template-columns: 1fr;
            }

            .profile-header h1 {
                font-size: 23px;
            }

            .profile-header, .profile-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="profile-page">

    <div class="profile-header">
        <div>
            <h1>Welcome, <?= profileEscape($userName) ?>!</h1>
            <p><?= profileEscape($userEmail) ?></p>
            <p>
                Posting permission:
                <strong><?= $canPost ? 'Enabled' : 'Not enabled yet' ?></strong>
            </p>
        </div>

        <div class="profile-actions">
            <a class="profile-btn light" href="index.php">Visit Website</a>
            <a class="profile-btn dark" href="profile/logout.php">Logout</a>
        </div>
    </div>

    <?php if (isset($_GET['created']) && $_GET['created'] === '1'): ?>
        <div style="background:#d4edda;color:#155724;padding:12px 16px;border-radius:6px;margin-top:18px;">
            Your post was submitted successfully and is waiting for admin approval.
        </div>
    <?php elseif (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div style="background:#d4edda;color:#155724;padding:12px 16px;border-radius:6px;margin-top:18px;">
            Your post was updated and sent back for admin approval.
        </div>
    <?php endif; ?>

    <div class="profile-stats">
        <div class="profile-card">
            <span>My Posts</span>
            <strong><?= (int) $totalPosts ?></strong>
        </div>

        <div class="profile-card">
            <span>Posting Access</span>
            <strong><?= $canPost ? 'Yes' : 'No' ?></strong>
        </div>

        <div class="profile-card">
            <span>Cart Items</span>
            <strong><?= (int) $cartCount ?></strong>
        </div>
    </div>

    <section class="profile-section">
        <h2>My Account</h2>

        <div class="profile-links">
            <a class="profile-link" href="profile/my-posts.php">
                <strong>My Posts</strong>
                <span>View, edit and manage your blog posts.</span>
            </a>

            <a class="profile-link" href="shop.php">
                <strong>Shop Products</strong>
                <span>Browse available products.</span>
            </a>

            <a class="profile-link" href="cart.php">
                <strong>Shopping Cart</strong>
                <span>Review your cart and quantities.</span>
            </a>

            <a class="profile-link" href="profile/my-orders.php">
                <strong>My Orders</strong>
                <span>View your purchases and order status.</span>
            </a>

            <?php if ($canPost === 1): ?>
                <a class="profile-link" href="profile/create-post.php">
                    <strong>Create a Post</strong>
                    <span>Write and submit a new blog post.</span>
                </a>
            <?php else: ?>
                <div class="profile-link">
                    <strong>Posting Access</strong>
                    <span>Ask the administrator to enable your posting permission.</span>
                </div>
            <?php endif; ?>

            <a class="profile-link" href="index.php">
                <strong>Read the Blog</strong>
                <span>Return to the Mora Blog homepage.</span>
            </a>

            <a class="profile-link" href="contact.php">
                <strong>Contact Us</strong>
                <span>Get in touch with the website team.</span>
            </a>
        </div>
    </section>

    <section class="profile-section">
        <h2>My Recent Posts</h2>

        <?php if (empty($recentPosts)): ?>
            <p class="empty-message">
                You haven't created any posts yet.
                <?php if ($canPost): ?>
                    Use Create a Post to get started.
                <?php else: ?>
                    Posting access must be enabled by the administrator first.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="post-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPosts as $post): ?>
                            <tr>
                                <td><?= profileEscape($post['title']) ?></td>
                                <td>
                                    <span class="status">
                                        <?= profileEscape($post['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= profileEscape($post['created_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>

</body>
</html>
