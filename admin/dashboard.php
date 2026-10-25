<?php

session_start();

include "../includes/db.php";

// Only Admin can access this dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

// Total Posts
$postQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM posts"
);

$postData = mysqli_fetch_assoc($postQuery);
$totalPosts = $postData['total'] ?? 0;


// Published Posts
$publishedQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM posts WHERE status = 'published'"
);

$publishedData = mysqli_fetch_assoc($publishedQuery);
$publishedPosts = $publishedData['total'] ?? 0;


// Total Categories
$categoryQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

$categoryData = mysqli_fetch_assoc($categoryQuery);
$totalCategories = $categoryData['total'] ?? 0;


// Total Authors
$authorQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'author'"
);

$authorData = mysqli_fetch_assoc($authorQuery);
$totalAuthors = $authorData['total'] ?? 0;


// Total Comments
$commentQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM comments"
);

$commentData = mysqli_fetch_assoc($commentQuery);
$totalComments = $commentData['total'] ?? 0;


// Total Videos
$videoQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM videos"
);

$videoData = mysqli_fetch_assoc($videoQuery);
$totalVideos = $videoData['total'] ?? 0;


// Total Products
$productQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

$productData = mysqli_fetch_assoc($productQuery);
$totalProducts = $productData['total'] ?? 0;


// Total Registered Users
$userQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM registered_users"
);

$userData = mysqli_fetch_assoc($userQuery);
$totalUsers = $userData['total'] ?? 0;


// Total Subscribers
$subscriberQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM subscribers"
);

$subscriberData = mysqli_fetch_assoc($subscriberQuery);
$totalSubscribers = $subscriberData['total'] ?? 0;


// Total Contact Messages
$messageQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM contact_messages"
);

$messageData = mysqli_fetch_assoc($messageQuery);
$totalMessages = $messageData['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard | Mora</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .admin_layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */

        .sidebar {
            width: 250px;
            background: #1f2937;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
        }

        .sidebar_logo {
            padding: 25px 20px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar_menu {
            list-style: none;
            padding: 15px 0;
        }

        .sidebar_menu li a {
            display: block;
            padding: 13px 20px;
            color: #d1d5db;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar_menu li a:hover,
        .sidebar_menu li a.active {
            background: #374151;
            color: #fff;
        }

        .sidebar_menu .menu_heading {
            padding: 15px 20px 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #9ca3af;
        }

        /* Main */

        .main_content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        /* Topbar */

        .topbar {
            background: #fff;
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .topbar h2 {
            font-size: 22px;
        }

        .admin_info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin_name {
            font-weight: bold;
        }

        .logout_btn {
            text-decoration: none;
            background: #dc3545;
            color: #fff;
            padding: 8px 14px;
            border-radius: 4px;
        }

        .logout_btn:hover {
            background: #b02a37;
        }

        /* Dashboard */

        .dashboard_content {
            padding: 25px;
        }

        .welcome_box {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }

        .welcome_box h3 {
            margin-bottom: 8px;
        }

        .welcome_box p {
            color: #666;
        }

        /* Cards */

        .stats_grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat_card {
            background: #fff;
            padding: 22px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .stat_card h4 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat_card .number {
            font-size: 30px;
            font-weight: bold;
        }

        .stat_card .link {
            display: inline-block;
            margin-top: 12px;
            color: #007bff;
            text-decoration: none;
            font-size: 13px;
        }

        /* Quick Actions */

        .quick_actions {
            margin-top: 30px;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .quick_actions h3 {
            margin-bottom: 18px;
        }

        .action_grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .action_btn {
            display: block;
            padding: 14px;
            text-align: center;
            background: #f3f4f6;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
        }

        .action_btn:hover {
            background: #e5e7eb;
        }

        /* Responsive */

        @media (max-width: 1100px) {

            .stats_grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .action_grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 768px) {

            .sidebar {
                width: 210px;
            }

            .main_content {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .stats_grid {
                grid-template-columns: 1fr;
            }

            .action_grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="admin_layout">

    <!-- Sidebar -->

    <?php include 'sidebar.php'; ?>


    <!-- Main Content -->

    <main class="main_content">

        <!-- Topbar -->

        <div class="topbar">

            <h2>
                Dashboard
            </h2>

            <div class="admin_info">

                <span class="admin_name">
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                </span>

                <a
                    href="logout.php"
                    class="logout_btn"
                >
                    Logout
                </a>

            </div>

        </div>


        <!-- Dashboard Content -->

        <div class="dashboard_content">

            <div class="welcome_box">

                <h3>
                    Welcome,
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?> 👋
                </h3>

                <p>
                    Manage your Mora website from this administration panel.
                </p>

            </div>


            <!-- Statistics -->

            <div class="stats_grid">

                <div class="stat_card">

                    <h4>Total Posts</h4>

                    <div class="number">
                        <?php echo $totalPosts; ?>
                    </div>

                    <a href="posts/index.php" class="link">
                        Manage Posts →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Published Posts</h4>

                    <div class="number">
                        <?php echo $publishedPosts; ?>
                    </div>

                    <a href="posts/index.php" class="link">
                        View Posts →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Categories</h4>

                    <div class="number">
                        <?php echo $totalCategories; ?>
                    </div>

                    <a href="categories/index.php" class="link">
                        Manage Categories →
                    </a>

                </div>



                <div class="stat_card">

                    <h4>Comments</h4>

                    <div class="number">
                        <?php echo $totalComments; ?>
                    </div>

                    <a href="comments/index.php" class="link">
                        Manage Comments →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Videos</h4>

                    <div class="number">
                        <?php echo $totalVideos; ?>
                    </div>

                    <a href="videos/index.php" class="link">
                        Manage Videos →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Products</h4>

                    <div class="number">
                        <?php echo $totalProducts; ?>
                    </div>

                    <a href="products/index.php" class="link">
                        Manage Products →
                    </a>

                </div>

                 <div class="stat_card">

                    <h4>Orders</h4>

                    <!-- <div class="number">
                        <?php echo $totalProducts; ?>
                    </div> -->

                    <a href="orders/index.php" class="link">
                        Manage Orders →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Registered Users</h4>

                    <div class="number">
                        <?php echo $totalUsers; ?>
                    </div>

                    <a href="users/index.php" class="link">
                        Manage Users →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Subscribers</h4>

                    <div class="number">
                        <?php echo $totalSubscribers; ?>
                    </div>

                    <a href="subscribers/index.php" class="link">
                        View Subscribers →
                    </a>

                </div>


                <div class="stat_card">

                    <h4>Contact Messages</h4>

                    <div class="number">
                        <?php echo $totalMessages; ?>
                    </div>

                    <a href="messages/index.php" class="link">
                        View Messages →
                    </a>

                </div>

            </div>


            <!-- Quick Actions -->

            <div class="quick_actions">

                <h3>
                    Quick Actions
                </h3>

                <div class="action_grid">

                    <a
                        href="posts/create.php"
                        class="action_btn"
                    > 
                        ➕ Add New Post
                    </a>

                    <a
                        href="categories/create.php"
                        class="action_btn"
                    >
                        ➕ Add Category
                    </a>

                    <a
                        href="videos/create.php"
                        class="action_btn"
                    >
                        ➕ Add Video
                    </a>

                    <a
                        href="products/create.php"
                        class="action_btn"
                    >
                        ➕ Add Product
                    </a>

                </div>

            </div>

        </div>

    </main>

</div>

</body>

</html>