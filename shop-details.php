<?php
require_once __DIR__ . '/includes/maintenance-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

/* =========================================
   SESSION AND LOGIN SETUP
========================================= */

$isLoggedIn = isset($_SESSION['registered_user_id'])
    && (int) $_SESSION['registered_user_id'] > 0;

$customerName = $isLoggedIn
    ? ($_SESSION['registered_user_name'] ?? 'My Account')
    : '';

/* Use the same CSRF token as cart.php */
if (empty($_SESSION['cart_csrf_token'])) {
    $_SESSION['cart_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['cart_csrf_token'];

/* =========================================
   HELPER FUNCTIONS
========================================= */

function detailEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* =========================================
   LOAD PRODUCT
========================================= */

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId || $productId < 1) {
    header('Location: shop.php');
    exit;
}

$productSql = "
    SELECT id, name, price, image, category, description, created_at
    FROM products
    WHERE id = ? AND status = 'active'
    LIMIT 1
";

$productStmt = mysqli_prepare($conn, $productSql);

if (!$productStmt) {
    error_log('Product details query could not be prepared.');
    http_response_code(500);
    exit('Unable to load product details. Please try again later.');
}

mysqli_stmt_bind_param($productStmt, 'i', $productId);
mysqli_stmt_execute($productStmt);

$productResult = mysqli_stmt_get_result($productStmt);
$product = mysqli_fetch_assoc($productResult);

mysqli_stmt_close($productStmt);

if (!$product) {
    header('Location: shop.php');
    exit;
}

/* =========================================
   PRODUCT DESCRIPTION
========================================= */

$productName = $product['name'] ?? '';
$productPrice = (float) ($product['price'] ?? 0);
$productImage = $product['image'] ?? '';
$productCategory = $product['category'] ?? '';
$productDescription = $product['description'] ?? '';

/* =========================================
   LOAD CATEGORIES
========================================= */

$categories = [];

$categoryResult = mysqli_query(
    $conn,
    "SELECT category, COUNT(*) AS total
     FROM products
     WHERE status = 'active'
     GROUP BY category
     ORDER BY category ASC"
);

if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row;
    }
}

/* =========================================
   LOAD POPULAR PRODUCTS
========================================= */

$popularProducts = [];

$popularStmt = mysqli_prepare(
    $conn,
    "SELECT id, name, price, image
     FROM products
     WHERE status = 'active' AND id != ?
     ORDER BY id DESC
     LIMIT 4"
);

if ($popularStmt) {
    mysqli_stmt_bind_param($popularStmt, 'i', $productId);
    mysqli_stmt_execute($popularStmt);

    $popularResult = mysqli_stmt_get_result($popularStmt);

    while ($row = mysqli_fetch_assoc($popularResult)) {
        $popularProducts[] = $row;
    }

    mysqli_stmt_close($popularStmt);
}

/* =========================================
   SOCIAL LINKS
========================================= */

$socialLinks = [];

$socialResult = mysqli_query(
    $conn,
    "SELECT platform, url
     FROM social_links
     WHERE status = 'active'
     ORDER BY id ASC"
);

if ($socialResult) {
    while ($row = mysqli_fetch_assoc($socialResult)) {
        $socialLinks[$row['platform']] = $row['url'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="images/favicon.png" type="image/x-icon">

    <title>
        Mora Blog | <?= detailEscape($productName) ?>
    </title>

    <link href="css/style.css" rel="stylesheet">

    <style>
        .product-account {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px auto;
            padding: 14px 18px;
            background: #f8f8f8;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
        }

        .product-account-links {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .product-account-links a {
            color: #222;
            font-weight: 600;
            text-decoration: none;
        }

        .product-account-links a:hover {
            text-decoration: underline;
        }

        .detail-product-image {
            width: 100%;
            max-height: 520px;
            object-fit: contain;
        }

        .detail-product-description {
            margin: 20px 0;
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        .detail-cart-button {
            border: 0;
            cursor: pointer;
        }

        .value-section {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            list-style: none;
            padding-left: 0;
        }

        .value-section li {
            display: flex;
            align-items: center;
        }

        .value-section button[type="button"] {
            cursor: pointer;
        }

        #product-value {
            min-width: 28px;
            justify-content: center;
            font-weight: 600;
        }

        .detail-login-note {
            margin-top: 18px;
        }

        .detail-login-note a {
            font-weight: 600;
        }

        .detail-sidebar-product {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .detail-sidebar-product img {
            width: 75px;
            height: 75px;
            object-fit: cover;
        }

        .detail-sidebar-product a {
            overflow-wrap: anywhere;
        }

        .detail-sidebar-product h6 {
            margin-top: 8px;
        }

        .detail-category-list {
            padding-left: 18px;
        }

        .detail-category-list li {
            margin-bottom: 10px;
        }

        .detail-related {
            margin-top: 45px;
        }

        .detail-related .shop_items {
            margin-bottom: 25px;
        }

        @media (max-width: 576px) {
            .product-account {
                align-items: flex-start;
                flex-direction: column;
            }

            .value-section {
                gap: 8px;
            }

            .detail-cart-button {
                margin-top: 8px;
            }
        }
    </style>
</head>

<body class="background-transparent">

<!-- Header -->
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Navbar -->
<header class="main_header_area" id="header">
    <div class="container">
        <div class="header_menu">
            <?php include __DIR__ . '/includes/navbar.php'; ?>
        </div>
    </div>
</header>

<!-- Product details page -->
<main>

    <div class="container">

        <?php if ($isLoggedIn): ?>
            <div class="product-account">
                <div>
                    Welcome,
                    <strong><?= detailEscape($customerName) ?></strong>
                </div>

                <div class="product-account-links">
                    <a href="profile.php">My Profile</a>
                    <a href="profile/my-orders.php">My Orders</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page heading -->
        <div class="pages_heder">
            <h2>Product Details</h2>

            <ol class="breadcrumb">
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li>
                    <a href="shop.php">Shop</a>
                </li>
                <li>
                    <a href="shop-details.php?id=<?= (int) $productId ?>"
                       class="active">
                        Product Details
                    </a>
                </li>
            </ol>
        </div>

    </div>

    <!-- Product details and sidebar -->
    <section class="shop_with_sidebar">
        <div class="container">
            <div class="row">

                <!-- Sidebar -->
                <aside class="col-lg-3 shop_right_sidebar">

                    <!-- Search -->
                    <div class="s_widget">
                        <h4>Search Products</h4>

                        <form action="shop.php" method="GET">
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search Product"
                                >

                                <div class="input-group-append">
                                    <button
                                        type="submit"
                                        class="input-group-text theme_btn"
                                        aria-label="Search products"
                                    >
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Categories -->
                    <div class="s_widget">
                        <h4>Shop Categories</h4>

                        <ul class="detail-category-list">
                            <li>
                                <a href="shop.php">All Products</a>
                            </li>

                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="shop.php?category=<?= urlencode($category['category']) ?>">
                                        <?= detailEscape($category['category']) ?>
                                        (<?= (int) $category['total'] ?>)
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Popular products -->
                    <div class="s_widget popular_products">
                        <h4>Related Products</h4>

                        <?php if (!empty($popularProducts)): ?>

                            <?php foreach ($popularProducts as $popular): ?>
                                <div class="detail-sidebar-product">

                                    <a href="shop-details.php?id=<?= (int) $popular['id'] ?>">
                                        <img
                                            src="<?= detailEscape($popular['image']) ?>"
                                            alt="<?= detailEscape($popular['name']) ?>"
                                        >
                                    </a>

                                    <div>
                                        <a href="shop-details.php?id=<?= (int) $popular['id'] ?>">
                                            <?= detailEscape($popular['name']) ?>
                                        </a>

                                        <h6>
                                            $<?= number_format((float) $popular['price'], 2) ?>
                                        </h6>
                                    </div>

                                </div>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <p>No other products found.</p>
                        <?php endif; ?>
                    </div>

                </aside>

                <!-- Main product area -->
                <section class="col-lg-9 shop_left_sidebar">

                    <div class="row">

                        <!-- Product image -->
                        <div class="col-md-6">
                            <div class="shop_items">

                                <?php if ($productImage !== ''): ?>
                                    <img
                                        class="detail-product-image"
                                        src="<?= detailEscape($productImage) ?>"
                                        alt="<?= detailEscape($productName) ?>"
                                    >
                                <?php else: ?>
                                    <div class="detail-product-image">
                                        Product image unavailable.
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- Product information -->
                        <div class="col-md-6">
                            <div class="shop_text">

                                <h2>
                                    <?= detailEscape($productName) ?>
                                </h2>

                                <h3>
                                    $<?= number_format($productPrice, 2) ?>
                                </h3>

                                <?php if ($productCategory !== ''): ?>
                                    <p>
                                        <strong>Category:</strong>
                                        <?= detailEscape($productCategory) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="detail-product-description">
                                    <?php if (trim($productDescription) !== ''): ?>
                                        <?= nl2br(detailEscape($productDescription)) ?>
                                    <?php else: ?>
                                        <p>No description is available for this product.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Add to Cart form -->
                                <form action="cart.php" method="POST">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="add"
                                    >

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= (int) $productId ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= detailEscape($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="quantity"
                                        id="quantity-input"
                                        value="1"
                                    >

                                    <ul class="value-section">

                                        <li>
                                            <button
                                                type="button"
                                                class="tran3s"
                                                id="value-decrease"
                                                aria-label="Decrease quantity"
                                            >
                                                <i class="fa fa-angle-left"
                                                   aria-hidden="true"></i>
                                            </button>
                                        </li>

                                        <li id="product-value">1</li>

                                        <li>
                                            <button
                                                type="button"
                                                class="tran3s"
                                                id="value-increase"
                                                aria-label="Increase quantity"
                                            >
                                                <i class="fa fa-angle-right"
                                                   aria-hidden="true"></i>
                                            </button>
                                        </li>

                                        <li>
                                            <button
                                                type="submit"
                                                class="theme_btn detail-cart-button"
                                            >
                                                Add to Cart
                                            </button>
                                        </li>

                                    </ul>

                                </form>

                                <?php if (!$isLoggedIn): ?>
                                    <p class="detail-login-note">
                                        Please
                                        <a href="login.php">log in</a>
                                        to add this product to your cart.
                                    </p>
                                <?php endif; ?>

                                <p>
                                    <a href="shop.php">Continue Shopping</a>
                                </p>

                            </div>
                        </div>

                    </div>

                    <!-- Description tab -->
                    <div class="row">
                        <div class="col-12">
                            <div class="detail-related">

                                <h3>Product Description</h3>

                                <div class="detail-product-description">
                                    <?php if (trim($productDescription) !== ''): ?>
                                        <?= nl2br(detailEscape($productDescription)) ?>
                                    <?php else: ?>
                                        No additional product information is available.
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Related products -->
                    <?php if (!empty($popularProducts)): ?>
                        <div class="detail-related">

                            <div class="feature_tittle">
                                <h2>You May Also Like</h2>

                                <a href="shop.php">
                                    View All Products
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="row">

                                <?php foreach (array_slice($popularProducts, 0, 3) as $related): ?>

                                    <div class="col-md-4 col-sm-6">
                                        <div class="shop_items">

                                            <a
                                                href="shop-details.php?id=<?= (int) $related['id'] ?>"
                                                class="shop_img"
                                            >
                                                <img
                                                    src="<?= detailEscape($related['image']) ?>"
                                                    alt="<?= detailEscape($related['name']) ?>"
                                                >
                                            </a>

                                            <div class="shop_text">
                                                <a
                                                    href="shop-details.php?id=<?= (int) $related['id'] ?>"
                                                    class="s_heding"
                                                >
                                                    <?= detailEscape($related['name']) ?>
                                                </a>

                                                <h4>
                                                    $<?= number_format((float) $related['price'], 2) ?>
                                                </h4>

                                                <a
                                                    href="shop-details.php?id=<?= (int) $related['id'] ?>"
                                                    class="theme_btn"
                                                >
                                                    View Product
                                                </a>
                                            </div>

                                        </div>
                                    </div>

                                <?php endforeach; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Social links -->
                    <?php if (!empty($socialLinks)): ?>
                        <div class="detail-related">
                            <h3>Connect With Us</h3>

                            <ul class="social_icon">
                                <?php foreach ($socialLinks as $platform => $url): ?>
                                    <li>
                                        <a
                                            href="<?= detailEscape($url) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="<?= detailEscape($platform) ?>"
                                        >
                                            <?= detailEscape(ucfirst($platform)) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </section>

            </div>
        </div>
    </section>

</main>

<!-- Footer -->
<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/theme.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const decreaseButton = document.getElementById('value-decrease');
    const increaseButton = document.getElementById('value-increase');
    const quantityInput = document.getElementById('quantity-input');
    const quantityDisplay = document.getElementById('product-value');

    if (!decreaseButton || !increaseButton || !quantityInput || !quantityDisplay) {
        return;
    }

    let quantity = 1;

    function updateQuantity() {
        quantity = Math.max(1, Math.min(99, quantity));

        quantityInput.value = quantity;
        quantityDisplay.textContent = quantity;
    }

    decreaseButton.addEventListener('click', function () {
        quantity--;
        updateQuantity();
    });

    increaseButton.addEventListener('click', function () {
        quantity++;
        updateQuantity();
    });

    updateQuantity();
});
</script>

</body>
</html>
