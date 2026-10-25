<?php
require_once __DIR__ . '/db.php';

$navbarLogo = 'images/logo.png';

$logoQuery = $conn->query(
    "SELECT logo_path FROM site_settings WHERE id = 1 LIMIT 1"
);

if ($logoQuery && $logoRow = $logoQuery->fetch_assoc()) {
    if (!empty($logoRow['logo_path'])) {
        $navbarLogo = $logoRow['logo_path'];
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isUserLoggedIn = !empty($_SESSION['registered_user_id']);
$navUserName = $_SESSION['registered_user_name'] ?? 'Account';

$isProfileFolder = (basename(dirname($_SERVER['PHP_SELF'] ?? '')) === 'profile');
$navRootBase = $isProfileFolder ? '../' : '';
$navProfileBase = $isProfileFolder ? '' : 'profile/';

$currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$isHomeActive = in_array($currentScript, ['index.php', 'index-2.php']);
$isBlogActive = in_array($currentScript, ['news.php', 'news-2.php', 'news-3.php', 'news-details.php', 'news-details-2.php', 'news-details-3.php', 'video-details.php']);
$isShopActive = in_array($currentScript, ['shop.php', 'shop-details.php', 'cart.php', 'checkout.php']);
$isPagesActive = in_array($currentScript, ['faq.html', 'error.php']);
$isUserActive = in_array($currentScript, ['profile.php', 'my-orders.php', 'my-posts.php', 'edit-post.php', 'create-post.php', 'order-details.php']);
$isContactActive = ($currentScript === 'contact.php');
$isAboutActive = in_array($currentScript, ['about.php', 'about.html']);
$currentSearch = htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<nav class="navbar navbar-expand-lg">

    <a class="navbar-brand" href="<?= $navRootBase ?>index.php">
        <img src="<?= htmlspecialchars($navRootBase . $navbarLogo, ENT_QUOTES, 'UTF-8') ?>" alt="Mora Blog">
    </a>

    <!-- Small Device Menu-->
    <button
        class="navbar-toggler"
        type="button"
        data-toggle="collapse"
        data-target=".navbar_supported"
        aria-expanded="false"
        aria-label="Toggle navigation"
    >
        <i class="fa fa-bars" aria-hidden="true"></i>
    </button>

    <div class="collapse navbar-collapse navbar_supported">

        <ul class="navbar-nav">

            <!-- Home -->
            <li class="dropdown">

                <a
                    class="nav-link dropdown-toggle <?= $isHomeActive ? 'active' : '' ?>"
                    href="<?= $navRootBase ?>index.php"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false"
                >
                    Home
                </a>

                <ul class="dropdown-menu">

                    <li>
                        <a href="<?= $navRootBase ?>index.php" class="<?= $currentScript === 'index.php' ? 'active' : '' ?>">
                            Home Version 1
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>index-2.php" class="<?= $currentScript === 'index-2.php' ? 'active' : '' ?>">
                            Home Version 2
                        </a>
                    </li>

                </ul>

            </li>


            <!-- About -->
            <li>
                <a class="nav-link <?= $isAboutActive ? 'active' : '' ?>" href="<?= $navRootBase ?>about.php">
                    About us
                </a>
            </li>


            <!-- Blog -->
            <li class="dropdown">

                <a
                    class="nav-link dropdown-toggle <?= $isBlogActive ? 'active' : '' ?>"
                    href="<?= $navRootBase ?>news.php"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false"
                >
                    Blog
                </a>

                <ul class="dropdown-menu">

                    <li>
                        <a href="<?= $navRootBase ?>news.php" class="<?= $currentScript === 'news.php' ? 'active' : '' ?>">
                            All News
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>news-2.php" class="<?= $currentScript === 'news-2.php' ? 'active' : '' ?>">
                            News Grid
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>news-3.php" class="<?= $currentScript === 'news-3.php' ? 'active' : '' ?>">
                            News List
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>news-details.php" class="<?= in_array($currentScript, ['news-details.php', 'news-details-2.php', 'news-details-3.php']) ? 'active' : '' ?>">
                            News Details
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>video-details.php" class="<?= $currentScript === 'video-details.php' ? 'active' : '' ?>">
                            Video Gallery
                        </a>
                    </li>

                </ul>

            </li>


            <!-- Shop -->
            <li class="dropdown">

                <a
                    class="nav-link dropdown-toggle <?= $isShopActive ? 'active' : '' ?>"
                    href="<?= $navRootBase ?>shop.php"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false"
                >
                    Shop
                </a>

                <ul class="dropdown-menu">

                    <li>
                        <a href="<?= $navRootBase ?>shop.php" class="<?= $currentScript === 'shop.php' ? 'active' : '' ?>">
                            Shop Products
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>cart.php" class="<?= $currentScript === 'cart.php' ? 'active' : '' ?>">
                            Shopping Cart
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>checkout.php" class="<?= $currentScript === 'checkout.php' ? 'active' : '' ?>">
                            Checkout
                        </a>
                    </li>

                </ul>

            </li>


            <!-- Pages -->
            <li class="dropdown">

                <a
                    class="nav-link dropdown-toggle <?= $isPagesActive ? 'active' : '' ?>"
                    href="#"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false"
                >
                    Pages
                </a>

                <ul class="dropdown-menu">

                    <?php if ($isUserLoggedIn): ?>
                        <li>
                            <a href="<?= $navRootBase ?>profile.php" class="<?= $currentScript === 'profile.php' ? 'active' : '' ?>">
                                User Profile
                            </a>
                        </li>

                        <li>
                            <a href="<?= $navProfileBase ?>my-orders.php" class="<?= $currentScript === 'my-orders.php' ? 'active' : '' ?>">
                                My Orders
                            </a>
                        </li>

                        <li>
                            <a href="<?= $navProfileBase ?>my-posts.php" class="<?= $currentScript === 'my-posts.php' ? 'active' : '' ?>">
                                My Posts
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= $navRootBase ?>login.php" class="<?= $currentScript === 'login.php' ? 'active' : '' ?>">
                                Login
                            </a>
                        </li>

                        <li>
                            <a href="<?= $navRootBase ?>register.php" class="<?= $currentScript === 'register.php' ? 'active' : '' ?>">
                                Register
                            </a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <a href="<?= $navRootBase ?>faq.html" class="<?= $currentScript === 'faq.html' ? 'active' : '' ?>">
                            FAQ
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>contact.php" class="<?= $currentScript === 'contact.php' ? 'active' : '' ?>">
                            Contact Us
                        </a>
                    </li>

                    <li>
                        <a href="<?= $navRootBase ?>error.php" class="<?= $currentScript === 'error.php' ? 'active' : '' ?>">
                            Error (404)
                        </a>
                    </li>

                    <?php if ($isUserLoggedIn): ?>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a href="<?= $navProfileBase ?>logout.php" class="text-danger">
                                Logout
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>

            </li>


            <!-- Contact -->
            <li>
                <a class="nav-link <?= $isContactActive ? 'active' : '' ?>" href="<?= $navRootBase ?>contact.php">
                    Contact us
                </a>
            </li>


            <!-- User Account Dropdown in Main Navbar -->
            <?php if ($isUserLoggedIn): ?>
                <li class="dropdown main_nav_user_dropdown">
                    <a
                        class="nav-link dropdown-toggle <?= $isUserActive ? 'active' : '' ?>"
                        href="<?= $navRootBase ?>profile.php"
                        role="button"
                        data-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="fa fa-user-circle"></i> <?= htmlspecialchars($navUserName, ENT_QUOTES, 'UTF-8') ?>
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?= $navRootBase ?>profile.php" class="<?= $currentScript === 'profile.php' ? 'active' : '' ?>">
                                <i class="fa fa-id-card mr-2 text-primary"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a href="<?= $navProfileBase ?>my-posts.php" class="<?= $currentScript === 'my-posts.php' ? 'active' : '' ?>">
                                <i class="fa fa-pencil-square-o mr-2 text-info"></i> My Posts
                            </a>
                        </li>
                        <li>
                            <a href="<?= $navProfileBase ?>my-orders.php" class="<?= $currentScript === 'my-orders.php' ? 'active' : '' ?>">
                                <i class="fa fa-shopping-bag mr-2 text-warning"></i> My Orders
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a href="<?= $navProfileBase ?>logout.php" class="text-danger">
                                <i class="fa fa-sign-out mr-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="dropdown main_nav_user_dropdown">
                    <a
                        class="nav-link dropdown-toggle <?= in_array($currentScript, ['login.php', 'register.php']) ? 'active' : '' ?>"
                        href="<?= $navRootBase ?>login.php"
                        role="button"
                        data-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="fa fa-user-circle-o"></i> Account
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?= $navRootBase ?>login.php" class="<?= $currentScript === 'login.php' ? 'active' : '' ?>">
                                <i class="fa fa-sign-in mr-2"></i> Login
                            </a>
                        </li>
                        <li>
                            <a href="<?= $navRootBase ?>register.php" class="<?= $currentScript === 'register.php' ? 'active' : '' ?>">
                                <i class="fa fa-user-plus mr-2"></i> Register
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>


            <!-- Search -->
            <li class="search_box">

                <form action="news.php" method="GET" class="header_search_form">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search news..."
                        value="<?= $currentSearch ?>"
                        required
                    >

                    <button type="submit" aria-label="Search">
                        <i class="fa fa-search"></i>
                    </button>

                </form>

            </li>

        </ul>

    </div>

</nav>