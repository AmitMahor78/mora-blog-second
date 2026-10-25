
<?php
session_start();

require_once __DIR__ . '/includes/maintenance-check.php';
require_once __DIR__ . '/includes/db.php';

$message = "";
$messageType = "";

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $message = "Registration successful. Please verify your email before logging in.";
    $messageType = "success";
}

if (isset($_GET['verified']) && $_GET['verified'] === 'success') {
    $message = "Email verified successfully! You can now log in.";
    $messageType = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $message = "Please enter your email and password.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        $sql = "
            SELECT id, name, email, password, status, is_verified
            FROM registered_users
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $message = "Database error. Please try again.";
            $messageType = "error";

        } else {

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {

                mysqli_stmt_bind_result(
                    $stmt,
                    $userId,
                    $userName,
                    $userEmail,
                    $hashedPassword,
                    $userStatus,
                    $isVerified
                );

                mysqli_stmt_fetch($stmt);

                if (!password_verify($password, $hashedPassword)) {

                    $message = "Invalid email or password.";
                    $messageType = "error";

                } elseif ($userStatus !== 'active') {

                    $message = "Your account is inactive. Please contact administrator.";
                    $messageType = "error";

                } elseif ((int)$isVerified !== 1) {

                    $message = "Please verify your email address before logging in. Check your inbox for the verification link.";
                    $messageType = "error";

                } else {

                    session_regenerate_id(true);

                    $_SESSION['registered_user_id'] = $userId;
                    $_SESSION['registered_user_name'] = $userName;
                    $_SESSION['registered_user_email'] = $userEmail;

                    mysqli_stmt_close($stmt);

                    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                    unset($_SESSION['redirect_after_login']);

                    // Safe redirect check (prevent external open redirects)
                    if (!is_string($redirect) || strpos($redirect, '//') === 0 || preg_match('/^https?:\/\//i', $redirect)) {
                        $redirect = 'index.php';
                    }

                    header("Location: " . $redirect);
                    exit;
                }

            } else {

                $message = "Invalid email or password.";
                $messageType = "error";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $message = "Password reset successfully. Please log in.";
    $messageType = "success";
}
?>


<!Doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="icon"
          href="images/favicon.png"
          type="image/x-icon" />


    <!-- Theme title -->

    <title>Mora Blog | Login</title>


    <!-- Theme style CSS -->

    <link href="css/style.css" rel="stylesheet">
    <link href="css/modern.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support -->

    <!--[if lt IE 9]>

    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>

    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    <![endif]-->

</head>


<body>


<!-- =========================================================
     Header Top Area
========================================================= -->

<?php include "includes/header.php"; ?>

<header class="main_header_area" id="header">
    <div class="container">
        <div class="header_menu">
            <?php include "includes/navbar.php"; ?>
        </div>
    </div>
</header>


<!-- =========================================================
     Pages Header Area
========================================================= -->

<div class="container">

    <div class="pages_heder">

        <h2>
            Login Page
        </h2>


        <ol class="breadcrumb">

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>

            <li>
                <a href="">
                    Pages
                </a>
            </li>

            <li>
                <a href="login.php"
                   class="active">

                    Login Page

                </a>
            </li>

        </ol>

    </div>

</div>


<!-- =========================================================
     Login Area
========================================================= -->

<section class="login_area">

    <div class="container">

        <div class="login_inner">


            <!-- Login Message -->

            <?php if ($message !== '') { ?>

                <div style="
                    width: 100%;
                    padding: 12px 15px;
                    margin-bottom: 20px;
                    text-align: center;
                    border-radius: 3px;
                    <?php
                    echo ($messageType === 'success')
                        ? 'background:#d4edda;color:#155724;'
                        : 'background:#f8d7da;color:#721c24;';
                    ?>
                ">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php } ?>


            <!-- Login Form -->

            <form class="row login_from"
                  action="login.php"
                  method="POST">


                <div class="form-group col-12">

                    <input
                        type="email"
                        class="form-control"
                        name="email"
                        placeholder="Email Address"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                    >

                </div>


                <div class="form-group col-12">

                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        placeholder="Password"
                        required
                    >

                </div>


               


                <div class="form-group larg_btn col-12">

                    <button
                        class="defult_btn"
                        type="submit">

                        Login

                    </button>

                </div>


                <div class="form-group col-sm-6">

                   <a href="forgot-password.php" class="reset_btn">
    Forgot Password?
</a>

                </div>


                <div class="form-group col-sm-6">

                    <a href="register.php"
                       class="reset_btn">

                        Sign Up

                    </a>

                </div>


                <h4 class="col-12">
                    or login in with
                </h4>


                <div class="form-group col-sm-6">

                    <a href="#"
                       class="google_btn">

                        <img src="images/google.png"
                             alt="">

                        GOOGLE

                    </a>

                </div>


                <div class="form-group col-sm-6">

                    <a href="#"
                       class="google_btn">

                        <i class="fa fa-facebook"></i>

                        Sign Up

                    </a>

                </div>


                <div class="col-12">

                    <div class="custom-control custom-checkbox">

                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="check1"
                            required
                        >


                        <label
                            class="custom-control-label"
                            for="check1">

                            I agree to the mora.com

                            <a href="#">
                                Terms of Service
                            </a>

                        </label>

                    </div>

                </div>


            </form>

        </div>

    </div>

</section>


<!-- =========================================================
     Connect With Us
========================================================= -->

<section class="connect_with_us">

    <div class="container">

        <h2>
            Connect with us
        </h2>


        <ul class="contact_with_socail">

            <li>
                <a href="#">
                    <i class="fa fa-google-plus"></i>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-linkedin"></i>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-instagram"></i>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-twitter"></i>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-youtube"></i>
                </a>
            </li>

        </ul>

    </div>

</section>


<!-- =========================================================
     Footer Area
========================================================= -->

<footer class="footer_area">

    <div class="container">

        <div class="footer_inner row">


            <div class="col-lg-5 col-md-6 footer_logo">

                <a href="index.php">

                    <img src="images/logo.png"
                         alt="">

                </a>


                <p>

                    Established in 2013 as the first cloud mining
                    provider, Blog has become a multi-functional
                    Blog Categories, trusted by over a million users.

                </p>


                <address>

                    <span>
                        LOCATION
                    </span>

                    <p>
                        12/4 New Yourk R Block Street 2101 USA
                    </p>

                </address>

            </div>



            <div class="col-lg-5 col-md-6">

                <div class="subscribe">

                    <h4>
                        Subscribe
                    </h4>


                    <p>
                        Sign up for our mailing list to get latest
                        updates and offers
                    </p>


                    <div class="input-group">

                        <input
                            type="text"
                            class="form-control"
                            placeholder=""
                        >


                        <div class="input-group-append">

                            <span class="input-group-text">

                                <i class="fa fa-paper-plane"></i>

                            </span>

                        </div>

                    </div>


                    <h6>

                        Working Hours :
                        Monday-Saturday

                        <span>
                            Close : Sunday
                        </span>

                    </h6>

                </div>

            </div>



            <div class="col-lg-2 col-md-3 resources">

                <h4>
                    Short Link
                </h4>


                <ul class="resources_list">

                    <li>
                        <a href="about.php">
                            About
                        </a>
                    </li>

                    <li>
                        <a href="contact.php">
                            Contact
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Bug Bounty Program
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Policy
                        </a>
                    </li>

                    <li>
                        <a href="faq.html">
                            FAQ
                        </a>
                    </li>

                    <li>
                        <a href="shop.php">
                            Shop
                        </a>
                    </li>

                </ul>

            </div>


        </div>

    </div>


    <div class="copy_right">

        <p>

            Copyright © 2018

            <a href="#">
                Mora
            </a>

            Developed by

            <a href="http://themazin.com">
                Themazin
            </a>

        </p>

    </div>

</footer>


<!-- =========================================================
     Scroll Top Button
========================================================= -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- =========================================================
     Preloader
========================================================= -->

<div class="preloader"></div>


<!-- =========================================================
     JavaScript
========================================================= -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="js/theme.js"></script>


</body>

</html>