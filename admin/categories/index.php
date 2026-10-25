<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";

/*
    Get all categories with post count
*/
$query = "
    SELECT
        c.id,
        c.name,
        COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p
        ON c.id = p.category_id
    GROUP BY c.id, c.name
    ORDER BY c.name ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

$message = "";

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'created') {
        $message = "Category created successfully.";
    }

    if ($_GET['success'] === 'updated') {
        $message = "Category updated successfully.";
    }

    if ($_GET['success'] === 'deleted') {
        $message = "Category deleted successfully.";
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'has_posts') {
    $message = "This category contains posts and cannot be deleted.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Categories - Admin Panel</title>

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
            background: #dc3545;
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
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 28px;
        }

        .add-btn {
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 5px;
        }

        .add-btn:hover {
            background: #0056b3;
        }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f1f1;
        }

        .count {
            display: inline-block;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
        }

        .edit-btn {
            background: #28a745;
            color: white;
            text-decoration: none;
            padding: 7px 10px;
            border-radius: 4px;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 7px 10px;
            border-radius: 4px;
        }

        .disabled-btn {
            background: #999;
            color: white;
            padding: 7px 10px;
            border-radius: 4px;
            cursor: not-allowed;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

    </style>

</head>

<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="topbar">

    <h2>Admin Panel - Categories</h2>

    <a href="../logout.php">Logout</a>

</div>


<div class="container">

    <div class="header">

        <h1>Manage Categories</h1>

        <a
            href="create.php"
            class="add-btn"
        >
            + Add Category
        </a>

    </div>


    <?php if (isset($_GET['success'])): ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['error'])): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <div class="table-box">

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Posts</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($category = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php echo $category['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </td>

                        <td>

                            <span class="count">

                                <?php echo $category['post_count']; ?>

                                <?php
                                echo ($category['post_count'] == 1)
                                    ? ' Post'
                                    : ' Posts';
                                ?>

                            </span>

                        </td>

                        <td>

                            <a
                                href="edit.php?id=<?php echo $category['id']; ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>


                            <?php if ($category['post_count'] == 0): ?>

                                <a
                                    href="delete.php?id=<?php echo $category['id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this category?');"
                                >
                                    Delete
                                </a>

                            <?php else: ?>

                                <span class="disabled-btn">
                                    Delete
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="4"
                        class="empty"
                    >
                        No categories found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>