<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";


/*
    Fetch all comments with related post title
*/
$query = "
    SELECT
        c.id,
        c.name,
        c.email,
        c.comment,
        c.created_at,
        c.post_id,
        p.title AS post_title
    FROM comments c
    LEFT JOIN posts p
        ON c.post_id = p.id
    ORDER BY c.created_at DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}


$message = "";
$messageType = "";


/*
    Success messages
*/
if (isset($_GET['success'])) {

    if ($_GET['success'] === 'deleted') {
        $message = "Comment deleted successfully.";
        $messageType = "success";
    }
}


/*
    Error messages
*/
if (isset($_GET['error'])) {

    if ($_GET['error'] === 'delete_failed') {
        $message = "Failed to delete comment.";
        $messageType = "error";
    }
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

    <title>Manage Comments - Admin Panel</title>

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
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 28px;
        }

        .message {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
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
            vertical-align: top;
        }

        th {
            background: #f1f1f1;
        }

        .comment-text {
            max-width: 350px;
            line-height: 1.5;
        }

        .post-title {
            font-weight: bold;
            color: #333;
        }

        .email {
            color: #666;
            font-size: 14px;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 7px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .delete-btn:hover {
            background: #c82333;
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

    <h2>Admin Panel - Comments</h2>

    <a href="../logout.php">
        Logout
    </a>

</div>


<div class="container">

    <div class="header">

        <h1>Manage Comments</h1>

    </div>


    <?php if ($message !== ""): ?>

        <div class="message <?php echo $messageType; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Post</th>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Comment</th>

                    <th>Date</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($comment = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php echo $comment['id']; ?>
                        </td>


                        <td>

                            <?php if (!empty($comment['post_title'])): ?>

                                <span class="post-title">

                                    <?php
                                    echo htmlspecialchars(
                                        $comment['post_title']
                                    );
                                    ?>

                                </span>

                            <?php else: ?>

                                <span>
                                    Post Deleted
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $comment['name']
                            );
                            ?>

                        </td>


                        <td>

                            <span class="email">

                                <?php
                                echo htmlspecialchars(
                                    $comment['email']
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <div class="comment-text">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $comment['comment']
                                    )
                                );
                                ?>

                            </div>

                        </td>


                        <td>

                            <?php
                            echo date(
                                "M d, Y h:i A",
                                strtotime($comment['created_at'])
                            );
                            ?>

                        </td>


                        <td>

                            <a
                                href="delete.php?id=<?php echo $comment['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this comment?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="empty"
                    >
                        No comments found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>