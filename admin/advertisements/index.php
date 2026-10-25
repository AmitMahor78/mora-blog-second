```php
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$query = "SELECT id, title, image, link, status, created_at
          FROM advertisements
          ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advertisement Management</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fb;
}

.topbar {
    margin-left: 260px;
    padding: 20px 25px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar h1 { margin: 0; font-size: 24px; }

.container {
    margin-left: 260px;
    padding: 25px;
}

.table-card {
    background: #fff;
    padding: 22px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,.05);
    overflow-x: auto;
}

.add-btn, .edit-btn, .delete-btn {
    display: inline-block;
    padding: 9px 13px;
    border-radius: 6px;
    color: #fff;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 13px;
}

.add-btn { background: #4f46e5; }
.edit-btn { background: #2563eb; }
.delete-btn { background: #dc2626; }

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
}

th, td {
    padding: 14px 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    vertical-align: middle;
}

th {
    background: #f8fafc;
    color: #475569;
}

.ad-image {
    width: 120px;
    height: 65px;
    object-fit: contain;
    border-radius: 5px;
    background: #f1f5f9;
}

.status-active {
    color: #15803d;
    background: #dcfce7;
    padding: 5px 9px;
    border-radius: 20px;
}

.status-inactive {
    color: #b91c1c;
    background: #fee2e2;
    padding: 5px 9px;
    border-radius: 20px;
}

.message {
    padding: 12px;
    background: #dcfce7;
    color: #166534;
    border-radius: 6px;
    margin-bottom: 15px;
}

.error-message {
    padding: 12px;
    background: #fee2e2;
    color: #991b1b;
    border-radius: 6px;
    margin-bottom: 15px;
}

@media(max-width: 768px) {
    .topbar, .container {
        margin-left: 220px;
    }
}

@media(max-width: 480px) {
    .topbar, .container {
        margin-left: 200px;
        padding: 12px;
    }
}
</style>
</head>

<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<header class="topbar">
    <h1>Advertisement Management</h1>
    <a href="create.php" class="add-btn">+ Add Advertisement</a>
</header>

<main class="container">

    <?php if (isset($_GET['success'])): ?>
        <div class="message">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <h2>All Advertisements</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Link</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($ad = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int)$ad['id'] ?></td>

                        <td>
                            <?php if (!empty($ad['image'])): ?>
                                <img
                                    class="ad-image"
                                    src="../../<?= htmlspecialchars($ad['image']) ?>"
                                    alt="Advertisement"
                                >
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($ad['title']) ?></td>

                        <td>
                            <?php if (!empty($ad['link'])): ?>
                                <a href="<?= htmlspecialchars($ad['link']) ?>"
                                   target="_blank" rel="noopener noreferrer">
                                    Open Link
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($ad['status'] === 'active'): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(date('d M Y', strtotime($ad['created_at']))) ?>
                        </td>

                        <td>
                            <a class="edit-btn"
                               href="edit.php?id=<?= (int)$ad['id'] ?>">
                                Edit
                            </a>

                            <form method="POST" action="delete.php"
                                  style="display:inline"
                                  onsubmit="return confirm('Delete this advertisement?');">
                                <input type="hidden" name="id"
                                       value="<?= (int)$ad['id'] ?>">
                                <button class="delete-btn" type="submit">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;">
                        No advertisements found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
