
<?php
require_once __DIR__ . '/includes/maintenance-check.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $message = "Please fill all required fields.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } elseif ($password !== $confirm_password) {
        $message = "Password and Confirm Password do not match.";
        $messageType = "danger";

    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "danger";

    } else {

        $checkSql = "SELECT id FROM registered_users WHERE email = ? LIMIT 1";
        $checkStmt = mysqli_prepare($conn, $checkSql);

        if (!$checkStmt) {
            $message = "Database error (check): " . mysqli_error($conn);
            $messageType = "danger";
        } else {
            mysqli_stmt_bind_param($checkStmt, "s", $email);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            $emailExists = mysqli_stmt_num_rows($checkStmt) > 0;
            mysqli_stmt_close($checkStmt);

            if ($emailExists) {
                $message = "This email is already registered. If this is your account, please <a href='login.php' class='alert-link'>log in</a> or <a href='forgot-password.php' class='alert-link'>reset your password</a>.";
                $messageType = "danger";
            } else {

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);

                $insertSql = "
                    INSERT INTO registered_users
                    (
                        name,
                        email,
                        password,
                        is_verified,
                        verification_token_hash,
                        verification_expires_at
                    )
                    VALUES (?, ?, ?, 0, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))
                ";

                $insertStmt = mysqli_prepare($conn, $insertSql);

                if (!$insertStmt) {
                    $message = "Database error (insert): " . mysqli_error($conn);
                    $messageType = "danger";
                } else {
                    mysqli_stmt_bind_param(
                        $insertStmt,
                        "ssss",
                        $name,
                        $email,
                        $hashedPassword,
                        $tokenHash
                    );

                    if (mysqli_stmt_execute($insertStmt)) {

                        $userId = mysqli_insert_id($conn);
                        mysqli_stmt_close($insertStmt);

                        // Dynamic site base URL handles both localhost and published domains (e.g. InfinityFree)
                        $verificationUrl = getMoraSiteBaseUrl()
                            . "/verify-email.php?token="
                            . urlencode($token);

                        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                        $safeUrl  = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');

                        $emailBody = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>Confirm Your Mora Blog Account</title>
                        </head>
                        <body style='margin:0; padding:0; background-color:#f4f6f9; font-family:-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                            <table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0' style='background-color:#f4f6f9; padding: 30px 10px;'>
                                <tr>
                                    <td align='center'>
                                        <table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0' style='max-width:580px; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.06);'>
                                            <!-- Header -->
                                            <tr>
                                                <td style='background:#1a1d20; padding:25px 30px; text-align:center;'>
                                                    <h1 style='margin:0; color:#ffffff; font-size:24px; font-weight:700; letter-spacing:1px;'>MORA BLOG</h1>
                                                </td>
                                            </tr>
                                            <!-- Content -->
                                            <tr>
                                                <td style='padding:35px 30px; color:#333333; line-height:1.6;'>
                                                    <h2 style='margin-top:0; color:#1a1d20; font-size:20px;'>Welcome to Mora Blog!</h2>
                                                    <p style='font-size:15px; margin-bottom:20px;'>Hello <strong>{$safeName}</strong>,</p>
                                                    <p style='font-size:15px; margin-bottom:25px;'>Thank you for creating an account with us. Please confirm your email address by clicking the button below:</p>
                                                    <div style='text-align:center; margin:30px 0;'>
                                                        <a href='{$safeUrl}' style='background-color:#0d6efd; color:#ffffff; padding:12px 28px; text-decoration:none; font-size:15px; font-weight:600; border-radius:6px; display:inline-block;'>Verify My Email Address</a>
                                                    </div>
                                                    <p style='font-size:13px; color:#6c757d; margin-top:25px;'>If the button above does not work, copy and paste this link into your browser:</p>
                                                    <p style='font-size:12px; color:#0d6efd; word-break:break-all; background:#f8f9fa; padding:10px; border-radius:4px;'>{$safeUrl}</p>
                                                    <p style='font-size:13px; color:#6c757d; margin-top:20px;'>This verification link will expire in <strong>30 minutes</strong>.</p>
                                                    <p style='font-size:13px; color:#999999; margin-top:20px; border-top:1px solid #eeeeee; padding-top:15px;'>If you did not register for an account on Mora Blog, you can safely ignore this email.</p>
                                                </td>
                                            </tr>
                                            <!-- Footer -->
                                            <tr>
                                                <td style='background:#f8f9fa; padding:18px 30px; text-align:center; font-size:12px; color:#888888; border-top:1px solid #ebebeb;'>
                                                    &copy; " . date('Y') . " Mora Blog. All rights reserved.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>
                        ";

                        $mailDebug = '';
                        if (sendMoraEmail($email, "Confirm Your Mora Blog Account", $emailBody, true, $mailDebug)) {
                            $message = "Registration successful! Please check your email and click the verification link. It expires in 30 minutes.";
                            $messageType = "success";
                        } else {
                            // Do not leave an unusable, unverified account behind.
                            $deleteSql = "
                                DELETE FROM registered_users
                                WHERE id = ? AND is_verified = 0
                            ";
                            $deleteStmt = mysqli_prepare($conn, $deleteSql);

                            if ($deleteStmt) {
                                mysqli_stmt_bind_param($deleteStmt, "i", $userId);
                                mysqli_stmt_execute($deleteStmt);
                                mysqli_stmt_close($deleteStmt);
                            }

                            $mailErr = getMoraMailerLastError();
                            $message = "We could not send the verification email. " . ($mailErr ? "Mailer error: " . htmlspecialchars($mailErr, ENT_QUOTES, 'UTF-8') : "Please check your email settings and try again.");
                            if (!empty($mailDebug)) {
                                $message .= "<br><details style='margin-top:10px;'><summary style='cursor:pointer; color:#0d6efd;'>View Technical Connection Details</summary><pre style='font-size:11px; max-height:180px; overflow:auto; text-align:left; background:#1e1e1e; color:#bbb; padding:10px; border-radius:4px; margin-top:5px; white-space:pre-wrap;'>" . htmlspecialchars($mailDebug, ENT_QUOTES, 'UTF-8') . "</pre></details>";
                            }
                            $messageType = "danger";
                        }

                    } else {
                        mysqli_stmt_close($insertStmt);
                        $message = "Registration failed. Please try again.";
                        $messageType = "danger";
                    }
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="images/favicon.png" type="image/x-icon" />

    <title>Mora Blog | Register</title>

    <link href="css/style.css" rel="stylesheet">
    <link href="css/modern.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>


<!-- Header Top Area -->

<?php include "includes/header.php"; ?>

<header class="main_header_area" id="header">
    <div class="container">
        <div class="header_menu">
            <?php include "includes/navbar.php"; ?>
        </div>
    </div>
</header>


<!-- Pages Header Area -->

<div class="container">

    <div class="pages_heder">

        <h2>Register Page</h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="">Pages</a>
            </li>

            <li>
                <a href="register.php" class="active">
                    Register
                </a>
            </li>

        </ol>

    </div>

</div>


<!-- Register Area -->

<section class="login_area">

    <div class="container">

        <div class="login_inner">

            <form
                class="row login_from"
                action="register.php"
                method="POST"
            >


                <?php if ($message !== '') { ?>

                    <div class="col-12">

                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

                            <?php echo $message; ?>

                        </div>

                    </div>

                <?php } ?>


                <!-- Name -->

                <div class="form-group col-12">

                    <input
                        type="text"
                        class="form-control"
                        name="name"
                        placeholder="Your Name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Email -->

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


                <!-- Password -->

                <div class="form-group col-12">

                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        placeholder="Password"
                        required
                    >

                </div>


                <!-- Confirm Password -->

                <div class="form-group col-12">

                    <input
                        type="password"
                        class="form-control"
                        name="confirm_password"
                        placeholder="Confirm Password"
                        required
                    >

                </div>


                <!-- Register Button -->

                <div class="form-group larg_btn col-12">

                    <button
                        class="defult_btn"
                        type="submit"
                    >
                        Register
                    </button>

                </div>


                <div class="form-group col-sm-6">

                    <a href="#" class="reset_btn">
                        Reset Password
                    </a>

                </div>


                <div class="form-group col-sm-6">

                    <a href="login.php" class="reset_btn">
                        Login
                    </a>

                </div>


                <h4 class="col-12">
                    or login in with
                </h4>


                <div class="form-group col-sm-6">

                    <a href="#" class="google_btn">

                        <img src="images/google.png" alt="">

                        GOOGLE

                    </a>

                </div>


                <div class="form-group col-sm-6">

                    <a href="#" class="google_btn">

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
                            for="check1"
                        >
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


<!-- Connect With Us -->

<section class="connect_with_us">

    <div class="container">

        <h2>Connect with us</h2>

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


<!-- Footer Area -->

<footer class="footer_area">

    <div class="container">

        <div class="footer_inner row">

            <div class="col-lg-5 col-md-6 footer_logo">

                <a href="index.php">
                    <img src="images/logo.png" alt="">
                </a>

                <p>
                    Established in 2013 as the first cloud mining provider,
                    Blog has become a multi-functional Blog Categories,
                    trusted by over a million users.
                </p>

                <address>

                    <span>LOCATION</span>

                    <p>
                        12/4 New Yourk R Block Street 2101 USA
                    </p>

                </address>

            </div>


            <div class="col-lg-5 col-md-6">

                <div class="subscribe">

                    <h4>Subscribe</h4>

                    <p>
                        Sign up for our mailing list to get latest updates and offers
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

                <h4>Short Link</h4>

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
            <a href="#">Mora</a>
            Developed by
            <a href="http://themazin.com">
                Themazin
            </a>
        </p>

    </div>

</footer>


<!-- Scroll Top Button -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- Preloader -->

<div class="preloader"></div>


<!-- jQuery -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<!-- Bootstrap -->

<script src="js/bootstrap.min.js"></script>

<!-- Extra Plugin -->

<script src="vendors/animate-css/wow.min.js"></script>

<!-- Theme JS -->

<script src="js/theme.js"></script>

</body>

</html>