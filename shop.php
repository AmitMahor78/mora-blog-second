<?php

require_once __DIR__ . '/includes/maintenance-check.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include "includes/db.php";

/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

/* =========================================================
   CATEGORY FILTER
========================================================= */

$selectedCategory = isset($_GET['category'])
    ? trim($_GET['category'])
    : '';

/* =========================================================
   PAGINATION
========================================================= */

$productsPerPage = 6;

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $productsPerPage;


/* =========================================================
   ESCAPE SEARCH / CATEGORY
========================================================= */

$searchSafe = mysqli_real_escape_string($conn, $search);
$categorySafe = mysqli_real_escape_string($conn, $selectedCategory);


/* =========================================================
   TOTAL PRODUCTS
========================================================= */

$countQuery = "
    SELECT COUNT(*) AS total
    FROM products
    WHERE status = 'active'
";

if ($search !== '') {
    $countQuery .= "
        AND (
            name LIKE '%$searchSafe%'
            OR description LIKE '%$searchSafe%'
            OR category LIKE '%$searchSafe%'
        )
    ";
}

if ($selectedCategory !== '') {
    $countQuery .= "
        AND category = '$categorySafe'
    ";
}

$countResult = mysqli_query($conn, $countQuery);

$totalProducts = 0;

if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalProducts = (int) $countRow['total'];
}

$totalPages = (int) ceil($totalProducts / $productsPerPage);


/* =========================================================
   PRODUCTS
========================================================= */

$productQuery = "
    SELECT
        id,
        name,
        price,
        image,
        category,
        description,
        created_at
    FROM products
    WHERE status = 'active'
";

if ($search !== '') {
    $productQuery .= "
        AND (
            name LIKE '%$searchSafe%'
            OR description LIKE '%$searchSafe%'
            OR category LIKE '%$searchSafe%'
        )
    ";
}

if ($selectedCategory !== '') {
    $productQuery .= "
        AND category = '$categorySafe'
    ";
}

$productQuery .= "
    ORDER BY id DESC
    LIMIT $productsPerPage OFFSET $offset
";

$productResult = mysqli_query($conn, $productQuery);


/* =========================================================
   SHOP CATEGORIES
========================================================= */

$categoryQuery = "
    SELECT
        category,
        COUNT(*) AS total
    FROM products
    WHERE status = 'active'
    GROUP BY category
    ORDER BY category ASC
";

$categoryResult = mysqli_query($conn, $categoryQuery);


/* =========================================================
   POPULAR PRODUCTS
========================================================= */

$popularQuery = "
    SELECT
        id,
        name,
        price,
        image
    FROM products
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 4
";

$popularResult = mysqli_query($conn, $popularQuery);


/* =========================================================
   SOCIAL LINKS
========================================================= */

$socialQuery = "
    SELECT platform, url
    FROM social_links
    WHERE status = 'active'
    ORDER BY id ASC
";

$socialResult = mysqli_query($conn, $socialQuery);

$socialLinks = [];

if ($socialResult) {
    while ($row = mysqli_fetch_assoc($socialResult)) {
        $socialLinks[$row['platform']] = $row['url'];
    }
}


/* =========================================================
   HELPER FOR PAGINATION URL
========================================================= */

function shopPageUrl($pageNumber)
{
    $params = [];

    if (!empty($_GET['search'])) {
        $params['search'] = $_GET['search'];
    }

    if (!empty($_GET['category'])) {
        $params['category'] = $_GET['category'];
    }

    $params['page'] = $pageNumber;

    return 'shop.php?' . http_build_query($params);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="images/favicon.png" type="image/x-icon" />

    <!-- Theme title -->
    <title>Mora Blog | Shop</title>

    <!-- Theme style CSS -->
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>


<body class="background-transparent">


<!-- =========================================================
     HEADER TOP AREA
========================================================= -->

<?php include "includes/header.php"; ?>




<!-- =========================================================
     HEADER / NAVBAR
========================================================= -->

<header class="main_header_area" id="header">

    <div class="container">

        <div class="header_menu">

            <?php include "includes/navbar.php"; ?>

        </div>

    </div>

</header>



<?php if (!empty($_SESSION['registered_user_id'])): ?>

    <div class="container">
        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:12px;
            margin:20px 0;
            padding:14px 18px;
            background:#f8f8f8;
            border:1px solid #e5e5e5;
            border-radius:6px;
        ">
            <span>
                Welcome,
                <strong>
                    <?= htmlspecialchars(
                        $_SESSION['registered_user_name'] ?? 'My Account',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>
            </span>

            <div>
                <a href="profile.php">My Profile</a>
                &nbsp; | &nbsp;
                <a href="profile/my-orders.php">My Orders</a>
            </div>
        </div>
    </div>

<?php endif; ?>
<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="container">

    <div class="pages_heder">

        <h2>Shop Page</h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="shop.php">Shop</a>
            </li>

            <li>
                <a href="shop.php" class="active">Shop</a>
            </li>

        </ol>

    </div>

</div>


<!-- =========================================================
     SHOP BANNER AREA
========================================================= -->

<section class="shop_banner">

    <div class="container">

        <div class="row">


            <!-- Main Slider -->

            <div
                class="carousel slide banner_slider col-12"
                data-ride="carousel"
            >

                <div class="carousel-inner">


                    <!-- Slide 1 -->

                    <div class="carousel-item active">

                        <img
                            src="images/shop/shop-slider.jpg"
                            alt="Shop Slider"
                        >

                        <a href="shop.php" class="shop_logo">

                            <img
                                src="images/shop/mora-shop-logo.png"
                                alt="Mora Shop"
                            >

                        </a>

                        <h3>
                            Buy 1 Get
                            <br>
                            1 Free
                        </h3>

                        <div class="slider_caption">

                            <h2>
                                <span>Summer</span>
                                Collection
                            </h2>

                            <a href="shop.php">
                                Shop Now
                            </a>

                        </div>

                        <h1>55% OFF</h1>

                    </div>


                    <!-- Slide 2 -->

                    <div class="carousel-item">

                        <img
                            src="images/shop/shop-slider.jpg"
                            alt="Shop Slider"
                        >

                        <a href="shop.php" class="shop_logo">

                            <img
                                src="images/shop/mora-shop-logo.png"
                                alt="Mora Shop"
                            >

                        </a>

                        <h3>
                            Buy 1 Get
                            <br>
                            1 Free
                        </h3>

                        <div class="slider_caption">

                            <h2>
                                <span>Summer</span>
                                Collection
                            </h2>

                            <a href="shop.php">
                                Shop Now
                            </a>

                        </div>

                        <h1>55% OFF</h1>

                    </div>


                    <!-- Slide 3 -->

                    <div class="carousel-item">

                        <img
                            src="images/shop/shop-slider.jpg"
                            alt="Shop Slider"
                        >

                        <a href="shop.php" class="shop_logo">

                            <img
                                src="images/shop/mora-shop-logo.png"
                                alt="Mora Shop"
                            >

                        </a>

                        <h3>
                            Buy 1 Get
                            <br>
                            1 Free
                        </h3>

                        <div class="slider_caption">

                            <h2>
                                <span>Summer</span>
                                Collection
                            </h2>

                            <a href="shop.php">
                                Shop Now
                            </a>

                        </div>

                        <h1>55% OFF</h1>

                    </div>


                </div>


                <ol class="carousel-indicators">

                    <li
                        data-target=".banner_slider"
                        data-slide-to="0"
                        class="active"
                    ></li>

                    <li
                        data-target=".banner_slider"
                        data-slide-to="1"
                    ></li>

                    <li
                        data-target=".banner_slider"
                        data-slide-to="2"
                    ></li>

                </ol>


            </div>


            <!-- Bottom Offer 1 -->

            <div class="col-lg-3 col-md-6">

                <div class="slider_offer">

                    <a href="shop.php">
                        Free Shipping
                    </a>

                    <img
                        src="images/shop/shop-slider-bottom-1.jpg"
                        alt="Free Shipping"
                    >

                </div>

            </div>


            <!-- Bottom Offer 2 -->

            <div class="col-lg-3 col-md-6 order-lg-last">

                <div class="slider_offer">

                    <a href="shop.php">
                        Free Shipping
                    </a>

                    <img
                        src="images/shop/shop-slider-bottom-2.jpg"
                        alt="Free Shipping"
                    >

                </div>

            </div>


            <!-- Bottom Offer 3 -->

            <div class="col-lg-6">

                <div class="slider_offer">

                    <a href="shop.php" class="middle_offer">

                        55% OFF

                        <small>
                            First order
                        </small>

                    </a>

                    <img
                        src="images/shop/shop-slider-bottom-3.jpg"
                        alt="55% OFF"
                    >

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     SHOP WITH SIDEBAR
========================================================= -->

<section class="shop_with_sidebar">

    <div class="container">

        <div class="row">


            <!-- =================================================
                 RIGHT SIDEBAR
            ================================================= -->

            <div class="col-lg-3 shop_right_sidebar">


                <!-- Product Search -->

                <form action="shop.php" method="GET">

                    <?php if ($selectedCategory !== '') { ?>

                        <input
                            type="hidden"
                            name="category"
                            value="<?php echo htmlspecialchars($selectedCategory); ?>"
                        >

                    <?php } ?>


                    <div class="input-group">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search Product"
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                        <div class="input-group-append">

                            <button
                                type="submit"
                                class="input-group-text theme_btn"
                                style="border:none;"
                            >

                                <i class="fa fa-search"></i>

                            </button>

                        </div>

                    </div>

                </form>


                <!-- =================================================
                     SHOP CATEGORIES
                ================================================= -->

                <div class="s_widget">

                    <h4>
                        Shop Categories :
                    </h4>


                    <ul class="categories">


                        <!-- All Products -->

                        <li>

                            <a href="shop.php">

                                All Products

                            </a>

                        </li>


                        <?php

                        if ($categoryResult) {

                            while ($category = mysqli_fetch_assoc($categoryResult)) {

                        ?>

                            <li>

                                <a
                                    href="shop.php?category=<?php echo urlencode($category['category']); ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category['category']
                                    );
                                    ?>

                                    (<?php echo $category['total']; ?>)

                                </a>

                            </li>

                        <?php

                            }

                        }

                        ?>

                    </ul>

                </div>


                <!-- =================================================
                     POPULAR PRODUCTS
                ================================================= -->

                <div class="s_widget popular_products">

                    <h4>
                        Popular Product
                    </h4>


                    <?php

                    if ($popularResult && mysqli_num_rows($popularResult) > 0) {

                        $popularFirst = true;

                        while ($popular = mysqli_fetch_assoc($popularResult)) {

                    ?>

                        <div
                            class="media <?php echo $popularFirst ? 'media_fast' : ''; ?>"
                        >


                           <a href="shop-details.php?id=<?= (int) $popular['id'] ?>">

                                <img
                                    src="<?php echo htmlspecialchars($popular['image']); ?>"
                                    alt="<?php echo htmlspecialchars($popular['name']); ?>"
                                >

                            </a>


                            <div class="media-body">

                               <a href="shop-details.php?id=<?= (int) $popular['id'] ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $popular['name']
                                    );
                                    ?>

                                </a>


                                <h6>

                                    $<?php
                                    echo number_format(
                                        $popular['price'],
                                        2
                                    );
                                    ?>

                                </h6>

                            </div>


                        </div>

                    <?php

                            $popularFirst = false;

                        }

                    } else {

                    ?>

                        <p>
                            No popular products found.
                        </p>

                    <?php

                    }

                    ?>

                </div>


            </div>


            <!-- =================================================
                 PRODUCTS AREA
            ================================================= -->

            <div class="col-lg-9 shop_left_sidebar">

                <div class="row">


                    <?php

                    if ($productResult && mysqli_num_rows($productResult) > 0) {

                        while ($product = mysqli_fetch_assoc($productResult)) {

                    ?>


                        <!-- Shop Item -->

                        <div class="col-md-4 col-sm-6">

                            <div class="shop_items">


                                <!-- Product Image -->

                                <a
                                    href="shop-details.php?id=<?php echo $product['id']; ?>"
                                    class="shop_img"
                                >

                                    <img
                                        src="<?php echo htmlspecialchars($product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    >

                                </a>


                                <!-- Product Information -->

                                <div class="shop_text">


                                    <a
                                        href="shop-details.php?id=<?php echo $product['id']; ?>"
                                        class="s_heding"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $product['name']
                                        );
                                        ?>

                                    </a>


                                    <h4>

                                        $<?php
                                        echo number_format(
                                            $product['price'],
                                            2
                                        );
                                        ?>

                                    </h4>


                                    
<?php if (!empty($_SESSION['registered_user_id'])): ?>

    <?php
    if (empty($_SESSION['cart_csrf_token'])) {
        $_SESSION['cart_csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>

    <form action="cart.php" method="POST">

        <input type="hidden" name="action" value="add">

        <input
            type="hidden"
            name="product_id"
            value="<?= (int) $product['id'] ?>"
        >

        <input type="hidden" name="quantity" value="1">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['cart_csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <button type="submit" class="theme_btn">
            Add to Cart
        </button>

    </form>

<?php else: ?>

    <a href="login.php" class="theme_btn">
        Login to Add to Cart
    </a>

<?php endif; ?>

                                </div>


                            </div>

                        </div>


                    <?php

                        }

                    } else {

                    ?>


                        <!-- No Product -->

                        <div class="col-12">

                            <div style="padding:40px 0; text-align:center;">

                                <h4>
                                    No products found.
                                </h4>

                                <?php if ($search !== '') { ?>

                                    <p>
                                        No products matched your search:
                                        <strong>
                                            <?php
                                            echo htmlspecialchars($search);
                                            ?>
                                        </strong>
                                    </p>

                                <?php } ?>

                                <?php if ($selectedCategory !== '') { ?>

                                    <p>
                                        No products available in
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $selectedCategory
                                            );
                                            ?>
                                        </strong>
                                        category.
                                    </p>

                                <?php } ?>

                                <a
                                    href="shop.php"
                                    class="theme_btn"
                                >
                                    View All Products
                                </a>

                            </div>

                        </div>


                    <?php

                    }

                    ?>


                    <!-- =================================================
                         PAGINATION
                    ================================================= -->

                    <?php if ($totalPages > 1) { ?>


                        <nav class="pagination_area col-12 p-0">

                            <ul class="pagination">


                                <!-- Previous -->

                                <?php if ($page > 1) { ?>

                                    <li class="page-item">

                                        <a
                                            href="<?php echo shopPageUrl($page - 1); ?>"
                                        >

                                            <i class="fa fa-arrow-left"></i>

                                        </a>

                                    </li>

                                <?php } ?>


                                <!-- Page Numbers -->

                                <?php

                                for (
                                    $i = 1;
                                    $i <= $totalPages;
                                    $i++
                                ) {

                                ?>

                                    <li
                                        class="page-item <?php echo ($i == $page) ? 'disabled' : ''; ?>"
                                    >

                                        <a
                                            class="<?php echo ($i == $page) ? 'active' : ''; ?>"
                                            href="<?php echo shopPageUrl($i); ?>"
                                        >

                                            <?php echo $i; ?>

                                        </a>

                                    </li>

                                <?php

                                }

                                ?>


                                <!-- Next -->

                                <?php if ($page < $totalPages) { ?>

                                    <li class="page-item">

                                        <a
                                            href="<?php echo shopPageUrl($page + 1); ?>"
                                        >

                                            <i class="fa fa-arrow-right"></i>

                                        </a>

                                    </li>

                                <?php } ?>


                            </ul>

                        </nav>


                    <?php } ?>


                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     CONNECT WITH US
========================================================= -->

<section class="connect_with_us">

    <div class="container">

        <h2>
            Connect with us
        </h2>


        <ul class="contact_with_socail">


            <?php if (!empty($socialLinks['google'])) { ?>

                <li>

                    <a
                        href="<?php echo htmlspecialchars($socialLinks['google']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa fa-google-plus"></i>

                    </a>

                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['linkedin'])) { ?>

                <li>

                    <a
                        href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa fa-linkedin"></i>

                    </a>

                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['instagram'])) { ?>

                <li>

                    <a
                        href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa fa-instagram"></i>

                    </a>

                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['twitter'])) { ?>

                <li>

                    <a
                        href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa fa-twitter"></i>

                    </a>

                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['youtube'])) { ?>

                <li>

                    <a
                        href="<?php echo htmlspecialchars($socialLinks['youtube']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa fa-youtube"></i>

                    </a>

                </li>

            <?php } ?>


        </ul>

    </div>

</section>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php include "includes/footer.php"; ?>


<!-- =========================================================
     SCROLL TOP
========================================================= -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- =========================================================
     PRELOADER
========================================================= -->

<div class="preloader"></div>


<!-- =========================================================
     SCRIPTS
========================================================= -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="js/theme.js"></script>


</body>

</html>