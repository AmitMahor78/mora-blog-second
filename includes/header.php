<?php

include __DIR__ . "/db.php";


$siteName = 'Mora';
$logoPath = 'images/logo.png';

// =====================================================
// 1. FETCH SITE SETTINGS
// =====================================================

$settingsQuery = "
    SELECT site_name, logo_path 
    FROM site_settings
    WHERE id = 1
    LIMIT 1
";

$settingsResult = mysqli_query($conn, $settingsQuery);

$siteName = "Mora";

if ($settingsResult && mysqli_num_rows($settingsResult) > 0) {

    $settings = mysqli_fetch_assoc($settingsResult);

    if (!empty($settings['site_name'])) {
    $siteName = $settings['site_name'];
}

if (!empty($settings['logo_path'])) {
    $logoPath = $settings['logo_path'];
}
}


// =====================================================
// 2. FETCH SOCIAL LINKS
// =====================================================

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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isUserLoggedIn = !empty($_SESSION['registered_user_id']);
$headerUserName = $_SESSION['registered_user_name'] ?? 'Account';

$isProfileFolder = (basename(dirname($_SERVER['PHP_SELF'] ?? '')) === 'profile');
$topRootBase = $isProfileFolder ? '../' : '';
$topProfileBase = $isProfileFolder ? '' : 'profile/';

$cartItemCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartItemCount += (int) $qty;
    }
}
?>

<!-- Modern CSS Enhancement Layer -->
<link href="<?= $topRootBase ?>css/modern.css" rel="stylesheet">

<!-- =====================================================
     HEADER TOP AREA
===================================================== -->

<div class="header_top_area">

    <div class="container">

        <div class="header_top_inner d-sm-flex justify-content-sm-between align-items-center">


            <!-- =================================================
                 LEFT MENU
            ================================================== -->

            <ul class="left_info">

                <li>
                    <a href="<?= $topRootBase ?>index.php">
                        Home
                    </a>
                </li>

                <li>
                    <a href="<?= $topRootBase ?>contact.php">
                        Contact Us
                    </a>
                </li>

                <li>
                    <a href="<?= $topRootBase ?>about.php">
                        About Us
                    </a>
                </li>

                <li>
                    <a href="<?= $topRootBase ?>shop.php">
                        Shop
                    </a>
                </li>

                <?php if ($isUserLoggedIn) { ?>

                    <li class="dropdown top_user_dropdown">
                        <a href="<?= $topRootBase ?>profile.php" class="header_user_btn dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user-circle"></i> <span><?php echo htmlspecialchars($headerUserName); ?></span> <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= $topRootBase ?>profile.php"><i class="fa fa-id-card text-primary"></i> My Profile</a></li>
                            <li><a href="<?= $topProfileBase ?>my-posts.php"><i class="fa fa-pencil-square-o text-info"></i> My Posts</a></li>
                            <li><a href="<?= $topProfileBase ?>my-orders.php"><i class="fa fa-shopping-bag text-warning"></i> My Orders</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="<?= $topProfileBase ?>logout.php" class="text-danger"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>

                <?php } else { ?>

                    <li>
                        <a href="<?= $topRootBase ?>login.php">
                            <i class="fa fa-sign-in"></i> Login
                        </a>
                    </li>

                    <li>
                        <a href="<?= $topRootBase ?>register.php">
                            <i class="fa fa-user-plus"></i> Register
                        </a>
                    </li>

                <?php } ?>

            </ul>


            <!-- =================================================
                 RIGHT SOCIAL MENU
            ================================================== -->

            <ul class="header_social">


                <!-- Facebook -->

                <?php if (!empty($socialLinks['facebook'])) { ?>

                    <li>

                        <a
                            href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            <i class="fa fa-facebook"></i>

                        </a>

                    </li>

                <?php } ?>


                <!-- Twitter -->

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


                <!-- LinkedIn -->

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


                <!-- Pinterest -->

                <?php if (!empty($socialLinks['pinterest'])) { ?>

                    <li>

                        <a
                            href="<?php echo htmlspecialchars($socialLinks['pinterest']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            <i class="fa fa-pinterest"></i>

                        </a>

                    </li>

                <?php } ?>


                <!-- Google -->

                <?php if (!empty($socialLinks['google'])) { ?>

                    <li>

                        <a
                            href="<?php echo htmlspecialchars($socialLinks['google']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            <i class="fa fa-google"></i>

                        </a>

                    </li>

                <?php } ?>


                <!-- Instagram -->

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


                <!-- Shopping Bag -->

                <li>

                    <a
                        href="cart.php"
                        class="shop_btn"
                        title="View Shopping Cart"
                    >

                        <i class="flaticon-shopping-bag"></i>

                        <span><?php echo (int) $cartItemCount; ?></span>

                    </a>

                </li>


                <!-- Get Free Quote -->

                <li>

                    <a
                        href="contact.php"
                        class="quote_btn"
                    >

                        Contact Us

                        <i class="fa fa-arrow-right"></i>

                    </a>

                </li>


            </ul>

        </div>

    </div>

</div>