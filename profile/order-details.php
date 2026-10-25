<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

// Check if customer is logged in
if (!isset($_SESSION['registered_user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int) $_SESSION['registered_user_id'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId < 1) {
    header("Location: my-orders.php");
    exit;
}

// Fetch order belonging to this customer
$orderStmt = mysqli_prepare(
    $conn,
    "SELECT id, order_number, customer_name, customer_email, customer_phone,
            address, city, state, pincode, total_amount, status,
            payment_method, created_at
     FROM orders
     WHERE id = ? AND registered_user_id = ?
     LIMIT 1"
);

if (!$orderStmt) {
    die("Database query error.");
}

mysqli_stmt_bind_param($orderStmt, "ii", $orderId, $userId);
mysqli_stmt_execute($orderStmt);
$orderResult = mysqli_stmt_get_result($orderStmt);
$order = mysqli_fetch_assoc($orderResult);
mysqli_stmt_close($orderStmt);

if (!$order) {
    header("Location: my-orders.php");
    exit;
}

// Fetch ordered items
$itemsStmt = mysqli_prepare(
    $conn,
    "SELECT product_id, product_name, price, quantity, subtotal
     FROM order_items
     WHERE order_id = ?"
);

mysqli_stmt_bind_param($itemsStmt, "i", $orderId);
mysqli_stmt_execute($itemsStmt);
$itemsResult = mysqli_stmt_get_result($itemsStmt);
$items = [];
while ($row = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $row;
}
mysqli_stmt_close($itemsStmt);

function esc($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../images/favicon.png" type="image/x-icon">
    <title>Order Details #<?= esc($order['order_number']) ?> | Mora Blog</title>
    
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/modern.css" rel="stylesheet">
    
    <style>
        .order_container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 15px;
        }
        .order_card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .order_header {
            background: #0f172a;
            color: #ffffff;
            padding: 24px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .order_header h2 {
            font-size: 20px;
            margin: 0;
            color: #ffffff !important;
            font-weight: 700;
        }
        .order_body {
            padding: 30px;
        }
        .info_grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .info_item h6 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 700;
        }
        .info_item p {
            font-size: 14px;
            color: #0f172a;
            margin: 0;
            font-weight: 600;
        }
        .order_table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .order_table th {
            background: #f1f5f9;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        .order_table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #1e293b;
        }
        .order_summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }
        .summary_box {
            width: 320px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 18px 20px;
        }
        .summary_row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #64748b;
        }
        .summary_row.total {
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 18px;
            font-weight: 800;
            color: #e11d48;
        }
    </style>
</head>
<body>

    <div class="order_container">
        
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="my-orders.php" class="btn btn-sm btn-outline-secondary" style="border-radius: 20px; font-weight: 600;">
                <i class="fa fa-arrow-left"></i> Back to My Orders
            </a>
            <div>
                <button onclick="window.print();" class="btn btn-sm btn-light" style="border-radius: 20px; font-weight: 600; border: 1px solid #cbd5e1;">
                    <i class="fa fa-print"></i> Print Invoice
                </button>
                <a href="../shop.php" class="btn btn-sm btn-primary" style="border-radius: 20px; font-weight: 600; margin-left: 8px;">
                    <i class="fa fa-shopping-cart"></i> Shop More
                </a>
            </div>
        </div>

        <div class="order_card">
            
            <div class="order_header">
                <div>
                    <h2>Order #<?= esc($order['order_number']) ?></h2>
                    <span style="font-size: 13px; color: #94a3b8;">
                        Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                    </span>
                </div>
                <div>
                    <span class="status-badge status-<?= strtolower(esc($order['status'])) ?>">
                        <?= strtoupper(esc($order['status'])) ?>
                    </span>
                </div>
            </div>

            <div class="order_body">
                
                <div class="info_grid">
                    <div class="info_item">
                        <h6>Customer Details</h6>
                        <p><?= esc($order['customer_name']) ?></p>
                        <p style="font-size: 13px; color: #64748b;"><?= esc($order['customer_email']) ?></p>
                        <p style="font-size: 13px; color: #64748b;"><?= esc($order['customer_phone']) ?></p>
                    </div>

                    <div class="info_item">
                        <h6>Shipping Address</h6>
                        <p><?= esc($order['address']) ?></p>
                        <p style="font-size: 13px; color: #64748b;">
                            <?= esc($order['city']) ?>, <?= esc($order['state']) ?> - <?= esc($order['pincode']) ?>
                        </p>
                    </div>

                    <div class="info_item">
                        <h6>Payment Information</h6>
                        <p><?= ucwords(str_replace('_', ' ', esc($order['payment_method']))) ?></p>
                        <p style="font-size: 13px; color: #16a34a; font-weight: 700;">
                            Status: Confirmed
                        </p>
                    </div>
                </div>

                <h5 style="margin-bottom: 16px; font-weight: 700; color: #0f172a;">Ordered Items</h5>
                
                <table class="order_table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td style="font-weight: 600;">
                                    <?= esc($item['product_name']) ?>
                                </td>
                                <td style="text-align: right;">
                                    $<?= number_format((float)$item['price'], 2) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?= (int)$item['quantity'] ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                    $<?= number_format((float)$item['subtotal'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="order_summary">
                    <div class="summary_box">
                        <div class="summary_row">
                            <span>Subtotal:</span>
                            <span>$<?= number_format((float)$order['total_amount'], 2) ?></span>
                        </div>
                        <div class="summary_row">
                            <span>Delivery:</span>
                            <span style="color: #16a34a; font-weight: 700;">FREE</span>
                        </div>
                        <div class="summary_row total">
                            <span>Total Paid:</span>
                            <span>$<?= number_format((float)$order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>
</html>
