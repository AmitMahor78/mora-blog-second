<?php
session_start();

require_once __DIR__ . '/includes/db.php';

/* =========================================================
   LOGIN VERIFICATION
========================================================= */

if (
    !isset($_SESSION['registered_user_id']) ||
    (int) $_SESSION['registered_user_id'] < 1
) {
    // Preserve the page to return to after login.
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        ($_POST['action'] ?? '') === 'add'
    ) {
        $productId = filter_input(
            INPUT_POST,
            'product_id',
            FILTER_VALIDATE_INT
        );

        if ($productId && $productId > 0) {
            $_SESSION['redirect_after_login'] =
                'shop-details.php?id=' . $productId;
        } else {
            $_SESSION['redirect_after_login'] = 'shop.php';
        }
    } else {
        $_SESSION['redirect_after_login'] = 'cart.php';
    }

    header('Location: login.php');
    exit;
}

$customerId = (int) $_SESSION['registered_user_id'];
$customerName = $_SESSION['registered_user_name'] ?? 'My Account';


/* =========================================================
   CART INITIALIZATION
========================================================= */

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function cartEscape($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function cartRedirect()
{
    header('Location: cart.php');
    exit;
}


/* =========================================================
   CSRF TOKEN
========================================================= */

if (empty($_SESSION['cart_csrf_token'])) {
    $_SESSION['cart_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['cart_csrf_token'];

$error = '';


/* =========================================================
   HANDLE CART ACTIONS
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
       ADD PRODUCT TO CART

       Supports product_id and quantity from shop-details.php.
    */

    if ($action === 'add') {

        $productId = filter_input(
            INPUT_POST,
            'product_id',
            FILTER_VALIDATE_INT
        );

        $quantity = filter_input(
            INPUT_POST,
            'quantity',
            FILTER_VALIDATE_INT
        );

        $submittedToken = $_POST['csrf_token'] ?? '';

        // Validate CSRF token.
        if (
            !is_string($submittedToken) ||
            !hash_equals($csrfToken, $submittedToken)
        ) {
            $_SESSION['cart_message'] =
                'Invalid request. Please refresh the product page and try again.';

            cartRedirect();
        }

        // Validate product ID.
        if (!$productId || $productId < 1) {
            $_SESSION['cart_message'] =
                'Invalid product selected.';

            cartRedirect();
        }

        // Validate requested quantity.
        if (
            $quantity === false ||
            $quantity === null ||
            $quantity < 1 ||
            $quantity > 99
        ) {
            $_SESSION['cart_message'] =
                'Quantity must be between 1 and 99.';

            cartRedirect();
        }

        // Verify the product is active.
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id
             FROM products
             WHERE id = ?
               AND status = 'active'
             LIMIT 1"
        );

        if (!$stmt) {
            $_SESSION['cart_message'] =
                'Unable to check the product. Please try again.';

            cartRedirect();
        }

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $productId
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $productExists = mysqli_stmt_num_rows($stmt) > 0;

        mysqli_stmt_close($stmt);

        if (!$productExists) {
            $_SESSION['cart_message'] =
                'This product is unavailable or no longer published.';

            cartRedirect();
        }

        // Add requested quantity to the existing quantity.
        $currentQuantity = (int) (
            $_SESSION['cart'][$productId] ?? 0
        );

        if ($currentQuantity + $quantity > 99) {
            $_SESSION['cart_message'] =
                'Maximum quantity per product is 99.';

            cartRedirect();
        }

        $_SESSION['cart'][$productId] =
            $currentQuantity + $quantity;

        $_SESSION['cart_message'] =
            'Product added to your cart successfully.';

        cartRedirect();
    }


    /*
       VERIFY CSRF TOKEN FOR OTHER ACTIONS
    */

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($csrfToken, $submittedToken)
    ) {
        $error =
            'Invalid request. Please refresh the page and try again.';
    }


    /*
       UPDATE CART
    */

    elseif ($action === 'update') {

        $quantities = $_POST['quantity'] ?? [];

        if (!is_array($quantities)) {

            $error = 'Invalid quantity information.';

        } else {

            $updatedCart = $_SESSION['cart'];

            foreach ($quantities as $id => $quantity) {

                $productId = filter_var(
                    $id,
                    FILTER_VALIDATE_INT
                );

                $quantity = filter_var(
                    $quantity,
                    FILTER_VALIDATE_INT
                );

                if (
                    !$productId ||
                    !isset($_SESSION['cart'][$productId])
                ) {
                    continue;
                }

                if (
                    $quantity === false ||
                    $quantity < 1
                ) {
                    $error =
                        'Quantity must be at least 1. Use Remove to delete a product.';

                    break;
                }

                if ($quantity > 99) {
                    $error =
                        'Maximum quantity per product is 99.';

                    break;
                }

                $updatedCart[$productId] = $quantity;
            }

            if ($error === '') {

                $_SESSION['cart'] = $updatedCart;

                $_SESSION['cart_message'] =
                    'Cart updated successfully.';

                cartRedirect();
            }
        }
    }


    /*
       REMOVE PRODUCT
    */

    elseif ($action === 'remove') {

        $productId = filter_input(
            INPUT_POST,
            'product_id',
            FILTER_VALIDATE_INT
        );

        if (
            $productId &&
            isset($_SESSION['cart'][$productId])
        ) {
            unset($_SESSION['cart'][$productId]);

            $_SESSION['cart_message'] =
                'Product removed from your cart.';
        } else {
            $_SESSION['cart_message'] =
                'Product was not found in your cart.';
        }

        cartRedirect();
    }


    /*
       CLEAR CART
    */

    elseif ($action === 'clear') {

        $_SESSION['cart'] = [];

        $_SESSION['cart_message'] =
            'Your cart has been cleared.';

        cartRedirect();
    }
}


/* =========================================================
   READ ONE-TIME MESSAGE
========================================================= */

$message = $_SESSION['cart_message'] ?? '';

unset($_SESSION['cart_message']);


/* =========================================================
   FETCH CURRENT PRODUCT DETAILS
========================================================= */

$cartProducts = [];
$subtotal = 0.0;

foreach ($_SESSION['cart'] as $id => $quantity) {

    $productId = (int) $id;
    $quantity = (int) $quantity;

    if (
        $productId < 1 ||
        $quantity < 1 ||
        $quantity > 99
    ) {
        unset($_SESSION['cart'][$id]);
        continue;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, price, image
         FROM products
         WHERE id = ?
           AND status = 'active'
         LIMIT 1"
    );

    if (!$stmt) {
        $error = 'Unable to load cart products.';
        break;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $productId
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $dbId,
        $dbName,
        $dbPrice,
        $dbImage
    );

    if (mysqli_stmt_fetch($stmt)) {

        $price = (float) $dbPrice;
        $lineTotal = $price * $quantity;

        $cartProducts[] = [
            'id' => (int) $dbId,
            'name' => $dbName,
            'price' => $price,
            'image' => $dbImage,
            'quantity' => $quantity,
            'line_total' => $lineTotal
        ];

        $subtotal += $lineTotal;

    } else {

        // Remove deleted or inactive products.
        unset($_SESSION['cart'][$id]);
    }

    mysqli_stmt_close($stmt);
}


/* =========================================================
   TOTAL ITEMS
========================================================= */

$totalItems = 0;

foreach ($cartProducts as $item) {
    $totalItems += $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Mora Blog | Shopping Cart</title>

    <link
        rel="icon"
        href="images/favicon.png"
        type="image/x-icon"
    >

    <link
        href="css/style.css"
        rel="stylesheet"
    >

    <style>
        .cart-page {
            padding: 45px 0;
        }

        .cart-heading {
            margin-bottom: 25px;
        }

        .cart-alert {
            background: #e3f5e8;
            color: #176534;
            padding: 14px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .cart-error {
            background: #ffe3e3;
            color: #a32121;
            padding: 14px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .cart-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .cart-table {
            width: 100%;
            min-width: 680px;
            border-collapse: collapse;
            background: white;
        }

        .cart-table th,
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }

        .cart-table th {
            background: #f6f7f9;
        }

        .cart-product {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-product img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 5px;
        }

        .cart-quantity {
            width: 80px;
            padding: 9px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .cart-button {
            display: inline-block;
            border: 0;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .cart-primary {
            background: #1769e0;
            color: white;
        }

        .cart-secondary {
            background: #e8ebf0;
            color: #222;
        }

        .cart-danger {
            background: #d93636;
            color: white;
        }

        .cart-summary {
            max-width: 430px;
            margin: 25px 0 0 auto;
            padding: 22px;
            background: #f7f8fa;
            border-radius: 7px;
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 15px;
        }

        .cart-summary-total {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            font-size: 20px;
            font-weight: bold;
        }

        .cart-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .cart-empty {
            text-align: center;
            background: #f7f8fa;
            padding: 45px 20px;
            border-radius: 8px;
        }

        .cart-empty h3 {
            margin-top: 0;
        }

        .cart-note {
            color: #666;
            font-size: 13px;
            line-height: 1.5;
            margin-top: 15px;
        }

        /* Logged-in customer indicator */

        .customer-profile-indicator {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 10px 15px;
            background: #f7f8fa;
            border: 1px solid #e7e7e7;
            border-radius: 6px;
            margin: 12px 0;
        }

        .customer-profile-indicator span {
            font-weight: 600;
        }

        .customer-profile-indicator a {
            color: #1769e0;
            text-decoration: none;
        }

        .customer-profile-indicator a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .cart-page {
                padding: 25px 0;
            }

            .cart-summary {
                max-width: 100%;
            }

            .customer-profile-indicator {
                justify-content: flex-start;
            }
        }
    </style>

</head>

<body class="background-transparent">


<!-- HEADER -->

<?php include "includes/header.php"; ?>


<!-- NAVBAR -->

<header class="main_header_area" id="header">

    <div class="container">

        <div class="header_menu">

            <?php include "includes/navbar.php"; ?>

        </div>

    </div>

</header>


<!-- CUSTOMER PROFILE INDICATOR -->

<div class="container">

    <div style="text-align: right;">

        <div class="customer-profile-indicator">

            <span>
                Welcome, <?= cartEscape($customerName) ?>
            </span>

            <a href="profile.php">
                My Profile
            </a>

            <a href="profile/my-orders.php">
                My Orders
            </a>

        </div>

    </div>

</div>


<!-- PAGE HEADER -->

<div class="container">

    <div class="pages_heder">

        <h2>Shopping Cart</h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="shop.php">Shop</a>
            </li>

            <li>
                <a href="cart.php" class="active">Cart</a>
            </li>

        </ol>

    </div>

</div>


<!-- CART CONTENT -->

<main class="container cart-page">

    <div class="cart-heading">

        <h2>Your Shopping Cart</h2>

        <p>
            You have <?= (int) $totalItems ?> item(s) in your cart.
        </p>

    </div>


    <?php if ($message !== ''): ?>

        <div class="cart-alert">
            <?= cartEscape($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="cart-error">
            <?= cartEscape($error) ?>
        </div>

    <?php endif; ?>


    <?php if (count($cartProducts) > 0): ?>


        <!-- UPDATE CART FORM -->

        <form action="cart.php" method="POST">

            <input
                type="hidden"
                name="action"
                value="update"
            >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= cartEscape($csrfToken) ?>"
            >


            <div class="cart-table-wrap">

                <table class="cart-table">

                    <thead>

                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Remove</th>
                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($cartProducts as $item): ?>

                        <tr>

                            <td>

                                <div class="cart-product">

                                    <?php if (!empty($item['image'])): ?>

                                        <img
                                            src="<?= cartEscape($item['image']) ?>"
                                            alt="<?= cartEscape($item['name']) ?>"
                                        >

                                    <?php endif; ?>

                                    <span>
                                        <?= cartEscape($item['name']) ?>
                                    </span>

                                </div>

                            </td>


                            <td>
                                $<?= number_format($item['price'], 2) ?>
                            </td>


                            <td>

                                <input
                                    class="cart-quantity"
                                    type="number"
                                    name="quantity[<?= (int) $item['id'] ?>]"
                                    value="<?= (int) $item['quantity'] ?>"
                                    min="1"
                                    max="99"
                                    required
                                >

                            </td>


                            <td>
                                $<?= number_format($item['line_total'], 2) ?>
                            </td>


                            <td>

                                <button
                                    class="cart-button cart-danger"
                                    type="submit"
                                    form="remove-<?= (int) $item['id'] ?>"
                                >
                                    Remove
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <div class="cart-actions">

                <button
                    type="submit"
                    class="cart-button cart-primary"
                >
                    Update Cart
                </button>

                <a
                    href="shop.php"
                    class="cart-button cart-secondary"
                >
                    Continue Shopping
                </a>

            </div>

        </form>


        <!-- REMOVE PRODUCT FORMS -->

        <?php foreach ($cartProducts as $item): ?>

            <form
                id="remove-<?= (int) $item['id'] ?>"
                action="cart.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="action"
                    value="remove"
                >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) $item['id'] ?>"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= cartEscape($csrfToken) ?>"
                >

            </form>

        <?php endforeach; ?>


        <!-- CART SUMMARY -->

        <section class="cart-summary">

            <h3>Cart Summary</h3>


            <div class="cart-summary-row">

                <span>Total Items</span>

                <span>
                    <?= (int) $totalItems ?>
                </span>

            </div>


            <div class="cart-summary-row cart-summary-total">

                <span>Subtotal</span>

                <span>
                    $<?= number_format($subtotal, 2) ?>
                </span>

            </div>


            <p class="cart-note">
                Shipping charges and final order details will be
                calculated during checkout.
            </p>


            <div class="cart-actions">

                <a
                    href="shop.php"
                    class="cart-button cart-secondary"
                >
                    Continue Shopping
                </a>

                <a
                    href="checkout.php"
                    class="cart-button cart-primary"
                >
                    Proceed to Checkout
                </a>

                <form
                    action="cart.php"
                    method="POST"
                    onsubmit="return confirm('Clear all products from your cart?');"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="clear"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= cartEscape($csrfToken) ?>"
                    >

                    <button
                        type="submit"
                        class="cart-button cart-danger"
                    >
                        Clear Cart
                    </button>

                </form>

            </div>

        </section>


    <?php else: ?>


        <!-- EMPTY CART -->

        <div class="cart-empty">

            <h3>Your cart is empty</h3>

            <p>
                Explore our shop and add products to your cart.
            </p>

            <a
                href="shop.php"
                class="cart-button cart-primary"
            >
                Browse Products
            </a>

        </div>


    <?php endif; ?>

</main>


<!-- FOOTER -->

<?php include "includes/footer.php"; ?>


<button class="scroll-top">
    <i class="fa fa-angle-double-up"></i>
</button>

<div class="preloader"></div>


<!-- SCRIPTS -->

<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="vendors/animate-css/wow.min.js"></script>
<script src="js/theme.js"></script>


</body>
</html>