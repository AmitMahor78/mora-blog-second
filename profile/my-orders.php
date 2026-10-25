<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

// Check whether customer is logged in
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: /mora-blog-second/login.php");
exit;
}

$userId = (int) $_SESSION['registered_user_id'];

// Fetch orders belonging to the logged-in customer
$sql = "SELECT id, order_number, total_amount, status,
               payment_method, created_at
        FROM orders
        WHERE registered_user_id = ?
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to prepare orders query.");
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

// IMPORTANT: Pass $stmt to mysqli_stmt_get_result()
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Unable to fetch orders.");
}

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function statusClass($status)
{
    switch (strtolower((string) $status)) {
        case 'pending':
            return 'status-pending';
        case 'processing':
            return 'status-processing';
        case 'shipped':
            return 'status-shipped';
        case 'delivered':
            return 'status-delivered';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-default';
    }
}

$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Orders | Mora Blog</title>

    <link rel="icon"
          href="../images/favicon.png"
          type="image/x-icon">

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/modern.css" rel="stylesheet">

    <style>
        .my-orders-section {
            padding: 50px 0;
            min-height: 450px;
            background: #f8f9fa;
        }

        .orders-heading {
            margin-bottom: 25px;
        }

        .orders-heading h2 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .orders-card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .orders-table-wrapper {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            min-width: 750px;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }

        .orders-table th {
            background: #f5f5f5;
            font-weight: 600;
        }

        .order-number {
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-pending {
            color: #856404;
            background: #fff3cd;
        }

        .status-processing {
            color: #004085;
            background: #cce5ff;
        }

        .status-shipped {
            color: #383d41;
            background: #e2e3e5;
        }

        .status-delivered {
            color: #155724;
            background: #d4edda;
        }

        .status-cancelled {
            color: #721c24;
            background: #f8d7da;
        }

        .status-default {
            color: #333;
            background: #eee;
        }

        .view-order-btn {
            display: inline-block;
            padding: 8px 13px;
            color: #fff;
            background: #222;
            border-radius: 4px;
            text-decoration: none;
            white-space: nowrap;
        }

        .view-order-btn:hover {
            color: #fff;
            background: #444;
            text-decoration: none;
        }

        .empty-orders {
            padding: 40px 15px;
            text-align: center;
        }

        .empty-orders h3 {
            margin-bottom: 12px;
        }

        .shop-now-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 11px 22px;
            color: #fff;
            background: #222;
            border-radius: 4px;
            text-decoration: none;
        }

        .shop-now-btn:hover {
            color: #fff;
            background: #444;
        }

        @media (max-width: 767px) {
            .my-orders-section {
                padding: 30px 0;
            }

            .orders-card {
                padding: 15px;
            }

            .orders-heading h2 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

    <!-- Website top header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <!-- Website navigation -->
    <header class="main_header_area" id="header">
        <div class="container">
            <div class="header_menu">
                <?php include __DIR__ . '/../includes/navbar.php'; ?>
            </div>
        </div>
    </header>

    <!-- My Orders -->
    <section class="my-orders-section">
        <div class="container">

            <div class="orders-heading">
                <h2>My Orders</h2>
                <p>View and track your previous purchases.</p>
            </div>

            <div class="orders-card">

                <?php if (count($orders) > 0): ?>

                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Order Date</th>
                                    <th>Total Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="order-number">
                                            <?= escapeHtml($order['order_number']); ?>
                                        </td>

                                        <td>
                                            <?= escapeHtml(
                                                date(
                                                    'd M Y',
                                                    strtotime($order['created_at'])
                                                )
                                            ); ?>
                                        </td>

                                        <td>
                                            ₹<?= number_format(
                                                (float) $order['total_amount'],
                                                2
                                            ); ?>
                                        </td>

                                        <td>
                                            <?= escapeHtml(
                                                ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $order['payment_method']
                                                    )
                                                )
                                            ); ?>
                                        </td>

                                        <td>
                                            <span class="status-badge <?= escapeHtml(statusClass($order['status'])); ?>">
                                                <?= escapeHtml($order['status']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <a href="order-details.php?id=<?= (int) $order['id']; ?>" class="btn btn-sm btn-outline-primary" style="border-radius:20px; font-weight:600; padding:4px 14px;">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <div class="empty-orders">
                        <h3>No Orders Yet</h3>
                        <p>You haven't placed any orders using this account.</p>

                        <a href="../shop.php" class="shop-now-btn">
                            Start Shopping
                        </a>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- Website footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>

</body>
</html>
