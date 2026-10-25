
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

/* -----------------------------------------
   REQUIRE CUSTOMER LOGIN
----------------------------------------- */

$isLoggedIn = isset($_SESSION['registered_user_id'])
    && (int) $_SESSION['registered_user_id'] > 0;

if (!$isLoggedIn) {
    // Login ke baad checkout par wapas bhejne ke liye
    $_SESSION['redirect_after_login'] = '/mora-blog-second/checkout.php';

    header('Location: /mora-blog-second/login.php');
    exit;
}

/* -----------------------------------------
   INITIAL SETUP
----------------------------------------- */

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (empty($_SESSION['checkout_csrf'])) {
    $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = 'error';

$customerName = $_SESSION['registered_user_name'] ?? '';
$customerEmail = $_SESSION['registered_user_email'] ?? '';
$customerPhone = '';
$address = '';
$city = '';
$state = '';
$pincode = '';

$successOrder = $_SESSION['checkout_success'] ?? null;
unset($_SESSION['checkout_success']);


/* -----------------------------------------
   LOAD ACTIVE PRODUCTS FROM CART
----------------------------------------- */

function checkoutEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getCheckoutProducts($conn, &$cart)
{
    $products = [];
    $cleanCart = [];

    foreach ($cart as $productId => $quantity) {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

        if (!$productId || !$quantity || $quantity < 1) {
            continue;
        }

        $quantity = min($quantity, 99);

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, price, image
             FROM products
             WHERE id = ? AND status = 'active'
             LIMIT 1"
        );

        if (!$stmt) {
            continue;
        }

        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$product) {
            continue;
        }

        $product['quantity'] = $quantity;
        $product['subtotal'] = (float)$product['price'] * $quantity;

        $products[] = $product;
        $cleanCart[$productId] = $quantity;
    }

    $cart = $cleanCart;

    return $products;
}

$cartProducts = getCheckoutProducts($conn, $_SESSION['cart']);

$totalAmount = 0;

foreach ($cartProducts as $item) {
    $totalAmount += $item['subtotal'];
}


/* -----------------------------------------
   PLACE ORDER
----------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['checkout_csrf'], $token)
    ) {
        $message = 'Invalid request. Please refresh and try again.';
    } elseif (empty($cartProducts)) {
        $message = 'Your cart is empty. Please add products first.';
    } else {

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        if (
            $customerName === '' ||
            $customerEmail === '' ||
            $customerPhone === '' ||
            $address === '' ||
            $city === '' ||
            $state === '' ||
            $pincode === ''
        ) {
            $message = 'Please fill in all delivery details.';
        } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $customerPhone)) {
            $message = 'Please enter a valid phone number.';
        } elseif (!preg_match('/^[A-Za-z0-9 -]{4,15}$/', $pincode)) {
            $message = 'Please enter a valid postal PIN code.';
        } elseif (strlen($customerName) > 100 || strlen($customerEmail) > 150) {
            $message = 'Name or email is too long.';
        } else {

            /*
             * Recheck products and prices from database
             * before saving the order.
             */
            $cartProducts = getCheckoutProducts($conn, $_SESSION['cart']);
            $totalAmount = 0;

            foreach ($cartProducts as $item) {
                $totalAmount += $item['subtotal'];
            }

            if (empty($cartProducts)) {
                $message = 'Your cart no longer contains available products.';
            } else {

                $userId = isset($_SESSION['registered_user_id'])
                    ? (int)$_SESSION['registered_user_id']
                    : null;

                $orderNumber = 'MB-' . date('Ymd') . '-' .
                    strtoupper(bin2hex(random_bytes(4)));

                $orderStatus = 'pending';
                $paymentMethod = 'cash_on_delivery';

                mysqli_begin_transaction($conn);

                try {

                    $orderSql = "
                        INSERT INTO orders (
                            order_number,
                            registered_user_id,
                            customer_name,
                            customer_email,
                            customer_phone,
                            address,
                            city,
                            state,
                            pincode,
                            total_amount,
                            status,
                            payment_method
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ";

                    $orderStmt = mysqli_prepare($conn, $orderSql);

                    if (!$orderStmt) {
                        throw new Exception('Could not prepare order query.');
                    }

                    mysqli_stmt_bind_param(
                        $orderStmt,
                        "sisssssssdss",
                        $orderNumber,
                        $userId,
                        $customerName,
                        $customerEmail,
                        $customerPhone,
                        $address,
                        $city,
                        $state,
                        $pincode,
                        $totalAmount,
                        $orderStatus,
                        $paymentMethod
                    );

                    if (!mysqli_stmt_execute($orderStmt)) {
                        throw new Exception('Could not save order.');
                    }

                    $orderId = mysqli_insert_id($conn);
                    mysqli_stmt_close($orderStmt);


                    $itemSql = "
                        INSERT INTO order_items (
                            order_id,
                            product_id,
                            product_name,
                            price,
                            quantity,
                            subtotal
                        )
                        VALUES (?, ?, ?, ?, ?, ?)
                    ";

                    $itemStmt = mysqli_prepare($conn, $itemSql);

                    if (!$itemStmt) {
                        throw new Exception('Could not prepare order items.');
                    }

                    foreach ($cartProducts as $item) {

                        $productId = (int)$item['id'];
                        $productName = $item['name'];
                        $price = (float)$item['price'];
                        $quantity = (int)$item['quantity'];
                        $subtotal = $price * $quantity;

                        mysqli_stmt_bind_param(
                            $itemStmt,
                            "iisdid",
                            $orderId,
                            $productId,
                            $productName,
                            $price,
                            $quantity,
                            $subtotal
                        );

                        if (!mysqli_stmt_execute($itemStmt)) {
                            throw new Exception('Could not save order items.');
                        }
                    }

                    mysqli_stmt_close($itemStmt);

                    mysqli_commit($conn);

                    $_SESSION['cart'] = [];
                    $_SESSION['checkout_success'] = [
                        'order_number' => $orderNumber,
                        'total_amount' => $totalAmount
                    ];

                    header('Location: checkout.php');
                    exit;

                } catch (Throwable $e) {

                    mysqli_rollback($conn);

                    error_log('Checkout error: ' . $e->getMessage());

                    $message = 'We could not place your order. Please try again.';
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mora Blog | Checkout</title>

    <link href="css/style.css" rel="stylesheet">
    <link href="css/modern.css" rel="stylesheet">

    <style>
        .checkout-wrapper {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 18px;
        }

        .checkout-title {
            margin-bottom: 30px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 25px;
        }

        .checkout-box {
            background: #fff;
            padding: 25px;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .checkout-box h3 {
            margin-bottom: 20px;
        }

        .checkout-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .checkout-form input,
        .checkout-form textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }

        .checkout-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        .checkout-form button,
        .checkout-link {
            display: inline-block;
            border: none;
            padding: 12px 20px;
            background: #222;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .checkout-form button {
            width: 100%;
        }

        .checkout-message {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .checkout-error {
            background: #ffe9e9;
            color: #9b1717;
        }

        .checkout-success {
            background: #e8f8ec;
            color: #17652a;
        }

        .checkout-product {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .checkout-total {
            font-size: 20px;
            font-weight: 700;
            margin: 20px 0;
        }

        .checkout-small {
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="background-transparent">

<?php include __DIR__ . '/includes/header.php'; ?>

<header class="main_header_area" id="header">
    <div class="container">
        <div class="header_menu">
            <?php include __DIR__ . '/includes/navbar.php'; ?>
        </div>
    </div>
</header>

<main class="checkout-wrapper">

    <h1 class="checkout-title">Checkout</h1>

    <?php if ($successOrder): ?>

        <div class="checkout-box">
            <div class="checkout-message checkout-success">
                <h2>Order Placed Successfully!</h2>
                <p>Thank you for shopping with Mora Blog.</p>
            </div>

            <p>
                <strong>Order Number:</strong>
                <?= checkoutEscape($successOrder['order_number']) ?>
            </p>

            <p>
                <strong>Total Amount:</strong>
                $<?= number_format((float)$successOrder['total_amount'], 2) ?>
            </p>

            <p><strong>Payment:</strong> Cash on Delivery</p>
            <p><strong>Status:</strong> Pending</p>

            <a href="shop.php" class="checkout-link">Continue Shopping</a>
        </div>

    <?php elseif (!empty($message)): ?>

        <div class="checkout-message checkout-error">
            <?= checkoutEscape($message) ?>
        </div>

    <?php endif; ?>


    <?php if (!$successOrder && !empty($cartProducts)): ?>

        <div class="checkout-grid">

            <section class="checkout-box">
                <h3>Delivery Details</h3>

                <form method="POST" action="checkout.php" class="checkout-form">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= checkoutEscape($_SESSION['checkout_csrf']) ?>"
                    >

                    <label for="customer_name">Full Name</label>
                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        maxlength="100"
                        required
                        value="<?= checkoutEscape($customerName) ?>"
                    >

                    <label for="customer_email">Email Address</label>
                    <input
                        type="email"
                        id="customer_email"
                        name="customer_email"
                        maxlength="150"
                        required
                        value="<?= checkoutEscape($customerEmail) ?>"
                    >

                    <label for="customer_phone">Phone Number</label>
                    <input
                        type="tel"
                        id="customer_phone"
                        name="customer_phone"
                        maxlength="20"
                        required
                        value="<?= checkoutEscape($customerPhone) ?>"
                    >

                    <label for="address">Full Address</label>
                    <textarea
                        id="address"
                        name="address"
                        required
                    ><?= checkoutEscape($address) ?></textarea>

                    <label for="city">City</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        maxlength="100"
                        required
                        value="<?= checkoutEscape($city) ?>"
                    >

                    <label for="state">State</label>
                    <input
                        type="text"
                        id="state"
                        name="state"
                        maxlength="100"
                        required
                        value="<?= checkoutEscape($state) ?>"
                    >

                    <label for="pincode">PIN Code</label>
                    <input
                        type="text"
                        id="pincode"
                        name="pincode"
                        maxlength="15"
                        required
                        value="<?= checkoutEscape($pincode) ?>"
                    >

                    <p><strong>Payment Method:</strong> Cash on Delivery</p>

                    <button type="submit">Place Order</button>

                </form>
            </section>


            <aside class="checkout-box">
                <h3>Your Order</h3>

                <?php foreach ($cartProducts as $item): ?>

                    <div class="checkout-product">
                        <div>
                            <strong>
                                <?= checkoutEscape($item['name']) ?>
                            </strong>

                            <div class="checkout-small">
                                Quantity: <?= (int)$item['quantity'] ?>
                            </div>
                        </div>

                        <div>
                            $<?= number_format($item['subtotal'], 2) ?>
                        </div>
                    </div>

                <?php endforeach; ?>

                <div class="checkout-total">
                    Total: $<?= number_format($totalAmount, 2) ?>
                </div>

                <a href="cart.php" class="checkout-link">
                    Back to Cart
                </a>
            </aside>

        </div>

    <?php elseif (!$successOrder): ?>

        <div class="checkout-box">
            <p>Your cart is empty. Add some products before checkout.</p>
            <a href="shop.php" class="checkout-link">Browse Products</a>
        </div>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/theme.js"></script>

</body>
</html>
