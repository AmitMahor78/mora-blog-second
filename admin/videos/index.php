<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$videosQuery = "
    SELECT
        v.id,
        v.title,
        v.thumbnail,
        v.video_url,
        v.status,
        v.created_at,
        c.name AS category_name,
        u.name AS author_name
    FROM videos v
    LEFT JOIN categories c ON v.category_id = c.id
    LEFT JOIN users u ON v.author_id = u.id
    ORDER BY v.created_at DESC
";

$videosResult = mysqli_query($conn, $videosQuery);

if (!$videosResult) {
    die("Error loading videos: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Management - Admin</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin-left: 280px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0;
        }

        .add-btn {
            background: #28a745;
            color: #fff;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f1f1f1;
        }

        .thumbnail {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-inactive {
            color: red;
            font-weight: bold;
        }

        .edit-btn,
        .delete-btn {
            padding: 7px 11px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }

        .edit-btn {
            background: #007bff;
        }

        .delete-btn {
            background: #dc3545;
        }

        .empty {
            text-align: center;
            padding: 30px;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="container">

    <div class="top-bar">
        <h1>Video Management</h1>

        <a href="create.php" class="add-btn">
            + Add Video
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">
            <?= htmlspecialchars($_GET['success']) ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;">
            <?= htmlspecialchars($_GET['error']) ?>
        </p>
    <?php endif; ?>

    <table>

        <thead>
            <tr>
                <th>ID</th>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        <?php if (mysqli_num_rows($videosResult) > 0): ?>

            <?php while ($video = mysqli_fetch_assoc($videosResult)): ?>

                <tr>

                    <td>
                        <?= (int)$video['id'] ?>
                    </td>

                    <td>

                        <?php if (!empty($video['thumbnail'])): ?>

                            <img
                                src="../../<?= htmlspecialchars($video['thumbnail']) ?>"
                                class="thumbnail"
                                alt="Thumbnail"
                            >

                        <?php else: ?>

                            No Image

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= htmlspecialchars($video['title']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($video['category_name'] ?? 'N/A') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($video['author_name'] ?? 'N/A') ?>
                    </td>

                    <td>

                        <?php if ($video['status'] === 'active'): ?>

                            <span class="status-active">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="status-inactive">
                                Inactive
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= date('M d, Y', strtotime($video['created_at'])) ?>
                    </td>

                    <td>

                        <a
                            href="edit.php?id=<?= (int)$video['id'] ?>"
                            class="edit-btn"
                        >
                            Edit
                        </a>

                        <a
                            href="delete.php?id=<?= (int)$video['id'] ?>"
                            class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this video?');"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="8" class="empty">
                    No videos found.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>
</html>