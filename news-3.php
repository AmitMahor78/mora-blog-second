<?php

require_once __DIR__ . '/includes/maintenance-check.php';

include "includes/db.php";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$postsPerPage = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $postsPerPage;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

/* =========================================================
   TOTAL POSTS
========================================================= */

$totalQuery = "
    SELECT COUNT(*) AS total
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
";

if ($selectedCategory !== '') {

    $categorySafe = mysqli_real_escape_string($conn, $selectedCategory);

    $totalQuery .= "
        AND c.slug = '$categorySafe'
    ";
}

if ($search !== '') {

    $searchSafe = mysqli_real_escape_string($conn, $search);

    $totalQuery .= "
        AND (
            p.title LIKE '%$searchSafe%'
            OR p.excerpt LIKE '%$searchSafe%'
            OR p.content LIKE '%$searchSafe%'
        )
    ";
}

$totalResult = mysqli_query($conn, $totalQuery);

$totalPosts = 0;

if ($totalResult) {
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalPosts = (int)$totalRow['total'];
}

$totalPages = ceil($totalPosts / $postsPerPage);


/* =========================================================
   MAIN POSTS
========================================================= */

$postsQuery = "
    SELECT
        p.id,
        p.title,
        p.excerpt,
        p.content,
        p.image,
        p.created_at,
        c.name AS category_name,
        c.slug AS category_slug,
        a.name AS author_name
    FROM posts p
    LEFT JOIN categories c
        ON p.category_id = c.id
    LEFT JOIN users a
        ON p.author_id = a.id
    WHERE p.status = 'published'
";

if ($selectedCategory !== '') {

    $categorySafe = mysqli_real_escape_string($conn, $selectedCategory);

    $postsQuery .= "
        AND c.slug = '$categorySafe'
    ";
}

if ($search !== '') {

    $searchSafe = mysqli_real_escape_string($conn, $search);

    $postsQuery .= "
        AND (
            p.title LIKE '%$searchSafe%'
            OR p.excerpt LIKE '%$searchSafe%'
            OR p.content LIKE '%$searchSafe%'
        )
    ";
}

$postsQuery .= "
    ORDER BY p.created_at DESC
    LIMIT $offset, $postsPerPage
";

$postsResult = mysqli_query($conn, $postsQuery);


/* =========================================================
   CATEGORIES
========================================================= */

$categoriesQuery = "
    SELECT
        c.id,
        c.name,
        c.slug,
        COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p
        ON p.category_id = c.id
        AND p.status = 'published'
    GROUP BY c.id, c.name, c.slug
    ORDER BY c.name ASC
";

$categoriesResult = mysqli_query($conn, $categoriesQuery);


/* =========================================================
   ALL NEWS COUNT
========================================================= */

$allNewsQuery = "
    SELECT COUNT(*) AS total
    FROM posts
    WHERE status = 'published'
";

$allNewsResult = mysqli_query($conn, $allNewsQuery);

$allNewsCount = 0;

if ($allNewsResult) {
    $allNewsRow = mysqli_fetch_assoc($allNewsResult);
    $allNewsCount = (int)$allNewsRow['total'];
}


/* =========================================================
   TRENDING NEWS
========================================================= */

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


/* =========================================================
   TEXT WIDGET
========================================================= */

$textWidgetQuery = "
    SELECT
        p.id,
        p.title,
        p.created_at,
        c.name AS category_name
    FROM posts p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 4
";

$textWidgetResult = mysqli_query($conn, $textWidgetQuery);


/* =========================================================
   ADVERTISEMENT
========================================================= */

$advertisementQuery = "
    SELECT
        id,
        title,
        image,
        link
    FROM advertisements
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1
";

$advertisementResult = mysqli_query($conn, $advertisementQuery);


/* =========================================================
   VIDEO WIDGET
========================================================= */

$videoWidgetQuery = "
    SELECT
        id,
        title,
        thumbnail,
        video_url
    FROM videos
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1
";

$videoWidgetResult = mysqli_query($conn, $videoWidgetQuery);


/* =========================================================
   SOCIAL LINKS
========================================================= */

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


/* =========================================================
   TECHNOLOGY POSTS
========================================================= */

$technologyQuery = "
    SELECT
        p.id,
        p.title,
        p.created_at,
        c.name AS category_name
    FROM posts p
    INNER JOIN categories c
        ON p.category_id = c.id
    WHERE p.status = 'published'
      AND c.slug = 'technology'
    ORDER BY p.created_at DESC
    LIMIT 4
";

$technologyResult = mysqli_query($conn, $technologyQuery);


/* =========================================================
   GAMING POSTS
========================================================= */

$gamingQuery = "
    SELECT
        p.id,
        p.title,
        p.created_at,
        c.name AS category_name
    FROM posts p
    INNER JOIN categories c
        ON p.category_id = c.id
    WHERE p.status = 'published'
      AND c.slug = 'gaming'
    ORDER BY p.created_at DESC
    LIMIT 4
";

$gamingResult = mysqli_query($conn, $gamingQuery);


/* =========================================================
   PAGINATION URL
========================================================= */

function pageUrl($pageNumber)
{
    $params = [];

    if (!empty($_GET['search'])) {
        $params['search'] = $_GET['search'];
    }

    if (!empty($_GET['category'])) {
        $params['category'] = $_GET['category'];
    }

    $params['page'] = $pageNumber;

    return 'news-3.php?' . http_build_query($params);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="images/favicon.png" type="image/x-icon" />

    <title>Mora Blog | Html Template</title>

    <link href="css/style.css" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

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
     PAGE HEADER
========================================================= -->

<div class="container">

    <div class="pages_heder">

        <h2>Blog List 3</h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="news.php">Blog</a>
            </li>

            <li>
                <a href="news-3.php" class="active">
                    Blog v3
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
                 LEFT SIDEBAR / POSTS
            ================================================== -->

            <div class="col-lg-8 left_sidebar ls_2">

                <div class="row feature_post_area">


                    <!-- FEATURE TITLE -->

                    <div class="col-12">

                        <div class="feature_tittle">

                            <h2>

                                <?php

                                if ($selectedCategory !== '') {

                                    echo htmlspecialchars(
                                        ucfirst($selectedCategory)
                                    );

                                } else {

                                    echo "Travel Post";

                                }

                                ?>

                            </h2>

                            <div class="post_select_button">

                                <select
                                    class="post_select"
                                    onchange="if(this.value) window.location.href=this.value;"
                                >

                                    <option value="">
                                        Select Category
                                    </option>

                                    <?php

                                    if ($categoriesResult) {

                                        while ($category = mysqli_fetch_assoc($categoriesResult)) {

                                            $categoryUrl =
                                                'news-3.php?category=' .
                                                urlencode($category['slug']);

                                    ?>

                                        <option
                                            value="<?php echo htmlspecialchars($categoryUrl); ?>"
                                            <?php
                                            echo (
                                                $selectedCategory === $category['slug']
                                            )
                                            ? 'selected'
                                            : '';
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $category['name']
                                            );
                                            ?>

                                        </option>

                                    <?php

                                        }
                                    }

                                    ?>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         DYNAMIC POSTS
                    ================================================== -->

                    <?php

                    if ($postsResult && mysqli_num_rows($postsResult) > 0) {

                        while ($post = mysqli_fetch_assoc($postsResult)) {

                    ?>

                        <div class="col-12">

                            <div class="belarus_items">


                                <!-- POST IMAGE -->

                                <?php if (!empty($post['image'])) { ?>

                                    <img
                                        src="<?php echo htmlspecialchars($post['image']); ?>"
                                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                                    >

                                <?php } ?>


                                <!-- SHARE -->

                                <ul class="special_share">

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

                                </ul>


                                <!-- POST CONTENT -->

                                <div class="belarus_content">

                                    <h6 class="wow fadeInUp">

                                        <?php

                                        echo date(
                                            "F d, Y",
                                            strtotime($post['created_at'])
                                        );

                                        ?>

                                    </h6>


                                    <a
                                        href="news-details.php?id=<?php echo (int)$post['id']; ?>"
                                        class="heding wow fadeInUp"
                                        data-wow-delay="0.2s"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $post['title']
                                        );
                                        ?>

                                    </a>


                                    <?php if (!empty($post['category_name'])) { ?>

                                        <a
                                            href="news-3.php?category=<?php echo urlencode($post['category_slug']); ?>"
                                            class="wow fadeInUp tag_btn"
                                            data-wow-delay="0.4s"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $post['category_name']
                                            );
                                            ?>

                                        </a>

                                    <?php } ?>

                                </div>

                            </div>

                        </div>

                    <?php

                        }

                    } else {

                    ?>

                        <div class="col-12">

                            <div class="belarus_items">

                                <div class="belarus_content">

                                    <h6>No Posts Found</h6>

                                    <p>
                                        No published posts are available for the selected category or search.
                                    </p>

                                </div>

                            </div>

                        </div>

                    <?php

                    }

                    ?>


                    <!-- =================================================
                         PAGINATION
                    ================================================== -->

                    <?php if ($totalPages > 1) { ?>

                        <div class="col-12">

                            <nav class="pagination_area pa_3">

                                <ul class="pagination">


                                    <!-- PREVIOUS -->

                                    <?php if ($page > 1) { ?>

                                        <li class="page-item">

                                            <a
                                                class="page-link"
                                                href="<?php echo pageUrl($page - 1); ?>"
                                            >

                                                <i class="fa fa-arrow-left"></i>

                                            </a>

                                        </li>

                                    <?php } ?>


                                    <!-- PAGE NUMBERS -->

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
                                                class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>"
                                                href="<?php echo pageUrl($i); ?>"
                                            >

                                                <?php echo $i; ?>

                                            </a>

                                        </li>

                                    <?php

                                    }

                                    ?>


                                    <!-- NEXT -->

                                    <?php if ($page < $totalPages) { ?>

                                        <li class="page-item">

                                            <a
                                                class="page-link"
                                                href="<?php echo pageUrl($page + 1); ?>"
                                            >

                                                <i class="fa fa-arrow-right"></i>

                                            </a>

                                        </li>

                                    <?php } ?>


                                </ul>

                            </nav>

                        </div>

                    <?php } ?>


                </div>

            </div>


            <!-- =================================================
                 RIGHT SIDEBAR
            ================================================== -->

            <div class="col-lg-4 right_sidebar">


                <!-- SEARCH -->

                <form action="news-3.php" method="GET">

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
                                style="border:none;"
                            >

                                <i class="fa fa-search"></i>

                            </button>

                        </div>

                    </div>

                </form>


                <!-- =================================================
                     CATEGORIES
                ================================================== -->

                <div class="categories">

                    <h3>Post Categories</h3>

                    <ul class="cpost_categories">

                        <li>

                            <a
                                href="news-3.php"
                                class="<?php echo ($selectedCategory === '') ? 'active' : ''; ?>"
                            >

                                All News

                                <span>
                                    <?php echo $allNewsCount; ?>
                                </span>

                            </a>

                        </li>


                        <?php

                        $categoriesResult = mysqli_query(
                            $conn,
                            $categoriesQuery
                        );

                        if ($categoriesResult) {

                            while ($category = mysqli_fetch_assoc($categoriesResult)) {

                        ?>

                            <li>

                                <a
                                    href="news-3.php?category=<?php echo urlencode($category['slug']); ?>"
                                    class="<?php echo ($selectedCategory === $category['slug']) ? 'active' : ''; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category['name']
                                    );
                                    ?>

                                    <span>
                                        <?php
                                        echo (int)$category['post_count'];
                                        ?>
                                    </span>

                                </a>

                            </li>

                        <?php

                            }
                        }

                        ?>

                    </ul>

                </div>


                <!-- =================================================
                     TRENDING NEWS
                ================================================== -->

                <div class="latest_news_widget p-0">

                    <h2>Trending News</h2>


                    <?php

                    if (
                        $trendingResult &&
                        mysqli_num_rows($trendingResult) > 0
                    ) {

                        while ($trending = mysqli_fetch_assoc($trendingResult)) {

                    ?>

                        <div class="widget">

                            <a
                                href="news-details.php?id=<?php echo (int)$trending['id']; ?>"
                            >

                                <?php if (!empty($trending['image'])) { ?>

                                    <img
                                        src="<?php echo htmlspecialchars($trending['image']); ?>"
                                        alt="<?php echo htmlspecialchars($trending['title']); ?>"
                                    >

                                <?php } ?>

                            </a>


                            <a
                                href="news-details.php?id=<?php echo (int)$trending['id']; ?>"
                                class="w_heding"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $trending['title']
                                );
                                ?>

                            </a>

                        </div>

                    <?php

                        }

                    }

                    ?>


                    <a
                        href="news.php"
                        class="load_more_btn"
                    >
                        Load more..
                    </a>

                </div>


                <!-- =================================================
                     TEXT WIDGET
                ================================================== -->

                <div class="text_widget_area">

                    <?php

                    if (
                        $textWidgetResult &&
                        mysqli_num_rows($textWidgetResult) > 0
                    ) {

                        $textCount = 0;

                        while ($textPost = mysqli_fetch_assoc($textWidgetResult)) {

                            $textCount++;

                            $extraClass =
                                ($textCount == 4)
                                ? 'm-0'
                                : '';

                    ?>

                        <div class="text_widget <?php echo $extraClass; ?>">

                            <a
                                href="news-details.php?id=<?php echo (int)$textPost['id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $textPost['title']
                                );
                                ?>

                            </a>

                            <h6
                                class="<?php echo ($textCount == 4) ? 'padding-bottom-0' : ''; ?>"
                            >

                                <?php
                                echo date(
                                    "H:i",
                                    strtotime($textPost['created_at'])
                                );
                                ?>,

                                <?php
                                echo htmlspecialchars(
                                    $textPost['category_name'] ?? 'News'
                                );
                                ?>

                            </h6>

                        </div>

                    <?php

                        }

                    }

                    ?>

                </div>


                <!-- =================================================
                     ADVERTISEMENT
                ================================================== -->

                <?php

                if (
                    $advertisementResult &&
                    mysqli_num_rows($advertisementResult) > 0
                ) {

                    $advertisement = mysqli_fetch_assoc(
                        $advertisementResult
                    );

                ?>

                    <div class="advertisement">

                        <a
                            href="<?php echo htmlspecialchars($advertisement['link'] ?? '#'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            <img
                                src="<?php echo htmlspecialchars($advertisement['image']); ?>"
                                alt="<?php echo htmlspecialchars($advertisement['title'] ?? 'Advertisement'); ?>"
                            >

                        </a>

                    </div>

                <?php } ?>


                <!-- =================================================
                     VIDEO WIDGET
                ================================================== -->

                <?php

                if (
                    $videoWidgetResult &&
                    mysqli_num_rows($videoWidgetResult) > 0
                ) {

                    $video = mysqli_fetch_assoc(
                        $videoWidgetResult
                    );

                ?>

                    <div class="video_widget">

                        <div class="watch_video">

                            <a
                                href="video-details.php?id=<?php echo (int)$video['id']; ?>"
                                class="video_thumbnail"
                            >

                                <img
                                    src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                    alt="<?php echo htmlspecialchars($video['title']); ?>"
                                >

                                <span class="play_btn">

                                    <i class="flaticon-play-button"></i>

                                </span>

                            </a>


                            <a
                                href="video-details.php?id=<?php echo (int)$video['id']; ?>"
                                class="video_heding"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $video['title']
                                );
                                ?>

                            </a>

                        </div>

                    </div>

                <?php } ?>


                <!-- =================================================
                     SOCIAL WIDGET
                ================================================== -->

                <ul class="social_widget">


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


                    <?php if (!empty($socialLinks['twitter'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                                class="twitter"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa fa-twitter"></i>

                            </a>

                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['pinterest'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['pinterest']); ?>"
                                class="pinterest"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa fa-pinterest"></i>

                            </a>

                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['linkedin'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                                class="linkedin"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa fa-linkedin"></i>

                            </a>

                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['google'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['google']); ?>"
                                class="google"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa fa-google-plus"></i>

                            </a>

                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['instagram'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>"
                                class="instagram"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa fa-instagram"></i>

                            </a>

                        </li>

                    <?php } ?>


                </ul>


                <!-- =================================================
                     TECHNOLOGY / GAMING TABS
                ================================================== -->

                <div class="tabs_widge">


                    <div
                        class="nav nav-tabs"
                        id="nav-tab"
                        role="tablist"
                    >

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


                    <div
                        class="tab-content"
                        id="nav-tabContent"
                    >


                        <!-- TECHNOLOGY -->

                        <div
                            class="tab-pane fade show active text_widget_area"
                            id="technology"
                            role="tabpanel"
                        >

                            <?php

                            if (
                                $technologyResult &&
                                mysqli_num_rows($technologyResult) > 0
                            ) {

                                $techCount = 0;

                                while (
                                    $techPost =
                                    mysqli_fetch_assoc($technologyResult)
                                ) {

                                    $techCount++;

                                    $extraClass =
                                        ($techCount == 4)
                                        ? 'm-0'
                                        : '';

                            ?>

                                <div
                                    class="text_widget <?php echo $extraClass; ?>"
                                >

                                    <a
                                        href="news-details.php?id=<?php echo (int)$techPost['id']; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $techPost['title']
                                        );
                                        ?>

                                    </a>

                                    <h6
                                        class="<?php echo ($techCount == 4) ? 'padding-bottom-0' : ''; ?>"
                                    >

                                        <?php
                                        echo date(
                                            "H:i",
                                            strtotime($techPost['created_at'])
                                        );
                                        ?>,

                                        <?php
                                        echo htmlspecialchars(
                                            $techPost['category_name']
                                        );
                                        ?>

                                        <span class="tech">
                                            Technology
                                        </span>

                                    </h6>

                                </div>

                            <?php

                                }

                            } else {

                            ?>

                                <div class="text_widget m-0">

                                    <a href="#">
                                        No Technology posts available
                                    </a>

                                </div>

                            <?php } ?>

                        </div>


                        <!-- GAMING -->

                        <div
                            class="tab-pane fade text_widget_area"
                            id="gaming"
                            role="tabpanel"
                        >

                            <?php

                            if (
                                $gamingResult &&
                                mysqli_num_rows($gamingResult) > 0
                            ) {

                                $gamingCount = 0;

                                while (
                                    $gamingPost =
                                    mysqli_fetch_assoc($gamingResult)
                                ) {

                                    $gamingCount++;

                                    $extraClass =
                                        ($gamingCount == 4)
                                        ? 'm-0'
                                        : '';

                            ?>

                                <div
                                    class="text_widget <?php echo $extraClass; ?>"
                                >

                                    <a
                                        href="news-details.php?id=<?php echo (int)$gamingPost['id']; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $gamingPost['title']
                                        );
                                        ?>

                                    </a>

                                    <h6
                                        class="<?php echo ($gamingCount == 4) ? 'padding-bottom-0' : ''; ?>"
                                    >

                                        <?php
                                        echo date(
                                            "H:i",
                                            strtotime($gamingPost['created_at'])
                                        );
                                        ?>,

                                        <?php
                                        echo htmlspecialchars(
                                            $gamingPost['category_name']
                                        );
                                        ?>

                                        <span class="gaming">
                                            Gaming
                                        </span>

                                    </h6>

                                </div>

                            <?php

                                }

                            } else {

                            ?>

                                <div class="text_widget m-0">

                                    <a href="#">
                                        No Gaming posts available
                                    </a>

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

<script src="vendors/bootstrap-selector/jquery.nice-select.min.js"></script>

<script src="js/theme.js"></script>


</body>

</html>