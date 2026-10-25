
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$query = "SELECT id, name, price, image, category, description, status, created_at
          FROM products
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Unable to fetch products.");
}

function productImageUrl($image)
{
    $image = trim((string)$image);

    if ($image === '') {
        return '';
    }

    // Convert a legacy Windows absolute path to a project-relative path.
    $image = str_replace('\\', '/', $image);
    $marker = '/mora-blog-second/';

    $position = stripos($image, $marker);

    if ($position !== false) {
        $image = substr($image, $position + strlen($marker));
    }

    // Do not render arbitrary external URLs as product images.
    if (preg_match('#^https?://#i', $image)) {
        return '';
    }

    if (str_starts_with($image, '/') || str_contains($image, '..')) {
        return '';
    }

    return '/' . ltrim($image, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management | Mora Blog</title>

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
            gap: 15px;
            margin-bottom: 20px;
        }

        .page-heading h3 { margin: 0; }

        .btn {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-add { background: #198754; }
        .btn-edit { background: #0d6efd; }
        .btn-delete { background: #dc3545; }

        .table-wrapper {
            overflow-x: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        table {
            width: 100%;
            min-width: 950px;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        th {
            background: #212529;
            color: #fff;
        }

        tr:hover { background: #f8f9fa; }

        .product-image {
            width: 65px;
            height: 60px;
            object-fit: contain;
            border-radius: 5px;
            background: #fff;
            border: 1px solid #eee;
        }

        .no-image {
            color: #777;
            font-size: 12px;
        }

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

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .actions form { margin: 0; }

        .message {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 18px;
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
    <h2>Products Management</h2>
</header>

<main class="container">

    <div class="page-heading">
        <h3>All Products</h3>
        <a href="create.php" class="btn btn-add">+ Add Product</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="message">
            <?php
            $messages = [
                'created' => 'Product added successfully.',
                'updated' => 'Product updated successfully.',
                'deleted' => 'Product deleted successfully.'
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
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php $imageUrl = productImageUrl($row['image']); ?>

                    <tr>
                        <td><?= (int)$row['id']; ?></td>

                        <td>
                            <?php if ($imageUrl !== ''): ?>
                                <img
                                    src="<?= htmlspecialchars($imageUrl); ?>"
                                    alt="<?= htmlspecialchars($row['name']); ?>"
                                    class="product-image"
                                >
                            <?php else: ?>
                                <span class="no-image">No image</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['name']); ?>
                        </td>

                        <td>
                            ₹<?= number_format((float)$row['price'], 2); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['category']); ?>
                        </td>

                        <td>
                            <span class="status <?= $row['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['created_at']); ?>
                        </td>

                        <td>
                            <div class="actions">
                                <a
                                    href="edit.php?id=<?= (int)$row['id']; ?>"
                                    class="btn btn-edit"
                                >Edit</a>

                                <form
                                    action="delete.php"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this product?');"
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
                    <td colspan="8" class="empty">
                        No products found. Click Add Product to create one.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>
