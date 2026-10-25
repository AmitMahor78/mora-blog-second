<?php
require_once __DIR__ . '/includes/maintenance-check.php';
include "includes/db.php";

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function getCategoryId($conn, $slug)
{
    $slugSafe = mysqli_real_escape_string($conn, $slug);

    $query = "
        SELECT id
        FROM categories
        WHERE slug = '$slugSafe'
        LIMIT 1
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return (int)$row['id'];
    }

    return 0;
}


/*
|--------------------------------------------------------------------------
| Breaking News
|--------------------------------------------------------------------------
*/

$breakingQuery = "
    SELECT
        p.id,
        p.title,
        p.created_at
    FROM posts p
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 3
";

$breakingResult = mysqli_query($conn, $breakingQuery);


/*
|--------------------------------------------------------------------------
| Home Banner Posts
|--------------------------------------------------------------------------
*/

$bannerQuery = "
    SELECT
        p.id,
        p.title,
        p.image,
        p.created_at,
        c.name AS category_name,
        c.slug AS category_slug
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 3
";

$bannerResult = mysqli_query($conn, $bannerQuery);

$bannerPosts = [];

if ($bannerResult) {
    while ($row = mysqli_fetch_assoc($bannerResult)) {
        $bannerPosts[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Banner Bottom Posts
|--------------------------------------------------------------------------
*/

$bannerBottomQuery = "
    SELECT
        p.id,
        p.title,
        p.image,
        p.created_at,
        c.name AS category_name,
        c.slug AS category_slug
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 2 OFFSET 3
";

$bannerBottomResult = mysqli_query($conn, $bannerBottomQuery);

$bannerBottomPosts = [];

if ($bannerBottomResult) {
    while ($row = mysqli_fetch_assoc($bannerBottomResult)) {
        $bannerBottomPosts[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Category IDs
|--------------------------------------------------------------------------
*/

$fashionCategoryId = getCategoryId($conn, 'fashion');
$businessCategoryId = getCategoryId($conn, 'business');
$travelCategoryId = getCategoryId($conn, 'travel');
$technologyCategoryId = getCategoryId($conn, 'technology');
$gamingCategoryId = getCategoryId($conn, 'gaming');


/*
|--------------------------------------------------------------------------
| Fashion Posts
|--------------------------------------------------------------------------
*/

$fashionPosts = [];

if ($fashionCategoryId > 0) {

    $fashionQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.created_at,
            a.name AS author_name,
            c.name AS category_name
        FROM posts p
        LEFT JOIN users a ON p.author_id = a.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
          AND p.category_id = $fashionCategoryId
        ORDER BY p.created_at DESC
        LIMIT 2
    ";

    $fashionResult = mysqli_query($conn, $fashionQuery);

    if ($fashionResult) {
        while ($row = mysqli_fetch_assoc($fashionResult)) {
            $fashionPosts[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Business Posts
|--------------------------------------------------------------------------
*/

$businessPosts = [];

if ($businessCategoryId > 0) {

    $businessQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.created_at,
            a.name AS author_name,
            c.name AS category_name
        FROM posts p
        LEFT JOIN users a ON p.author_id = a.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
          AND p.category_id = $businessCategoryId
        ORDER BY p.created_at DESC
        LIMIT 4
    ";

    $businessResult = mysqli_query($conn, $businessQuery);

    if ($businessResult) {
        while ($row = mysqli_fetch_assoc($businessResult)) {
            $businessPosts[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Watch It Videos
|--------------------------------------------------------------------------
*/

$videosQuery = "
    SELECT
        id,
        title,
        thumbnail
    FROM videos
    WHERE status = 'active'
    ORDER BY created_at DESC
    LIMIT 4
";

$videosResult = mysqli_query($conn, $videosQuery);

$videos = [];

if ($videosResult) {
    while ($row = mysqli_fetch_assoc($videosResult)) {
        $videos[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Travel Posts
|--------------------------------------------------------------------------
*/

$travelPosts = [];

if ($travelCategoryId > 0) {

    $travelQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.excerpt,
            p.created_at,
            a.name AS author_name,
            c.name AS category_name
        FROM posts p
        LEFT JOIN users a ON p.author_id = a.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
          AND p.category_id = $travelCategoryId
        ORDER BY p.created_at DESC
        LIMIT 3
    ";

    $travelResult = mysqli_query($conn, $travelQuery);

    if ($travelResult) {
        while ($row = mysqli_fetch_assoc($travelResult)) {
            $travelPosts[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Trending News
|--------------------------------------------------------------------------
*/

$trendingQuery = "
    SELECT
        p.id,
        p.title,
        p.image,
        p.created_at
    FROM posts p
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 3
";

$trendingResult = mysqli_query($conn, $trendingQuery);

$trendingPosts = [];

if ($trendingResult) {
    while ($row = mysqli_fetch_assoc($trendingResult)) {
        $trendingPosts[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Text Widget
|--------------------------------------------------------------------------
*/

$textWidgetQuery = "
    SELECT
        id,
        title,
        created_at
    FROM posts
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 4
";

$textWidgetResult = mysqli_query($conn, $textWidgetQuery);

$textWidgets = [];

if ($textWidgetResult) {
    while ($row = mysqli_fetch_assoc($textWidgetResult)) {
        $textWidgets[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Advertisement
|--------------------------------------------------------------------------
*/

$advertisementQuery = "
    SELECT
        id,
        image,
        link
    FROM advertisements
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1
";

$advertisementResult = mysqli_query($conn, $advertisementQuery);

$advertisement = null;

if ($advertisementResult) {
    $advertisement = mysqli_fetch_assoc($advertisementResult);
}


/*
|--------------------------------------------------------------------------
| Second Advertisement
|--------------------------------------------------------------------------
*/

$advertisementSecondQuery = "
    SELECT
        id,
        image,
        link
    FROM advertisements
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1 OFFSET 1
";

$advertisementSecondResult = mysqli_query($conn, $advertisementSecondQuery);

$advertisementSecond = null;

if ($advertisementSecondResult) {
    $advertisementSecond = mysqli_fetch_assoc($advertisementSecondResult);
}


/*
|--------------------------------------------------------------------------
| Social Links
|--------------------------------------------------------------------------
*/

$socialQuery = "
    SELECT
        platform,
        url
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


/*
|--------------------------------------------------------------------------
| Technology Posts
|--------------------------------------------------------------------------
*/

$technologyPosts = [];

if ($technologyCategoryId > 0) {

    $technologyQuery = "
        SELECT
            p.id,
            p.title,
            p.created_at,
            c.name AS category_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
          AND p.category_id = $technologyCategoryId
        ORDER BY p.created_at DESC
        LIMIT 4
    ";

    $technologyResult = mysqli_query($conn, $technologyQuery);

    if ($technologyResult) {
        while ($row = mysqli_fetch_assoc($technologyResult)) {
            $technologyPosts[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Gaming Posts
|--------------------------------------------------------------------------
*/

$gamingPosts = [];

if ($gamingCategoryId > 0) {

    $gamingQuery = "
        SELECT
            p.id,
            p.title,
            p.created_at,
            c.name AS category_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
          AND p.category_id = $gamingCategoryId
        ORDER BY p.created_at DESC
        LIMIT 4
    ";

    $gamingResult = mysqli_query($conn, $gamingQuery);

    if ($gamingResult) {
        while ($row = mysqli_fetch_assoc($gamingResult)) {
            $gamingPosts[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Site Settings
|--------------------------------------------------------------------------
*/

$settingsQuery = "
    SELECT
        site_name,
        site_description,
        location,
        working_days,
        closing_day,
        copyright_year,
        developer_name,
        developer_url
    FROM site_settings
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1
";

$settingsResult = mysqli_query($conn, $settingsQuery);

$settings = null;

if ($settingsResult) {
    $settings = mysqli_fetch_assoc($settingsResult);
}

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
          type="image/x-icon">

    <title>
        <?php echo htmlspecialchars($settings['site_name'] ?? 'Mora Blog'); ?>
    </title>

    <link href="css/style.css" rel="stylesheet">

</head>

<body>


<!-- =========================================================
     HEADER TOP
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
     BREAKING NEWS
========================================================= -->

<div class="container">

    <div class="braking_news row">

        <h4 class="braking_heding col-1">
            News
        </h4>

        <div id="newsIndicators"
             class="carousel slide col-11"
             data-ride="carousel">

            <div class="carousel-inner">

                <?php

                $breakingIndex = 0;

                if ($breakingResult) {

                    while ($breaking = mysqli_fetch_assoc($breakingResult)) {

                        ?>

                        <div class="carousel-item <?php echo ($breakingIndex == 0) ? 'active' : ''; ?>">

                            <a href="news-details.php?id=<?php echo (int)$breaking['id']; ?>">

                                <?php
                                echo htmlspecialchars($breaking['title']);
                                ?>

                            </a>

                        </div>

                        <?php

                        $breakingIndex++;
                    }
                }

                ?>

            </div>


            <?php if ($breakingIndex > 0) { ?>

                <ol class="carousel-indicators">

                    <?php for ($i = 0; $i < $breakingIndex; $i++) { ?>

                        <li
                            data-target="#newsIndicators"
                            data-slide-to="<?php echo $i; ?>"
                            class="<?php echo ($i == 0) ? 'active' : ''; ?>">
                        </li>

                    <?php } ?>

                </ol>

            <?php } ?>

        </div>

    </div>

</div>


<!-- =========================================================
     HOME BANNER
========================================================= -->

<section class="home_banner_area home_banner_2">

    <div class="container">

        <div class="row home_banner_inner">


            <!-- Main Banner Slider -->

            <div class="carousel slide banner_slider bs_2 col-12"
                 data-ride="carousel">

                <div class="carousel-inner">

                    <?php

                    foreach ($bannerPosts as $index => $banner) {

                        ?>

                        <div class="carousel-item <?php echo ($index == 0) ? 'active' : ''; ?>">

                            <img
                                src="<?php echo htmlspecialchars($banner['image']); ?>"
                                alt="<?php echo htmlspecialchars($banner['title']); ?>"
                            >


                            <ul class="special_share">

                                <?php if (!empty($socialLinks['twitter'])) { ?>

                                    <li>
                                        <a href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                                           target="_blank"
                                           rel="noopener noreferrer">

                                            <i class="fa fa-twitter"></i>

                                        </a>
                                    </li>

                                <?php } ?>


                                <?php if (!empty($socialLinks['linkedin'])) { ?>

                                    <li>
                                        <a href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                                           target="_blank"
                                           rel="noopener noreferrer">

                                            <i class="fa fa-linkedin"></i>

                                        </a>
                                    </li>

                                <?php } ?>

                            </ul>


                            <div class="slider_caption">

                                <h6 class="wow fadeInUp animated">

                                    <?php
                                    echo date(
                                        'F d, Y',
                                        strtotime($banner['created_at'])
                                    );
                                    ?>

                                </h6>


                                <a
                                    href="news-details.php?id=<?php echo (int)$banner['id']; ?>"
                                    class="wow fadeInUp animated heding"
                                    data-wow-delay="0.2s">

                                    <?php
                                    echo htmlspecialchars($banner['title']);
                                    ?>

                                </a>


                                <a
                                    href="news.php?category=<?php echo urlencode($banner['category_slug']); ?>"
                                    class="wow fadeInUp animated tag_btn"
                                    data-wow-delay="0.5s">

                                    <?php
                                    echo htmlspecialchars($banner['category_name']);
                                    ?>

                                </a>

                            </div>

                        </div>

                        <?php

                    }

                    ?>

                </div>


                <ol class="carousel-indicators">

                    <?php foreach ($bannerPosts as $index => $banner) { ?>

                        <li
                            data-target=".banner_slider"
                            data-slide-to="<?php echo $index; ?>"
                            class="<?php echo ($index == 0) ? 'active' : ''; ?>">
                        </li>

                    <?php } ?>

                </ol>

            </div>


            <!-- Bottom Banner 1 -->

            <?php if (!empty($bannerBottomPosts[0])) {

                $bottomPost = $bannerBottomPosts[0];

                ?>

                <div class="col-lg-8 col-md-7">

                    <div class="belarus_items bi_2">

                        <img
                            src="<?php echo htmlspecialchars($bottomPost['image']); ?>"
                            alt="<?php echo htmlspecialchars($bottomPost['title']); ?>"
                        >

                        <div class="belarus_content">

                            <h6>

                                <?php
                                echo date(
                                    'F d, Y',
                                    strtotime($bottomPost['created_at'])
                                );
                                ?>

                            </h6>


                            <a
                                href="news-details.php?id=<?php echo (int)$bottomPost['id']; ?>"
                                class="heding">

                                <?php
                                echo htmlspecialchars($bottomPost['title']);
                                ?>

                            </a>


                            <a
                                href="news.php?category=<?php echo urlencode($bottomPost['category_slug']); ?>"
                                class="tag_btn">

                                <?php
                                echo htmlspecialchars($bottomPost['category_name']);
                                ?>

                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>


            <!-- Bottom Banner 2 -->

            <?php if (!empty($bannerBottomPosts[1])) {

                $bottomPost = $bannerBottomPosts[1];

                ?>

                <div class="col-lg-4 col-md-5">

                    <div class="belarus_items bi_2">

                        <img
                            src="<?php echo htmlspecialchars($bottomPost['image']); ?>"
                            alt="<?php echo htmlspecialchars($bottomPost['title']); ?>"
                        >

                        <div class="belarus_content bc_2">

                            <h6>

                                <?php
                                echo date(
                                    'F d, Y',
                                    strtotime($bottomPost['created_at'])
                                );
                                ?>

                            </h6>


                            <a
                                href="news-details.php?id=<?php echo (int)$bottomPost['id']; ?>"
                                class="heding">

                                <?php
                                echo htmlspecialchars($bottomPost['title']);
                                ?>

                            </a>


                            <a
                                href="news.php?category=<?php echo urlencode($bottomPost['category_slug']); ?>"
                                class="tag_btn">

                                <?php
                                echo htmlspecialchars($bottomPost['category_name']);
                                ?>

                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>


        </div>

    </div>

</section>


<!-- =========================================================
     POST SECTION
========================================================= -->

<section class="post_section">

    <div class="container">

        <div class="row post_section_inner">


            <!-- =================================================
                 LEFT SIDEBAR
            ================================================== -->

            <div class="col-lg-8 left_sidebar">


                <!-- =================================================
                     FASHION POSTS
                ================================================== -->

                <div class="row tranding_post_area">

                    <div class="col-12 tranding_tittle">

                        <h2>Fashion Post</h2>

                        <a href="news.php?category=fashion">
                            View More
                            <i class="fa fa-arrow-right"></i>
                        </a>

                    </div>


                    <?php foreach ($fashionPosts as $post) { ?>

                        <div class="col-md-6">

                            <div class="tranding_post">

                                <a
                                    href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                    class="post_img">

                                    <img
                                        src="<?php echo htmlspecialchars($post['image']); ?>"
                                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                                    >

                                    <span class="tag_btn">
                                        <?php
                                        echo htmlspecialchars($post['category_name']);
                                        ?>
                                    </span>

                                </a>


                                <div class="post_content">

                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                        class="t_heding">

                                        <?php
                                        echo htmlspecialchars($post['title']);
                                        ?>

                                    </a>


                                    <h6>

                                        <?php
                                        echo date(
                                            'M d, Y',
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

                                </div>

                            </div>

                        </div>

                    <?php } ?>


                </div>


                <!-- =================================================
                     BUSINESS POSTS
                ================================================== -->

                <div class="row tranding_post_area">

                    <div class="col-12 tranding_tittle">

                        <h2>Business Post</h2>

                        <a href="news.php?category=business">
                            View More
                            <i class="fa fa-arrow-right"></i>
                        </a>

                    </div>


                    <?php foreach ($businessPosts as $index => $post) { ?>

                        <div class="col-md-6">

                            <div class="tranding_post">

                                <?php if (!empty($post['image'])) { ?>

                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                        class="post_img">

                                        <img
                                            src="<?php echo htmlspecialchars($post['image']); ?>"
                                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                                        >

                                        <span class="tag_btn">
                                            <?php
                                            echo htmlspecialchars(
                                                $post['category_name']
                                            );
                                            ?>
                                        </span>

                                    </a>

                                <?php } else { ?>

                                    <div class="post_bg">

                                        <a
                                            href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                            class="t_heding">

                                            <?php
                                            echo htmlspecialchars($post['title']);
                                            ?>

                                        </a>

                                        <h6>

                                            <?php
                                            echo date(
                                                'M d, Y',
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

                                        <span class="tag_btn">
                                            <?php
                                            echo htmlspecialchars(
                                                $post['category_name']
                                            );
                                            ?>
                                        </span>

                                    </div>

                                <?php } ?>


                                <?php if (!empty($post['image'])) { ?>

                                    <div class="post_content">

                                        <a
                                            href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                            class="t_heding">

                                            <?php
                                            echo htmlspecialchars($post['title']);
                                            ?>

                                        </a>

                                        <h6>

                                            <?php
                                            echo date(
                                                'M d, Y',
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

                                    </div>

                                <?php } ?>

                            </div>

                        </div>

                    <?php } ?>


                </div>


                <!-- =================================================
                     WATCH IT AREA
                ================================================== -->

                <div class="row watch_it_area">


                    <!-- Font Load Post -->

                    <div class="col-12">

                        <div class="font_load_post flp_2">

                            <img
                                src="images/post/font-load-1.jpg"
                                alt=""
                            >

                            <div class="post_caption">

                                <a
                                    href="news.php?category=travel"
                                    class="tag_btn">

                                    Travel

                                </a>

                                <?php if (!empty($travelPosts[0])) { ?>

                                    <a
                                        href="news-details.php?id=<?php echo (int)$travelPosts[0]['id']; ?>"
                                        class="heding">

                                        <?php
                                        echo htmlspecialchars(
                                            $travelPosts[0]['title']
                                        );
                                        ?>

                                    </a>

                                <?php } ?>

                            </div>

                        </div>

                    </div>


                    <!-- Ethereum style article -->

                    <div class="col-12">

                        <div class="ethereum_price_posts">

                            <a
                                href="news.php?category=travel"
                                class="tag_btn">

                                Travel

                            </a>


                            <?php if (!empty($travelPosts[1])) { ?>

                                <a
                                    href="news-details.php?id=<?php echo (int)$travelPosts[1]['id']; ?>"
                                    class="ep_heding">

                                    <?php
                                    echo htmlspecialchars(
                                        $travelPosts[1]['title']
                                    );
                                    ?>

                                </a>


                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $travelPosts[1]['excerpt'] ?? ''
                                    );
                                    ?>

                                </p>


                                <img
                                    src="<?php echo htmlspecialchars(
                                        $travelPosts[1]['image']
                                    ); ?>"
                                    alt="">

                            <?php } ?>

                        </div>

                    </div>


                    <!-- Subscribe -->

                    <div class="col-12">

                        <div class="subscribe_area">

                            <form
                                method="POST"
                                action="subscribe.php">

                                <div class="input-group">

                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Email address"
                                        required
                                    >

                                    <div class="input-group-prepend">

                                        <button
                                            type="submit"
                                            class="input-group-text"
                                            style="border:none;background:transparent;">

                                            Subscribe

                                        </button>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>


                    <!-- Watch It Heading -->

                    <div class="col-12">

                        <div class="feature_tittle">

                            <h2>Watch It</h2>

                            <a href="video-details.php">

                                View More
                                <i class="fa fa-arrow-right"></i>

                            </a>

                        </div>

                    </div>


                    <!-- Videos -->

                    <?php foreach ($videos as $video) { ?>

                        <div class="col-md-6">

                            <div class="watch_video">

                                <a
                                    href="video-details.php?id=<?php echo (int)$video['id']; ?>"
                                    class="video_thumbnail">

                                    <img
                                        src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                        alt="<?php echo htmlspecialchars($video['title']); ?>"
                                    >

                                    <span class="play_btn">

                                        <i class="flaticon-play-button">

                                            <?php
                                            echo htmlspecialchars(
                                                $video['duration'] ?? ''
                                            );
                                            ?>

                                        </i>

                                    </span>

                                </a>


                                <a
                                    href="video-details.php?id=<?php echo (int)$video['id']; ?>"
                                    class="video_heding">

                                    <?php
                                    echo htmlspecialchars($video['title']);
                                    ?>

                                </a>

                            </div>

                        </div>

                    <?php } ?>


                </div>


                <!-- =================================================
                     TRAVEL POSTS
                ================================================== -->

                <div class="row feature_post_area">

                    <div class="col-12">

                        <div class="feature_tittle">

                            <h2>Travel Post</h2>

                            <a href="news-2.php?category=travel">

                                View More
                                <i class="fa fa-arrow-right"></i>

                            </a>

                        </div>

                    </div>


                    <?php foreach ($travelPosts as $index => $post) { ?>

                        <div class="col-12">

                            <div
                                class="media feature_post <?php echo ($index == count($travelPosts) - 1) ? 'border-0' : ''; ?>">

                                <div class="feture_img">

                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>">

                                        <img
                                            src="<?php echo htmlspecialchars($post['image']); ?>"
                                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                                        >

                                    </a>


                                    <ul class="special_share">

                                        <?php if (!empty($socialLinks['twitter'])) { ?>

                                            <li>
                                                <a
                                                    href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                                                    target="_blank">

                                                    <i class="fa fa-twitter"></i>

                                                </a>
                                            </li>

                                        <?php } ?>


                                        <?php if (!empty($socialLinks['facebook'])) { ?>

                                            <li>
                                                <a
                                                    href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>"
                                                    target="_blank">

                                                    <i class="fa fa-facebook"></i>

                                                </a>
                                            </li>

                                        <?php } ?>


                                        <?php if (!empty($socialLinks['google'])) { ?>

                                            <li>
                                                <a
                                                    href="<?php echo htmlspecialchars($socialLinks['google']); ?>"
                                                    target="_blank">

                                                    <i class="fa fa-google-plus"></i>

                                                </a>
                                            </li>

                                        <?php } ?>

                                    </ul>

                                </div>


                                <div class="media-body feture_content">

                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                        class="f_heding">

                                        <?php
                                        echo htmlspecialchars($post['title']);
                                        ?>

                                    </a>


                                    <h6>

                                        <?php
                                        echo date(
                                            'M d, Y',
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
                                            $post['excerpt'] ?? ''
                                        );
                                        ?>

                                    </p>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                </div>


                <!-- =================================================
                     LATEST ARTICLE
                ================================================== -->

                <div class="row tranding_post_area fashion_post">

                    <div class="col-12">

                        <div class="font_load_post m-0">

                            <img
                                src="images/post/font-load-3.jpg"
                                alt="">

                            <div class="post_caption">

                                <a
                                    href="news.php?category=travel"
                                    class="tag_btn">

                                    Travel

                                </a>


                                <?php if (!empty($travelPosts[2])) { ?>

                                    <a
                                        href="news-details.php?id=<?php echo (int)$travelPosts[2]['id']; ?>"
                                        class="heding heding_bg">

                                        <?php
                                        echo htmlspecialchars(
                                            $travelPosts[2]['title']
                                        );
                                        ?>

                                    </a>

                                <?php } ?>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Magazine -->

                <ul class="row magazine">

                    <?php

                    foreach (array_slice($trendingPosts, 0, 2) as $post) {

                        ?>

                        <li class="col-sm-6">

                            <a
                                href="news-details.php?id=<?php echo (int)$post['id']; ?>">

                                <?php
                                echo htmlspecialchars($post['title']);
                                ?>

                            </a>

                        </li>

                    <?php } ?>

                </ul>


            </div>


            <!-- =========================================================
                 RIGHT SIDEBAR
            ========================================================= -->

            <div class="col-lg-4 right_sidebar">


                <!-- Bitcoin Widget -->

                <div class="btc_accordion">

                    <div class="item">

                        <a
                            href="#"
                            data-toggle="collapse"
                            data-target="#collapseOne">

                            Bitcoin(24h)

                        </a>

                        <div
                            id="collapseOne"
                            class="collapse show"
                            data-parent=".btc_accordion">

                            <div class="accordion_content">

                                <h2>$15212.35</h2>

                                <h4>
                                    EUR: €12632.32
                                    <br>
                                    GBP: £11213.76
                                </h4>

                                <ul class="btc_list">

                                    <li>$15212</li>
                                    <li>$14212</li>
                                    <li>$13212</li>

                                </ul>

                            </div>

                        </div>

                    </div>


                    <div class="item">

                        <a
                            href="#"
                            data-toggle="collapse"
                            class="collapsed"
                            data-target="#collapseTwo">

                            Etherume
                            <span>$740.34</span>

                        </a>

                        <div
                            id="collapseTwo"
                            class="collapse"
                            data-parent=".btc_accordion">

                            <div class="accordion_content">

                                <h2>$15212.35</h2>

                                <h4>
                                    EUR: €12632.32
                                    <br>
                                    GBP: £11213.76
                                </h4>

                                <ul class="btc_list">

                                    <li>$15212</li>
                                    <li>$14212</li>
                                    <li>$13212</li>

                                </ul>

                            </div>

                        </div>

                    </div>


                    <div class="item">

                        <a
                            href="#"
                            class="collapsed"
                            data-toggle="collapse"
                            data-target="#collapseThree">

                            Zcash
                            <span>$546.83</span>

                        </a>

                        <div
                            id="collapseThree"
                            class="collapse"
                            data-parent=".btc_accordion">

                            <div class="accordion_content">

                                <h2>$15212.35</h2>

                                <h4>
                                    EUR: €12632.32
                                    <br>
                                    GBP: £11213.76
                                </h4>

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

                        <div
                            class="widget <?php echo ($index == count($trendingPosts) - 1) ? 'border-0' : ''; ?>">

                            <a
                                href="news-details.php?id=<?php echo (int)$post['id']; ?>">

                                <img
                                    src="<?php echo htmlspecialchars($post['image']); ?>"
                                    alt="<?php echo htmlspecialchars($post['title']); ?>">

                            </a>


                            <a
                                href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                class="w_heding">

                                <?php
                                echo htmlspecialchars($post['title']);
                                ?>

                            </a>

                        </div>

                    <?php } ?>


                    <a
                        href="news.php"
                        class="load_more_btn">

                        Load more..

                    </a>

                </div>


                <!-- Text Widget -->

                <div class="text_widget_area">

                    <?php foreach ($textWidgets as $index => $post) { ?>

                        <div
                            class="text_widget <?php echo ($index == count($textWidgets) - 1) ? 'm-0' : ''; ?>">

                            <a
                                href="news-details.php?id=<?php echo (int)$post['id']; ?>">

                                <?php
                                echo htmlspecialchars($post['title']);
                                ?>

                            </a>

                            <h6>

                                <?php
                                echo date(
                                    'H:i',
                                    strtotime($post['created_at'])
                                );
                                ?>

                                , Press Releases

                            </h6>

                        </div>

                    <?php } ?>

                </div>


                <!-- Advertisement -->

                <?php if (!empty($advertisement)) { ?>

                    <div class="advertisement">

                        <a
                            href="<?php echo htmlspecialchars(
                                $advertisement['link'] ?? '#'
                            ); ?>">

                            <img
                                src="<?php echo htmlspecialchars(
                                    $advertisement['image']
                                ); ?>"
                                alt="Advertisement">

                        </a>

                    </div>

                <?php } ?>


                <!-- Video Widget -->

                <?php if (!empty($videos[0])) { ?>

                    <div class="video_widget">

                        <div class="watch_video">

                            <a
                                href="video-details.php?id=<?php echo (int)$videos[0]['id']; ?>"
                                class="video_thumbnail">

                                <img
                                    src="<?php echo htmlspecialchars(
                                        $videos[0]['thumbnail']
                                    ); ?>"
                                    alt="<?php echo htmlspecialchars(
                                        $videos[0]['title']
                                    ); ?>">

                                <span class="play_btn">

                                    <i class="flaticon-play-button">

                                        <?php
                                        echo htmlspecialchars(
                                            $videos[0]['duration'] ?? ''
                                        );
                                        ?>

                                    </i>

                                </span>

                            </a>


                            <a
                                href="video-details.php?id=<?php echo (int)$videos[0]['id']; ?>"
                                class="video_heding">

                                <?php
                                echo htmlspecialchars(
                                    $videos[0]['title']
                                );
                                ?>

                            </a>

                        </div>

                    </div>

                <?php } ?>


                <!-- Second Advertisement -->

                <?php if (!empty($advertisementSecond)) { ?>

                    <div class="advertisement_2">

                        <a
                            href="<?php echo htmlspecialchars(
                                $advertisementSecond['link'] ?? '#'
                            ); ?>">

                            <img
                                src="<?php echo htmlspecialchars(
                                    $advertisementSecond['image']
                                ); ?>"
                                alt="Advertisement">

                        </a>

                    </div>

                <?php } ?>


                <!-- Social Widget -->

                <ul class="social_widget">

                    <?php if (!empty($socialLinks['facebook'])) { ?>

                        <li>
                            <a
                                href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>"
                                target="_blank">

                                <i class="fa fa-facebook"></i>

                            </a>
                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['twitter'])) { ?>

                        <li>
                            <a
                                href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                                class="twitter"
                                target="_blank">

                                <i class="fa fa-twitter"></i>

                            </a>
                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['pinterest'])) { ?>

                        <li>
                            <a
                                href="<?php echo htmlspecialchars($socialLinks['pinterest']); ?>"
                                class="pinterest"
                                target="_blank">

                                <i class="fa fa-pinterest"></i>

                            </a>
                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['linkedin'])) { ?>

                        <li>
                            <a
                                href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                                class="linkedin"
                                target="_blank">

                                <i class="fa fa-linkedin"></i>

                            </a>
                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['google'])) { ?>

                        <li>
                            <a
                                href="<?php echo htmlspecialchars($socialLinks['google']); ?>"
                                class="google"
                                target="_blank">

                                <i class="fa fa-google-plus"></i>

                            </a>
                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['instagram'])) { ?>

                        <li>
                            <a
                                href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>"
                                class="instagram"
                                target="_blank">

                                <i class="fa fa-instagram"></i>

                            </a>
                        </li>

                    <?php } ?>

                </ul>


                <!-- Tabs Widget -->

                <div class="tabs_widge">

                    <div
                        class="nav nav-tabs"
                        id="nav-tab"
                        role="tablist">

                        <a
                            class="active"
                            id="nav-home-tab"
                            data-toggle="tab"
                            href="#technology"
                            role="tab">

                            Technology

                        </a>


                        <a
                            id="nav-profile-tab"
                            data-toggle="tab"
                            href="#gaming"
                            role="tab">

                            Gaming

                        </a>

                    </div>


                    <div
                        class="tab-content"
                        id="nav-tabContent">


                        <!-- Technology -->

                        <div
                            class="tab-pane fade show active text_widget_area"
                            id="technology"
                            role="tabpanel">

                            <?php foreach ($technologyPosts as $index => $post) { ?>

                                <div
                                    class="text_widget <?php echo ($index == count($technologyPosts) - 1) ? 'm-0' : ''; ?>">

                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>">

                                        <?php
                                        echo htmlspecialchars($post['title']);
                                        ?>

                                    </a>


                                    <h6>

                                        <?php
                                        echo date(
                                            'H:i',
                                            strtotime($post['created_at'])
                                        );
                                        ?>

                                        , Press Releases

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
                            role="tabpanel">

                            <?php foreach ($gamingPosts as $index => $post) { ?>

                                <div
                                    class="text_widget <?php echo ($index == count($gamingPosts) - 1) ? 'm-0' : ''; ?>">

                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>">

                                        <?php
                                        echo htmlspecialchars($post['title']);
                                        ?>

                                    </a>


                                    <h6>

                                        <?php
                                        echo date(
                                            'H:i',
                                            strtotime($post['created_at'])
                                        );
                                        ?>

                                        , Press Releases

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


<!-- =========================================================
     CONNECT WITH US
========================================================= -->

<section class="connect_with_us">

    <div class="container">

        <h2>Connect with us</h2>

        <ul class="contact_with_socail">

            <?php if (!empty($socialLinks['google'])) { ?>

                <li>
                    <a
                        href="<?php echo htmlspecialchars($socialLinks['google']); ?>"
                        target="_blank">

                        <i class="fa fa-google-plus"></i>

                    </a>
                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['linkedin'])) { ?>

                <li>
                    <a
                        href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                        target="_blank">

                        <i class="fa fa-linkedin"></i>

                    </a>
                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['instagram'])) { ?>

                <li>
                    <a
                        href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>"
                        target="_blank">

                        <i class="fa fa-instagram"></i>

                    </a>
                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['twitter'])) { ?>

                <li>
                    <a
                        href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                        target="_blank">

                        <i class="fa fa-twitter"></i>

                    </a>
                </li>

            <?php } ?>


            <?php if (!empty($socialLinks['youtube'])) { ?>

                <li>
                    <a
                        href="<?php echo htmlspecialchars($socialLinks['youtube']); ?>"
                        target="_blank">

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
     SCRIPTS
========================================================= -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<div class="preloader"></div>


<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="js/theme.js"></script>


</body>
</html>