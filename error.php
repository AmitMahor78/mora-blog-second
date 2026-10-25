<?php
require_once __DIR__ . '/includes/maintenance-check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <link rel="icon"
          href="images/favicon.png"
          type="image/x-icon" />

    <!-- Theme title -->

    <title>Mora Blog | Page Not Found</title>

    <!-- Theme style CSS -->

    <link href="css/style.css"
          rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->

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
            Error (404)
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

                <a href="error.php"
                   class="active">

                    Error (404)

                </a>

            </li>

        </ol>

    </div>

</div>


<!-- =========================================================
     Error Page
========================================================= -->

<section class="error_area">

    <div class="container">

        <h1>
            Oops !
        </h1>

        <h2>
            Page Not Found!
        </h2>

        <p>
            The page you are looking for doesn't exist
            or has been moved.
        </p>

        <a href="index.php">
            Back to Main Page
        </a>

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


            <!-- Footer Logo -->

            <div class="col-lg-5 col-md-6 footer_logo">

                <a href="index.php">

                    <img src="images/logo.png"
                         alt="">

                </a>


                <p>

                    Established in 2013 as the first cloud
                    mining provider, Blog has become a
                    multi-functional Blog Categories,
                    trusted by over a million users.

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


            <!-- Subscribe -->

            <div class="col-lg-5 col-md-6">

                <div class="subscribe">

                    <h4>
                        Subscribe
                    </h4>


                    <p>
                        Sign up for our mailing list to get
                        latest updates and offers
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


            <!-- Short Links -->

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


    <!-- Copyright -->

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
     Scroll Top Button -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- =========================================================
     Preloader -->

<div class="preloader"></div>


<!-- =========================================================
     JavaScript -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="js/theme.js"></script>


</body>

</html>