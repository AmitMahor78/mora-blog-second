<?php

require_once __DIR__ . '/includes/maintenance-check.php';
include "includes/db.php";

$breakingQuery = "
    SELECT id, title
    FROM posts
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 5
";

$breakingResult = mysqli_query($conn, $breakingQuery);

$breakingNews = [];

while ($row = mysqli_fetch_assoc($breakingResult)) {
    $breakingNews[] = $row;
}

// Business Posts

$businessQuery = "
    SELECT id, title, excerpt, content, image, created_at
    FROM posts
    WHERE category_id = 1
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$businessResult = mysqli_query($conn, $businessQuery);

$businessPosts = [];

while ($row = mysqli_fetch_assoc($businessResult)) {
    $businessPosts[] = $row;
}


// Travel Posts

$travelQuery = "
    SELECT id, title, excerpt, content, image, created_at
    FROM posts
    WHERE category_id = 2
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 3
";

$travelResult = mysqli_query($conn, $travelQuery);

$travelPosts = [];

while ($row = mysqli_fetch_assoc($travelResult)) {
    $travelPosts[] = $row;
}


// Watch It Posts

$watchQuery = "
    SELECT id, title, excerpt, content, image, created_at
    FROM posts
    WHERE category_id = 6
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$watchResult = mysqli_query($conn, $watchQuery);

$watchPosts = [];

while ($row = mysqli_fetch_assoc($watchResult)) {
    $watchPosts[] = $row;
}

// Fashion Posts

$fashionQuery = "
    SELECT id, title, excerpt, content, image, created_at
    FROM posts
    WHERE category_id = 3
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$fashionResult = mysqli_query($conn, $fashionQuery);

$fashionPosts = [];

while ($row = mysqli_fetch_assoc($fashionResult)) {
    $fashionPosts[] = $row;
}



// Trending News

$trendingQuery = "
    SELECT id, title, image, created_at
    FROM posts
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 3
";

$trendingResult = mysqli_query($conn, $trendingQuery);

$trendingPosts = [];

while ($row = mysqli_fetch_assoc($trendingResult)) {
    $trendingPosts[] = $row;
}


// Sidebar Latest Posts

$sidebarQuery = "
    SELECT id, title, created_at
    FROM posts
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$sidebarResult = mysqli_query($conn, $sidebarQuery);

$sidebarPosts = [];

while ($row = mysqli_fetch_assoc($sidebarResult)) {
    $sidebarPosts[] = $row;
}


// Technology Posts

$technologyQuery = "
    SELECT id, title, created_at
    FROM posts
    WHERE category_id = 4
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$technologyResult = mysqli_query($conn, $technologyQuery);

$technologyPosts = [];

while ($row = mysqli_fetch_assoc($technologyResult)) {
    $technologyPosts[] = $row;
}


// Gaming Posts

$gamingQuery = "
    SELECT id, title, created_at
    FROM posts
    WHERE category_id = 5
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$gamingResult = mysqli_query($conn, $gamingQuery);

$gamingPosts = [];

while ($row = mysqli_fetch_assoc($gamingResult)) {
    $gamingPosts[] = $row;
}


// Home Banner Posts

$bannerQuery = "
    SELECT 
        p.id,
        p.title,
        p.excerpt,
        p.image,
        p.created_at,
        c.name AS category_name
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 7
";

$bannerResult = mysqli_query($conn, $bannerQuery);

$bannerPosts = [];

while ($row = mysqli_fetch_assoc($bannerResult)) {
    $bannerPosts[] = $row;
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="icon" href="images/favicon.png" type="image/x-icon" />
    <!-- Theme tittle -->
    <title>Mora Blog | Html Template</title> 
    
    <!-- Theme style CSS -->     
    <link href="css/bootstrap.min.css" rel="stylesheet"> 
    <link href="css/style.css" rel="stylesheet"> 
    <link href="css/modern.css" rel="stylesheet"> 
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body> 
    
    <!-- Header Top Area -->
   <?php include "includes/header.php"; ?>
    <!-- Header Top Area -->  
    
    <!-- Header Area -->
    <header class="main_header_area" id="header"> 
       <div class="container">
            <div class="header_menu"> 

               <?php include "includes/navbar.php"; ?>
           </div>
        </div>
    </header>
    <!-- Header Area -->   
    
    <!-- Braking News Area --> 
   
<!-- Breaking News Area -->
<div class="container">
    <div class="braking_news row">

        <h4 class="braking_heding col-1">News</h4>

        <div id="newsIndicators"
             class="carousel slide col-11"
             data-ride="carousel">

            <div class="carousel-inner">

                <?php foreach ($breakingNews as $index => $news) { ?>

                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <a href="#">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </a>
                    </div>

                <?php } ?>

            </div>

            <ol class="carousel-indicators">

                <?php foreach ($breakingNews as $index => $news) { ?>

                    <li
                        data-target="#newsIndicators"
                        data-slide-to="<?php echo $index; ?>"
                        class="<?php echo $index === 0 ? 'active' : ''; ?>">
                    </li>

                <?php } ?>

            </ol>

        </div>
    </div>
</div>
<!-- End Breaking News Area -->

    <!-- End Braking News Area --> 
    
    <!-- Home Banner Area --> 
  <!-- Home Banner Area -->
<section class="home_banner_area">

    <div class="container">

        <div class="row home_banner_inner">

            <!-- Main Slider -->
           <div
    id="bannerSlider"
    class="carousel slide banner_slider col-12"
    data-ride="carousel"
>

                <div class="carousel-inner">

                    <?php
                    $sliderPosts = array_slice($bannerPosts, 0, 2);
                    ?>

                    <?php foreach ($sliderPosts as $index => $post) { ?>

                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">

                            <?php if (!empty($post['image'])) { ?>

                                <img
                                    src="<?php echo htmlspecialchars($post['image']); ?>"
                                    alt="<?php echo htmlspecialchars($post['title']); ?>"
                                >

                            <?php } ?>

                            <ul class="special_share">

                                <li>
                                    <a href="#">
                                        <i class="fa fa-twitter"></i>
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <i class="fa fa-linkedin"></i>
                                    </a>
                                </li>

                            </ul>

                            <div class="slider_caption">

                                <h6 class="wow fadeInUp animated">

                                    <?php
                                    echo date(
                                        "M d, Y",
                                        strtotime($post['created_at'])
                                    );
                                    ?>

                                </h6>

                                <a
                                    href="news-details.php?id=<?php echo $post['id']; ?>"
                                    class="wow fadeInUp animated heding"
                                    data-wow-delay="0.2s"
                                >
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>

                                <p
                                    class="wow fadeInUp animated"
                                    data-wow-delay="0.4s"
                                >
                                    <?php echo htmlspecialchars($post['excerpt']); ?>
                                </p>

                                <a
                                    href="#"
                                    class="wow fadeInUp animated tag_btn"
                                    data-wow-delay="0.6s"
                                >
                                    <?php echo htmlspecialchars($post['category_name'] ?? 'News'); ?>
                                </a>

                            </div>

                        </div>

                    <?php } ?>

                </div>

              <a
    class="carousel-control-prev"
    href="#bannerSlider"
    data-slide="prev"
>
                    <i class="fa fa-angle-left"></i>
                </a>

                <a
    class="carousel-control-next"
    href="#bannerSlider"
    data-slide="next"
>
                    <i class="fa fa-angle-right"></i>
                </a>

            </div>


            <!-- Right Side Banner Posts -->

            <?php
            $sideBannerPosts = array_slice($bannerPosts, 2, 2);
            ?>

            <?php foreach ($sideBannerPosts as $post) { ?>

                <div class="col-md-6">

                    <div class="belarus_items">

                        <?php if (!empty($post['image'])) { ?>

                            <img
                                src="<?php echo htmlspecialchars($post['image']); ?>"
                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                            >

                        <?php } ?>

                        <div class="belarus_content">

                            <h6>
                                <?php
                                echo date(
                                    "M d, Y",
                                    strtotime($post['created_at'])
                                );
                                ?>
                            </h6>

                            <a
                                href="news-details.php?id=<?php echo $post['id']; ?>"
                                class="heding wow fadeInUp"
                                data-wow-delay="0.2s"
                            >
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>

                            <a
                                href="#"
                                class="wow fadeInUp tag_btn"
                                data-wow-delay="0.4s"
                            >
                                <?php echo htmlspecialchars($post['category_name'] ?? 'News'); ?>
                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>


            <!-- Magazine Posts -->

            <?php
            $magazinePosts = array_slice($bannerPosts, 4, 3);
            ?>

            <ul class="row magazine">

                <?php foreach ($magazinePosts as $post) { ?>

                    <li class="col-md-4 col-sm-6">

                        <a href="news-details.php?id=<?php echo $post['id']; ?>">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>

                    </li>

                <?php } ?>

            </ul>

        </div>

    </div>

</section>
<!-- End Home Banner Area -->
 
    
    <!-- Post section -->
    <section class="post_section">
        <div class="container">
            <div class="row post_section_inner">
                <!-- Left_sidebar -->
                <div class="col-lg-8 left_sidebar">
                    <!-- Tranding Area Post -->
                <div class="row tranding_post_area">

    <div class="col-12 tranding_tittle">
        <h2>BUSINESS Post</h2>

        <a href="news.php">
            View More <i class="fa fa-arrow-right"></i>
        </a>
    </div>


    <?php foreach ($businessPosts as $index => $post) { ?>

        <div class="col-md-6">

            <div class="tranding_post">

                <?php if (!empty($post['image'])) { ?>

                    <a href="#" class="post_img">

                        <img
                            src="<?php echo htmlspecialchars($post['image']); ?>"
                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                        >

                        <span class="tag_btn">
                            Business
                        </span>

                    </a>

                <?php } ?>


                <div class="post_content">

                    <a href="#" class="t_heding">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>

                    <h6>
                        <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                    </h6>

                </div>

            </div>

        </div>

    <?php } ?>

</div>
                    <!-- Feature Post Area-->
                   <!-- Travel Post Area -->
<div class="row feature_post_area">

    <div class="col-12">
        <div class="feature_tittle">

            <h2>Travel Post</h2>

            <a href="news-2.html">
                View More <i class="fa fa-arrow-right"></i>
            </a>

        </div>
    </div>


    <?php foreach ($travelPosts as $post) { ?>

        <div class="col-12">

            <div class="media feature_post">

                <div class="feture_img">

                    <?php if (!empty($post['image'])) { ?>

                        <a href="#">
                            <img
                                src="<?php echo htmlspecialchars($post['image']); ?>"
                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                            >
                        </a>

                    <?php } ?>

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


                <div class="media-body feture_content">

                    <a href="#" class="f_heding">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>

                    <h6>
                        <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                    </h6>

                    <p>
                        <?php echo htmlspecialchars($post['excerpt']); ?>
                    </p>

                </div>

            </div>

        </div>

    <?php } ?>

</div>
<!-- End Travel Post Area -->
                    <!-- Watch It Area-->
                    
<div class="row watch_it_area">

    <!-- Font Load Post -->
    <div class="col-12">
       
    </div>


    <!-- Ethereum Price Post -->
    <div class="col-12">
        
    </div>


    <!-- Subscribe Area -->
    <div class="col-12">
        <div class="subscribe_area">

            <div class="input-group">

                <input
                    type="text"
                    class="form-control"
                    placeholder="Email address"
                >

                <div class="input-group-prepend">

                    <span class="input-group-text">
                        Subscribe
                    </span>

                </div>

            </div>

        </div>
    </div>


    <!-- Watch It Heading -->
    <div class="col-12">

        <div class="feature_tittle">

            <h2>Watch It</h2>

            <a href="news.php">
    View More <i class="fa fa-arrow-right"></i>
</a>

        </div>

    </div>


    <!-- Dynamic Watch It Posts -->

    <?php foreach ($watchPosts as $post) { ?>

        <div class="col-md-6">

            <div class="watch_video">

                <a
                    href="news-details.php?id=<?php echo $post['id']; ?>"
                    class="video_thumbnail"
                >

                    <?php if (!empty($post['image'])) { ?>

                        <img
                            src="<?php echo htmlspecialchars($post['image']); ?>"
                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                        >

                    <?php } ?>

                    <span class="play_btn">
                        <i class="flaticon-play-button"></i>
                    </span>

                </a>


                <a
                    href="news-details.php?id=<?php echo $post['id']; ?>"
                    class="video_heding"
                >
                    <?php echo htmlspecialchars($post['title']); ?>
                </a>

            </div>

        </div>

    <?php } ?>


    <!-- Watch Text Post 1 -->

    <div class="col-md-6">

        <div class="tranding_post wathc_text">

            <a
                href="news-details.php"
                class="t_heding"
            >
                Stocks Climb as Yields Hold Dollar Falls: Markets Wrap
            </a>

            <h6>
                <span class="tag_btn">
                    Technology
                </span>

                January 21, 2018
            </h6>

        </div>

    </div>


    <!-- Watch Text Post 2 -->

    <div class="col-md-6">

        <div class="tranding_post wathc_text">

            <a
                href="news-details.php"
                class="t_heding"
            >
                Nullam neque leo, placerat eu ante vitae, vehicula.
            </a>

            <h6>
                <span class="tag_btn">
                    Trending
                </span>

                April 25, 2018
            </h6>

        </div>

    </div>

</div>
<!-- End Watch It Area -->
                    
                    <!-- Our Latest  Articles -->
                    <!-- Our Latest Articles -->
<div class="row tranding_post_area fashion_post">

    <div class="col-12">
        <div class="feature_tittle">
            <h2>Fashion</h2>
            <a href="news-2.html">
                View More <i class="fa fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Dynamic Fashion Posts -->

    <?php foreach ($fashionPosts as $post) { ?>

        <?php if (!empty($post['image'])) { ?>

            <!-- Fashion Post With Image -->
            <div class="col-md-6">
                <div class="tranding_post">

                    <a
                        href="news-details.php?id=<?php echo $post['id']; ?>"
                        class="post_img"
                    >
                        <img
                            src="<?php echo htmlspecialchars($post['image']); ?>"
                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                        >

                        <span class="tag_btn">
                            Fashion
                        </span>
                    </a>

                    <div class="post_content">

                        <a
                            href="news-details.php?id=<?php echo $post['id']; ?>"
                            class="t_heding"
                        >
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>

                        <h6>
                            <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                        </h6>

                    </div>

                </div>
            </div>

        <?php } else { ?>

            <!-- Fashion Post Without Image -->
            <div class="col-md-6">

                <div class="tranding_post text_post">

                    <span class="tag_btn">
                        Fashion
                    </span>

                    <a
                        href="news-details.php?id=<?php echo $post['id']; ?>"
                        class="t_heding"
                    >
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>

                    <h6>
                        <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                    </h6>

                </div>

            </div>

        <?php } ?>

    <?php } ?>


    <!-- Font Load Post -->
    <div class="col-12">
        <div class="font_load_post m-0">

            <img
                src="images/post/font-load-3.jpg"
                alt=""
            >

            <div class="post_caption">

                <a
                    href="#"
                    class="wow fadeInUp tag_btn"
                >
                    Travel
                </a>

                <a
                    href="news-details.php"
                    class="wow fadeInUp heding heding_bg"
                    data-wow-delay="0.2s"
                >
                    Neque porro quisquam est qui dolorem
                </a>

            </div>

        </div>
    </div>

</div>
                </div>
                <!-- End left_sidebar -->
                <div class="col-lg-4 right_sidebar"> 
                    <div class="btc_accordion">
                        <div class="item"> 
                            <a href="#" data-toggle="collapse" data-target="#collapseOne">Bitcoin(24h)</a> 
                            <div id="collapseOne" class="collapse show" data-parent=".btc_accordion"> 
                               <div class="accordion_content"> 
                                    <h2>$15212.35</h2>
                                    <h4>EUR: €12632.32 <br>GBP: £11213.76</h4>
                                    <ul class="btc_list">
                                        <li>$15212</li>
                                        <li>$14212</li>
                                        <li>$13212</li>
                                    </ul>
                               </div>
                            </div>
                        </div>
                        <div class="item"> 
                            <a href="#" data-toggle="collapse" class="collapsed" data-target="#collapseTwo">Etherume <span>$740.34</span></a> 
                            <div id="collapseTwo" class="collapse" data-parent=".btc_accordion"> 
                               <div class="accordion_content"> 
                                    <h2>$15212.35</h2>
                                    <h4>EUR: €12632.32 <br>GBP: £11213.76</h4>
                                    <ul class="btc_list">
                                        <li>$15212</li>
                                        <li>$14212</li>
                                        <li>$13212</li>
                                    </ul>
                               </div>
                            </div>
                        </div>
                        <div class="item"> 
                            <a href="#" class="collapsed" data-toggle="collapse" data-target="#collapseThree">Zcash <span>$546.83</span></a> 
                            <div id="collapseThree" class="collapse" data-parent=".btc_accordion">
                               <div class="accordion_content"> 
                                    <h2>$15212.35</h2>
                                    <h4>EUR: €12632.32 <br>GBP:  £11213.76</h4>
                                    <ul class="btc_list">
                                        <li>$15212</li>
                                        <li>$14212</li>
                                        <li>$13212</li>
                                    </ul>
                               </div>
                            </div>
                        </div>
                    </div> 
                    
                 <!-- Trending News -->
<div class="latest_news_widget">

    <h2>Trending News</h2>

    <?php foreach ($trendingPosts as $index => $post) { ?>

        <div class="widget <?php echo $index === count($trendingPosts) - 1 ? 'border-0' : ''; ?>">

            <?php if (!empty($post['image'])) { ?>

                <a href="news-details.php?id=<?php echo $post['id']; ?>">
                    <img
                        src="<?php echo htmlspecialchars($post['image']); ?>"
                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                    >
                </a>

            <?php } ?>

            <a
                href="news-details.php?id=<?php echo $post['id']; ?>"
                class="w_heding"
            >
                <?php echo htmlspecialchars($post['title']); ?>
            </a>

        </div>

    <?php } ?>

    <a href="news.php" class="load_more_btn">
        Load more..
    </a>

</div>



<!-- Dynamic Sidebar Text Widget -->
<div class="text_widget_area">

    <?php foreach ($sidebarPosts as $index => $post) { ?>

        <div class="text_widget <?php echo $index === count($sidebarPosts) - 1 ? 'm-0' : ''; ?>">

            <a href="news-details.php?id=<?php echo $post['id']; ?>">
                <?php echo htmlspecialchars($post['title']); ?>
            </a>

            <h6 class="<?php echo $index === count($sidebarPosts) - 1 ? 'padding-bottom-0' : ''; ?>">
                <?php echo date("H:i", strtotime($post['created_at'])); ?>,
                Latest News
            </h6>

        </div>

    <?php } ?>

</div>
                    <!-- Advertisement -->
                    <div class="advertisement">
                        <a href="#"><img src="images/advertisement-1.jpg" alt=""></a> 
                    </div> 
                    <!-- Video widget -->
                    <div class="video_widget">
                        <div class="watch_video">
                            <a href="video-details.php" class="video_thumbnail">
                                <img src="images/post/wathc-5.jpg" alt="">
                                <span class="play_btn"><i class="flaticon-play-button">5.16</i></span>
                            </a>
                            <a href="video-details.php" class="video_heding">The easiest way to buy Bitcoin and Ethereum</a>
                        </div>
                    </div>
                    <!-- Advertisement -->
                    <div class="advertisement_2">
                        <a href="#">
                            <img src="images/advertisement-2.jpg" alt="">
                        </a> 
                    </div> 
                    <!-- Social Sidget -->
                    <ul class="social_widget">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#" class="twitter"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#" class="pinterest"><i class="fa fa-pinterest"></i></a></li>
                        <li><a href="#" class="linkedin"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="#" class="google"><i class="fa fa-google-plus"></i></a></li>
                        <li><a href="#" class="instagram"><i class="fa fa-instagram"></i></a></li>
                    </ul> 
                    <!-- Tabs Widget -->
                    <div class="tabs_widge">

    <div class="nav nav-tabs" id="nav-tab" role="tablist">

        <a
            class="active"
            id="nav-home-tab"
            data-toggle="tab"
            href="#technology"
            role="tab"
        >
            Technology
        </a>

        <a
            id="nav-profile-tab"
            data-toggle="tab"
            href="#gaming"
            role="tab"
        >
            Gaming
        </a>

    </div>


    <div class="tab-content" id="nav-tabContent">

        <!-- Technology -->

        <div
            class="tab-pane fade show active text_widget_area"
            id="technology"
            role="tabpanel"
        >

            <?php foreach ($technologyPosts as $index => $post) { ?>

                <div class="text_widget <?php echo $index === count($technologyPosts) - 1 ? 'm-0' : ''; ?>">

                    <a href="news-details.php?id=<?php echo $post['id']; ?>">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>

                    <h6 class="<?php echo $index === count($technologyPosts) - 1 ? 'padding-bottom-0' : ''; ?>">

                        <?php echo date("H:i", strtotime($post['created_at'])); ?>,
                        Press Releases

                        <span class="tech">
                            Technology
                        </span>

                    </h6>

                </div>

            <?php } ?>

        </div>


        <!-- Gaming -->

        <div
            class="tab-pane fade text_widget_area"
            id="gaming"
            role="tabpanel"
        >

            <?php foreach ($gamingPosts as $index => $post) { ?>

                <div class="text_widget <?php echo $index === count($gamingPosts) - 1 ? 'm-0' : ''; ?>">

                    <a href="news-details.php?id=<?php echo $post['id']; ?>">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>

                    <h6 class="<?php echo $index === count($gamingPosts) - 1 ? 'padding-bottom-0' : ''; ?>">

                        <?php echo date("H:i", strtotime($post['created_at'])); ?>,
                        Press Releases

                        <span class="gaming">
                            Gaming
                        </span>

                    </h6>

                </div>

            <?php } ?>

        </div>

    </div>

</div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Post section -->
    
    <!-- Connect with us -->
    <section class="connect_with_us">
        <div class="container">
            <h2>Connect with us</h2>
            <ul class="contact_with_socail">
                <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                <li><a href="#"><i class="fa fa-youtube"></i></a></li>
            </ul>
        </div>
    </section>
    <!-- End Connect with us -->
        
    <!-- Footer Area -->  
      <?php include "includes/footer.php"; ?>
    <!-- End Footer Area -->  
    
    <!-- Scroll Top Button -->
    <button class="scroll-top">
        <i class="fa fa-angle-double-up"></i>
    </button>
    
    <!-- Preloader -->
    <div class="preloader"></div>
    
    <!-- jQuery v3.2.1 -->
    <script src="js/jquery-3.3.1.min.js"></script> 
    <script src="js/popper.min.js"></script>
    <!-- Bootstrap v4.1.0 -->
    <script src="js/bootstrap.min.js"></script> 
    <!-- Extra Plugin -->
    <script src="vendors/animate-css/wow.min.js"></script> 
    
    <!-- Theme js / Custom js -->
    <script src="js/theme.js"></script>
</body>
</html>