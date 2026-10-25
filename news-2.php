<?php
require_once __DIR__ . '/includes/maintenance-check.php';
include "includes/db.php";
include "includes/queries.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="images/favicon.png" type="image/x-icon">

    <title>Mora Blog | Html Template</title>

    <!-- Theme CSS -->
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>


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


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="container">

    <div class="pages_heder">

        <h2>
            Blog List 2
        </h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>

            <li>
                <a href="#">
                    Blog
                </a>
            </li>

            <li>
                <a href="news-2.php" class="active">
                    Blog v2
                </a>
            </li>

        </ol>

    </div>

</div>


<!-- =========================================================
     POST SECTION
========================================================= -->

<section class="post_section news_post_2">

    <div class="container">

        <div class="row post_section_inner">


            <!-- =================================================
                 LEFT SIDEBAR
            ================================================== -->

            <div class="col-lg-8 left_sidebar">


                <!-- Feature Post Area -->

                <div class="row feature_post_area">


                    <!-- Section Title -->

                    <div class="col-12">

                        <div class="feature_tittle">

                            <h2>
                                Travel Post
                            </h2>

                            <div class="post_select_button">

                                <select class="post_select">

                                    <option>
                                        Travel Post
                                    </option>

                                    <option>
                                        Finance Post
                                    </option>

                                    <option>
                                        Trading Post
                                    </option>

                                    <option>
                                        Tech Post
                                    </option>

                                    <option>
                                        Video Post
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         DYNAMIC TRAVEL POSTS
                    ================================================== -->

                    <?php if ($travelResult && mysqli_num_rows($travelResult) > 0) { ?>


                        <?php while ($post = mysqli_fetch_assoc($travelResult)) { ?>


                            <div class="col-12">

                                <div class="media feature_post">


                                    <!-- Image -->

                                    <div class="feture_img">

                                        <a href="news-details.php?id=<?php echo $post['id']; ?>">

                                            <img
                                                src="<?php echo htmlspecialchars($post['image']); ?>"
                                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                                            >

                                        </a>

                                        <!-- Share -->

                                        <ul class="special_share">

                                            <li>

                                                <a href="#">

                                                    <i class="fa fa-twitter"></i>

                                                </a>

                                            </li>

                                            <li>

                                                <a href="#">

                                                    <i class="fa fa-facebook"></i>

                                                </a>

                                            </li>

                                            <li>

                                                <a href="#">

                                                    <i class="fa fa-google-plus"></i>

                                                </a>

                                            </li>

                                        </ul>

                                    </div>


                                    <!-- Content -->

                                    <div class="media-body feture_content">


                                        <a
                                            href="news-details.php?id=<?php echo $post['id']; ?>"
                                            class="f_heding"
                                        >

                                            <?php
                                            echo htmlspecialchars($post['title']);
                                            ?>

                                        </a>


                                        <h6>

                                            <?php
                                            echo date(
                                                "M d, Y",
                                                strtotime($post['created_at'])
                                            );
                                            ?>

                                            <span>|</span>

                                            <?php
                                            echo htmlspecialchars(
                                                $post['author_name'] ?? 'Admin'
                                            );
                                            ?>

                                        </h6>


                                        <p>

                                            <?php
                                            echo htmlspecialchars(
                                                $post['excerpt']
                                            );
                                            ?>

                                        </p>


                                    </div>

                                </div>

                            </div>


                        <?php } ?>


                    <?php } else { ?>


                        <div class="col-12">

                            <p>
                                No travel posts available.
                            </p>

                        </div>


                    <?php } ?>


                    <!-- =================================================
                         PAGINATION
                    ================================================== -->

                    <?php if ($totalPages > 1) { ?>

                        <div class="col-12">

                            <ul class="pagination">


                                <!-- Previous -->

                                <?php if ($page > 1) { ?>

                                    <li>

                                        <a
                                            href="news-2.php?page=<?php echo $page - 1; ?>"
                                        >

                                            <i class="fa fa-angle-left"></i>

                                        </a>

                                    </li>

                                <?php } ?>


                                <!-- Page Numbers -->

                                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>

                                    <li
                                        class="<?php echo ($i == $page) ? 'active' : ''; ?>"
                                    >

                                        <a
                                            href="news-2.php?page=<?php echo $i; ?>"
                                        >

                                            <?php echo $i; ?>

                                        </a>

                                    </li>

                                <?php } ?>


                                <!-- Next -->

                                <?php if ($page < $totalPages) { ?>

                                    <li>

                                        <a
                                            href="news-2.php?page=<?php echo $page + 1; ?>"
                                        >

                                            <i class="fa fa-angle-right"></i>

                                        </a>

                                    </li>

                                <?php } ?>


                            </ul>

                        </div>

                    <?php } ?>


                </div>
                <!-- END feature_post_area -->


            </div>
            <!-- END left_sidebar -->


            <!-- =================================================
                 RIGHT SIDEBAR
            ================================================== -->

            <div class="col-lg-4 right_sidebar">


                <!-- Search -->

                <form action="news-2.php" method="GET">

    <div class="input-group">

        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search"
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <div class="input-group-append">

            <button
                type="submit"
                class="input-group-text"
            >
                <i class="fa fa-search"></i>
            </button>

        </div>

    </div>

</form>

<?php if ($search !== '') { ?>

    <div class="text_widget_area">

        <?php if ($searchResult && mysqli_num_rows($searchResult) > 0) { ?>

            <?php while ($result = mysqli_fetch_assoc($searchResult)) { ?>

                <div class="text_widget">

                    <a href="news-details.php?id=<?php echo $result['id']; ?>">
                        <?php echo htmlspecialchars($result['title']); ?>
                    </a>

                    <h6>
                        <?php echo date("M d, Y", strtotime($result['created_at'])); ?>
                    </h6>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="text_widget m-0">
                <p>No posts found.</p>
            </div>

        <?php } ?>

    </div>

<?php } ?>

                <!-- Categories -->

                <!-- Post Categories -->

<div class="categories">

    <h3>
        Post Categories
    </h3>

    <ul class="cpost_categories">


        <?php if ($categoryResult && mysqli_num_rows($categoryResult) > 0) { ?>


            <?php while ($category = mysqli_fetch_assoc($categoryResult)) { ?>

                <li>

                    <a
                        href="news.php?category=<?php echo urlencode($category['slug']); ?>"
                    >

                        <?php
                        echo htmlspecialchars($category['name']);
                        ?>

                        <span>
                            <?php
                            echo (int) $category['post_count'];
                            ?>
                        </span>

                    </a>

                </li>

            <?php } ?>


        <?php } else { ?>

            <li>
                <a href="#">
                    No Categories
                </a>
            </li>

        <?php } ?>


        <!-- All News -->

        <li>

            <a href="news.php">

                All News

                <span>
                    <?php echo $allNewsCount; ?>
                </span>

            </a>

        </li>


    </ul>

</div>


                <!-- Trending News -->

                <!-- =========================================================
     TRENDING NEWS
========================================================= -->

<div class="latest_news_widget p-0">

    <h2>
        Trending News
    </h2>


    <?php if ($trendingResult && mysqli_num_rows($trendingResult) > 0) { ?>


        <?php
        $trendingCount = mysqli_num_rows($trendingResult);
        $currentTrending = 0;
        ?>


        <?php while ($trending = mysqli_fetch_assoc($trendingResult)) { ?>

            <?php
            $currentTrending++;

            $borderClass = ($currentTrending == $trendingCount)
                ? 'border-0'
                : '';
            ?>


            <div class="widget <?php echo $borderClass; ?>">

                <!-- Post Image -->

                <a href="news-details.php?id=<?php echo $trending['id']; ?>">

                    <img
                        src="<?php echo htmlspecialchars($trending['image']); ?>"
                        alt="<?php echo htmlspecialchars($trending['title']); ?>"
                    >

                </a>


                <!-- Post Title -->

                <a
                    href="news-details.php?id=<?php echo $trending['id']; ?>"
                    class="w_heding"
                >

                    <?php
                    echo htmlspecialchars($trending['title']);
                    ?>

                </a>

            </div>


        <?php } ?>


    <?php } else { ?>

        <div class="widget border-0">

            <p>
                No trending news available.
            </p>

        </div>

    <?php } ?>


    <!-- Load More -->

    <a
        href="news.php"
        class="load_more_btn"
    >
        Load more..
    </a>

</div>

                <!-- Text Widget -->

                <!-- =========================================================
     TEXT WIDGET
========================================================= -->

<div class="text_widget_area">

    <?php if ($textWidgetResult && mysqli_num_rows($textWidgetResult) > 0) { ?>

        <?php
        $textWidgetCount = mysqli_num_rows($textWidgetResult);
        $currentTextWidget = 0;
        ?>

        <?php while ($textPost = mysqli_fetch_assoc($textWidgetResult)) { ?>

            <?php
            $currentTextWidget++;

            $marginClass = ($currentTextWidget == $textWidgetCount)
                ? 'm-0'
                : '';
            ?>

            <div class="text_widget <?php echo $marginClass; ?>">

                <a href="news-details.php?id=<?php echo $textPost['id']; ?>">

                    <?php
                    echo htmlspecialchars($textPost['title']);
                    ?>

                </a>

                <h6 class="<?php echo ($currentTextWidget == $textWidgetCount) ? 'padding-bottom-0' : ''; ?>">

                    <?php
                    echo date(
                        "H:i",
                        strtotime($textPost['created_at'])
                    );
                    ?>

                    ,

                    <?php
                    echo htmlspecialchars(
                        $textPost['category_name'] ?? 'News'
                    );
                    ?>

                </h6>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="text_widget m-0">

            <p>
                No news available.
            </p>

        </div>

    <?php } ?>

</div>


                <!-- Advertisement -->

               <div class="advertisement_area">

    <?php if ($advertisementResult && mysqli_num_rows($advertisementResult) > 0) { ?>

        <?php $advertisement = mysqli_fetch_assoc($advertisementResult); ?>

        <div class="advertisement">

            <?php if (!empty($advertisement['link'])) { ?>

                <a href="<?php echo htmlspecialchars($advertisement['link']); ?>" target="_blank">

                    <img 
                        src="<?php echo htmlspecialchars($advertisement['image']); ?>" 
                        alt="<?php echo htmlspecialchars($advertisement['title']); ?>"
                    >

                </a>

            <?php } else { ?>

                <img 
                    src="<?php echo htmlspecialchars($advertisement['image']); ?>" 
                    alt="<?php echo htmlspecialchars($advertisement['title']); ?>"
                >

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="advertisement">
            <p>No advertisement available.</p>
        </div>

    <?php } ?>

</div>

                <!-- Video Widget -->

               <div class="video_widget">

    <?php if ($videoWidgetResult && mysqli_num_rows($videoWidgetResult) > 0) { ?>

        <?php $video = mysqli_fetch_assoc($videoWidgetResult); ?>

        <div class="video_thumb">

            <a href="video-details.php?id=<?php echo $video['id']; ?>">

                <img 
                    src="<?php echo htmlspecialchars($video['thumbnail']); ?>" 
                    alt="<?php echo htmlspecialchars($video['title']); ?>"
                >

                <i class="fa fa-play"></i>

            </a>

        </div>

        <a 
            href="video-details.php?id=<?php echo $video['id']; ?>" 
            class="video_heding"
        >
            <?php echo htmlspecialchars($video['title']); ?>
        </a>

    <?php } else { ?>

        <div class="video_thumb">
            <p>No video available.</p>
        </div>

    <?php } ?>

</div>


                <!-- Social Widget -->

                <div class="social_widget">

    <h2>Follow Us</h2>

    <ul class="social_widget_list">

        <?php if (!empty($socialWidgetLinks['facebook'])) { ?>
            <li>
                <a href="<?php echo htmlspecialchars($socialWidgetLinks['facebook']); ?>" target="_blank">
                    <i class="fa fa-facebook"></i>
                </a>
            </li>
        <?php } ?>

        <?php if (!empty($socialWidgetLinks['twitter'])) { ?>
            <li>
                <a href="<?php echo htmlspecialchars($socialWidgetLinks['twitter']); ?>" target="_blank">
                    <i class="fa fa-twitter"></i>
                </a>
            </li>
        <?php } ?>

        <?php if (!empty($socialWidgetLinks['pinterest'])) { ?>
            <li>
                <a href="<?php echo htmlspecialchars($socialWidgetLinks['pinterest']); ?>" target="_blank">
                    <i class="fa fa-pinterest"></i>
                </a>
            </li>
        <?php } ?>

        <?php if (!empty($socialWidgetLinks['linkedin'])) { ?>
            <li>
                <a href="<?php echo htmlspecialchars($socialWidgetLinks['linkedin']); ?>" target="_blank">
                    <i class="fa fa-linkedin"></i>
                </a>
            </li>
        <?php } ?>

        <?php if (!empty($socialWidgetLinks['google'])) { ?>
            <li>
                <a href="<?php echo htmlspecialchars($socialWidgetLinks['google']); ?>" target="_blank">
                    <i class="fa fa-google"></i>
                </a>
            </li>
        <?php } ?>

        <?php if (!empty($socialWidgetLinks['instagram'])) { ?>
            <li>
                <a href="<?php echo htmlspecialchars($socialWidgetLinks['instagram']); ?>" target="_blank">
                    <i class="fa fa-instagram"></i>
                </a>
            </li>
        <?php } ?>

    </ul>

</div>

                <!-- Tabs Widget -->

               <div class="tabs_widget">

    <ul class="nav nav-tabs" role="tablist">

        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#technology">
                Technology
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#gaming">
                Gaming
            </a>
        </li>

    </ul>


    <div class="tab-content">

        <!-- TECHNOLOGY -->
        <div id="technology" class="tab-pane fade show active">

            <?php if ($technologyResult && mysqli_num_rows($technologyResult) > 0) { ?>

                <?php while ($tech = mysqli_fetch_assoc($technologyResult)) { ?>

                    <div class="tab_post">

                        <a href="news-details.php?id=<?php echo $tech['id']; ?>">

                            <img
                                src="<?php echo htmlspecialchars($tech['image']); ?>"
                                alt="<?php echo htmlspecialchars($tech['title']); ?>"
                            >

                        </a>

                        <div class="tab_post_content">

                            <a href="news-details.php?id=<?php echo $tech['id']; ?>">
                                <?php echo htmlspecialchars($tech['title']); ?>
                            </a>

                            <h6>
                                <?php echo date("M d, Y", strtotime($tech['created_at'])); ?>
                            </h6>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <p>No Technology posts available.</p>

            <?php } ?>

        </div>


        <!-- GAMING -->
        <div id="gaming" class="tab-pane fade">

            <?php if ($gamingResult && mysqli_num_rows($gamingResult) > 0) { ?>

                <?php while ($game = mysqli_fetch_assoc($gamingResult)) { ?>

                    <div class="tab_post">

                        <a href="news-details.php?id=<?php echo $game['id']; ?>">

                            <img
                                src="<?php echo htmlspecialchars($game['image']); ?>"
                                alt="<?php echo htmlspecialchars($game['title']); ?>"
                            >

                        </a>

                        <div class="tab_post_content">

                            <a href="news-details.php?id=<?php echo $game['id']; ?>">
                                <?php echo htmlspecialchars($game['title']); ?>
                            </a>

                            <h6>
                                <?php echo date("M d, Y", strtotime($game['created_at'])); ?>
                            </h6>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <p>No Gaming posts available.</p>

            <?php } ?>

        </div>

    </div>

</div>


            </div>
            <!-- END right_sidebar -->


        </div>

    </div>

</section>


<!-- =========================================================
     CONNECT WITH US
========================================================= -->

<!-- Connect with us -->

<section class="connect_with_us">

    <div class="container">

        <h2>Connect with us</h2>

        <ul class="contact_with_socail">

            <?php if (!empty($socialWidgetLinks['google'])) { ?>
                <li>
                    <a href="<?php echo htmlspecialchars($socialWidgetLinks['google']); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fa fa-google-plus"></i>
                    </a>
                </li>
            <?php } ?>

            <?php if (!empty($socialWidgetLinks['linkedin'])) { ?>
                <li>
                    <a href="<?php echo htmlspecialchars($socialWidgetLinks['linkedin']); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fa fa-linkedin"></i>
                    </a>
                </li>
            <?php } ?>

            <?php if (!empty($socialWidgetLinks['instagram'])) { ?>
                <li>
                    <a href="<?php echo htmlspecialchars($socialWidgetLinks['instagram']); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fa fa-instagram"></i>
                    </a>
                </li>
            <?php } ?>

            <?php if (!empty($socialWidgetLinks['twitter'])) { ?>
                <li>
                    <a href="<?php echo htmlspecialchars($socialWidgetLinks['twitter']); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fa fa-twitter"></i>
                    </a>
                </li>
            <?php } ?>

            <?php if (!empty($socialWidgetLinks['youtube'])) { ?>
                <li>
                    <a href="<?php echo htmlspecialchars($socialWidgetLinks['youtube']); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fa fa-youtube"></i>
                    </a>
                </li>
            <?php } ?>

        </ul>

    </div>

</section>

<!-- End Connect with us -->


<!-- =========================================================
     FOOTER
========================================================= -->
<?php include "includes/footer.php"; ?>


<!-- Scroll Top -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- Preloader -->

<div class="preloader"></div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="vendors/bootstrap-selector/jquery.nice-select.min.js"></script>

<script src="js/theme.js"></script>


</body>

</html>