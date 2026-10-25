<?php
session_start();

if (empty($_SESSION['order_status_csrf'])) {
    $_SESSION['order_status_csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../includes/db.php';

function viewEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Validate order ID
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId < 1) {
    http_response_code(400);
    exit("Invalid order ID.");
}

// Fetch order details
$orderStmt = mysqli_prepare(
    $conn,
    "SELECT * FROM orders WHERE id = ? LIMIT 1"
);

if (!$orderStmt) {
    exit("Unable to prepare order query.");
}

mysqli_stmt_bind_param($orderStmt, "i", $orderId);
mysqli_stmt_execute($orderStmt);

$orderResult = mysqli_stmt_get_result($orderStmt);
$order = mysqli_fetch_assoc($orderResult);

mysqli_stmt_close($orderStmt);

if (!$order) {
    http_response_code(404);
    exit("Order not found.");
}

// Fetch ordered products
$itemsStmt = mysqli_prepare(
    $conn,
    "SELECT product_id, product_name, price, quantity, subtotal
     FROM order_items
     WHERE order_id = ?"
);

if (!$itemsStmt) {
    exit("Unable to prepare order items query.");
}

mysqli_stmt_bind_param($itemsStmt, "i", $orderId);
mysqli_stmt_execute($itemsStmt);

$itemsResult = mysqli_stmt_get_result($itemsStmt);

$items = [];

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $item;
}

mysqli_stmt_close($itemsStmt);

// Status label
$status = strtolower(trim($order['status'] ?? 'pending'));

$statusClass = 'pending';

if ($status === 'processing') {
    $statusClass = 'processing';
} elseif ($status === 'shipped') {
    $statusClass = 'shipped';
} elseif ($status === 'delivered') {
    $statusClass = 'delivered';
} elseif ($status === 'cancelled') {
    $statusClass = 'cancelled';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Details | Mora Blog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #222;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0 0 8px;
        }

        .back-btn,
        .print-btn {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .back-btn {
            background: #374151;
            color: white;
        }

        .print-btn {
            background: #2563eb;
            color: white;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 8px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .card h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .detail-label {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .detail-value {
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 13px;
            text-transform: capitalize;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .processing {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .shipped {
            background: #e0e7ff;
            color: #3730a3;
        }

        .delivered {
            background: #dcfce7;
            color: #166534;
        }

        .cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-size: 18px;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 600px) {
            body {
                padding: 14px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 16px;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="top-bar no-print">
        <div>
            <h1>Order Details</h1>
            <p>
                Order:
                <strong><?= viewEscape($order['order_number']) ?></strong>
            </p>
        </div>

        <div>
            <a href="index.php" class="back-btn">
                Back to Orders
            </a>

            <button onclick="window.print()" class="print-btn">
                Print Order
            </button>
        </div>
    </div>

    <!-- Order Information -->
    <div class="card">
        <h2>Order Information</h2>

        <div class="details-grid">

            <div>
                <div class="detail-label">Order Number</div>
                <div class="detail-value">
                    <?= viewEscape($order['order_number']) ?>
                </div>
            </div>

            <div>
                <div class="detail-label">Order Date</div>
                <div class="detail-value">
                    <?= viewEscape($order['created_at']) ?>
                </div>
            </div>

            <div>
                <div class="detail-label">Order Status</div>
                <div class="detail-value">
                    <span class="status <?= $statusClass ?>">
                        <?= viewEscape($order['status']) ?>
                    </span>
                </div>
            </div>

            <div>
                <div class="detail-label">Payment Method</div>
                <div class="detail-value">
                    <?= viewEscape($order['payment_method']) ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Customer Information -->
    <div class="card">
        <h2>Customer Information</h2>

        <div class="details-grid">

            <div>
                <div class="detail-label">Customer Name</div>
                <div class="detail-value">
                    <?= viewEscape($order['customer_name']) ?>
                </div>
            </div>

            <div>
                <div class="detail-label">Email Address</div>
                <div class="detail-value">
                    <?= viewEscape($order['customer_email']) ?>
                </div>
            </div>

            <div>
                <div class="detail-label">Phone Number</div>
                <div class="detail-value">
                    <?= viewEscape($order['customer_phone']) ?>
                </div>
            </div>

            <div>
                <div class="detail-label">Customer ID</div>
                <div class="detail-value">
                    <?= $order['registered_user_id'] === null
                        ? 'Guest Checkout'
                        : (int)$order['registered_user_id'] ?>
                </div>
            </div>

            <div>
                <div class="detail-label">Address</div>
                <div class="detail-value">
                    <?= nl2br(viewEscape($order['address'])) ?>
                </div>
            </div>

            <div>
                <div class="detail-label">City / State / PIN Code</div>
                <div class="detail-value">
                    <?= viewEscape($order['city']) ?>,
                    <?= viewEscape($order['state']) ?> -
                    <?= viewEscape($order['pincode']) ?>
                </div>
            </div>

        </div>
    </div>

<hr style="margin: 20px 0;">

<h3>Update Order Status</h3>

<?php if (isset($_GET['success']) && $_GET['success'] === 'status_updated'): ?>
    <p style="color: green;">
        Order status updated successfully!
    </p>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <p style="color: red;">
        Unable to update order status. Please try again.
    </p>
<?php endif; ?>

<form action="update-status.php" method="POST">

    <input
        type="hidden"
        name="order_id"
        value="<?= (int)$order['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= viewEscape($_SESSION['order_status_csrf']) ?>"
    >

    <label for="status">Select New Status:</label>

    <select name="status" id="status" required>
        <?php
        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        ?>

        <?php foreach ($statuses as $value => $label): ?>
            <option
                value="<?= viewEscape($value) ?>"
                <?= strtolower($order['status']) === $value ? 'selected' : '' ?>
            >
                <?= viewEscape($label) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button
        type="submit"
        onclick="return confirm('Are you sure you want to update this order status?');"
        style="padding: 10px 16px; margin-top: 12px; cursor: pointer;"
    >
        Update Status
    </button>

</form>



    <!-- Ordered Products -->
    <div class="card">
        <h2>Ordered Products</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Product ID</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($items)): ?>

                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?= viewEscape($item['product_name']) ?>
                            </td>

                            <td>
                                <?= $item['product_id'] === null
                                    ? 'N/A'
                                    : (int)$item['product_id'] ?>
                            </td>

                            <td>
                                ₹<?= number_format(
                                    (float)$item['price'], 2
                                ) ?>
                            </td>

                            <td>
                                <?= (int)$item['quantity'] ?>
                            </td>

                            <td class="text-right">
                                ₹<?= number_format(
                                    (float)$item['subtotal'], 2
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="empty">
                            No products found for this order.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" class="text-right">
                            Order Total
                        </td>

                        <td class="text-right">
                            ₹<?= number_format(
                                (float)$order['total_amount'], 2
                            ) ?>
                        </td>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>

</div>

</body>
</html>