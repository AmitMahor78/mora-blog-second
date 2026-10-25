<?php

include "includes/db.php";


require_once __DIR__ . '/includes/maintenance-check.php';

// Existing page code continues below.

/* =========================================================
   GET VIDEO ID
========================================================= */

$videoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;


/* =========================================================
   FETCH VIDEO
========================================================= */

$video = null;

if ($videoId > 0) {

    $videoQuery = "
        SELECT
            v.id,
            v.title,
            v.description,
            v.thumbnail,
            v.video_url,
            v.category_id,
            v.author_id,
            v.created_at,

            a.name AS author_name,
            COALESCE(auth.image, 'images/post/author-1.jpg') AS author_image,
            COALESCE(auth.bio, 'Video creator and Mora Blog contributor.') AS author_bio,

            c.name AS category_name,
            c.slug AS category_slug

        FROM videos v

        LEFT JOIN users a
            ON v.author_id = a.id

        LEFT JOIN authors auth
            ON auth.id = a.id

        LEFT JOIN categories c
            ON v.category_id = c.id

        WHERE v.id = ?
        AND v.status = 'active'

        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $videoQuery);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $videoId);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $video = mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================================================
   VIDEO NOT FOUND
========================================================= */

if (!$video) {
    echo "Video not found.";
    echo "<br>Requested Video ID: " . (int)$videoId;

    if ($videoId <= 0) {
        echo "<br>Reason: Invalid or missing video ID.";
    } else {
        echo "<br>Reason: Video may not exist, may be inactive, or the query may have failed.";
    }

    exit;
}


/* =========================================================
   ADD COMMENT
========================================================= */

$commentMessage = "";
$commentMessageType = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['submit_comment'])
) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['message'] ?? '');

    if (
        $name === ''
        || $email === ''
        || $comment === ''
    ) {

        $commentMessage = "Please fill all fields.";
        $commentMessageType = "danger";

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $commentMessage = "Please enter a valid email address.";
        $commentMessageType = "danger";

    } else {

        $insertComment = "
            INSERT INTO comments
            (
                post_id,
                name,
                email,
                comment
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare(
            $conn,
            $insertComment
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "isss",
                $videoId,
                $name,
                $email,
                $comment
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: video-details.php?id="
                    . $videoId
                    . "&comment=success"
                );

                exit;

            } else {

                $commentMessage =
                    "Unable to submit comment.";

                $commentMessageType = "danger";
            }

            mysqli_stmt_close($stmt);

        } else {

            $commentMessage = "Database error.";
            $commentMessageType = "danger";
        }
    }
}


/* =========================================================
   COMMENT SUCCESS MESSAGE
========================================================= */

if (
    isset($_GET['comment'])
    && $_GET['comment'] === 'success'
) {

    $commentMessage =
        "Your comment has been submitted successfully.";

    $commentMessageType = "success";
}


/* =========================================================
   FETCH COMMENTS
========================================================= */

$comments = [];

$commentsQuery = "
    SELECT
        id,
        name,
        email,
        comment,
        created_at

    FROM comments

    WHERE post_id = ?

    ORDER BY created_at DESC
";

$stmt = mysqli_prepare(
    $conn,
    $commentsQuery
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $videoId
    );

    mysqli_stmt_execute($stmt);

    $commentsResult =
        mysqli_stmt_get_result($stmt);

    if ($commentsResult) {

        while (
            $row =
            mysqli_fetch_assoc($commentsResult)
        ) {

            $comments[] = $row;
        }
    }

    mysqli_stmt_close($stmt);
}

$commentCount = count($comments);


/* =========================================================
   TRENDING NEWS
========================================================= */

$trendingPosts = [];

$trendingQuery = "
    SELECT
        id,
        title,
        image,
        created_at

    FROM posts

    WHERE status = 'published'

    ORDER BY created_at DESC

    LIMIT 3
";

$trendingResult =
    mysqli_query(
        $conn,
        $trendingQuery
    );

if ($trendingResult) {

    while (
        $row =
        mysqli_fetch_assoc($trendingResult)
    ) {

        $trendingPosts[] = $row;
    }
}


/* =========================================================
   TEXT WIDGET
========================================================= */

$textPosts = [];

$textQuery = "
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

$textResult =
    mysqli_query(
        $conn,
        $textQuery
    );

if ($textResult) {

    while (
        $row =
        mysqli_fetch_assoc($textResult)
    ) {

        $textPosts[] = $row;
    }
}


/* =========================================================
   RELATED POSTS
========================================================= */

$relatedPosts = [];

if (!empty($video['category_id'])) {

    $relatedQuery = "
        SELECT
            id,
            title,
            image,
            created_at

        FROM posts

        WHERE category_id = ?

        AND status = 'published'

        ORDER BY created_at DESC

        LIMIT 3
    ";

    $stmt = mysqli_prepare(
        $conn,
        $relatedQuery
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $video['category_id']
        );

        mysqli_stmt_execute($stmt);

        $relatedResult =
            mysqli_stmt_get_result($stmt);

        if ($relatedResult) {

            while (
                $row =
                mysqli_fetch_assoc(
                    $relatedResult
                )
            ) {

                $relatedPosts[] = $row;
            }
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================================================
   CATEGORIES
========================================================= */

$categories = [];

$categoryQuery = "
    SELECT
        c.id,
        c.name,
        c.slug,
        COUNT(p.id) AS total_posts

    FROM categories c

    LEFT JOIN posts p
        ON p.category_id = c.id
        AND p.status = 'published'

    GROUP BY
        c.id,
        c.name,
        c.slug

    ORDER BY c.id ASC

    LIMIT 5
";

$categoryResult =
    mysqli_query(
        $conn,
        $categoryQuery
    );

if ($categoryResult) {

    while (
        $row =
        mysqli_fetch_assoc(
            $categoryResult
        )
    ) {

        $categories[] = $row;
    }
}


/* =========================================================
   ADVERTISEMENT
========================================================= */

$advertisement = null;

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

$advertisementResult =
    mysqli_query(
        $conn,
        $advertisementQuery
    );

if ($advertisementResult) {

    $advertisement =
        mysqli_fetch_assoc(
            $advertisementResult
        );
}


/* =========================================================
   SOCIAL LINKS
========================================================= */

$socialLinks = [];

$socialQuery = "
    SELECT
        platform,
        url

    FROM social_links

    WHERE status = 'active'

    ORDER BY id ASC
";

$socialResult =
    mysqli_query(
        $conn,
        $socialQuery
    );

if ($socialResult) {

    while (
        $row =
        mysqli_fetch_assoc(
            $socialResult
        )
    ) {

        $socialLinks[
            $row['platform']
        ] = $row['url'];
    }
}


/* =========================================================
   SITE SETTINGS
========================================================= */

$siteSettings = null;

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

$settingsResult =
    mysqli_query(
        $conn,
        $settingsQuery
    );

if ($settingsResult) {

    $siteSettings =
        mysqli_fetch_assoc(
            $settingsResult
        );
}


/* =========================================================
   DEFAULT VALUES
========================================================= */

$siteName =
    $siteSettings['site_name']
    ?? 'Mora';

$siteDescription =
    $siteSettings['site_description']
    ?? 'Established in 2013 as the first cloud mining provider, Blog has become a multi-functional Blog Categories.';

$location =
    $siteSettings['location']
    ?? '12/4 New Yourk R Block Street 2101 USA';

$workingDays =
    $siteSettings['working_days']
    ?? 'Monday-Saturday';

$closingDay =
    $siteSettings['closing_day']
    ?? 'Sunday';

$copyrightYear =
    $siteSettings['copyright_year']
    ?? date('Y');

$developerName =
    $siteSettings['developer_name']
    ?? 'Themazin';

$developerUrl =
    $siteSettings['developer_url']
    ?? '#';


/* =========================================================
   TOPICS
========================================================= */

$topics = [];

$topicsQuery = "
    SELECT
        id,
        name,
        slug

    FROM categories

    ORDER BY id ASC

    LIMIT 9
";

$topicsResult =
    mysqli_query(
        $conn,
        $topicsQuery
    );

if ($topicsResult) {

    while (
        $row =
        mysqli_fetch_assoc(
            $topicsResult
        )
    ) {

        $topics[] = $row;
    }
}


/* =========================================================
   TECHNOLOGY POSTS
========================================================= */

$technologyPosts = [];

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

$technologyResult =
    mysqli_query(
        $conn,
        $technologyQuery
    );

if ($technologyResult) {

    while (
        $row =
        mysqli_fetch_assoc(
            $technologyResult
        )
    ) {

        $technologyPosts[] = $row;
    }
}


/* =========================================================
   GAMING POSTS
========================================================= */

$gamingPosts = [];

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

$gamingResult =
    mysqli_query(
        $conn,
        $gamingQuery
    );

if ($gamingResult) {

    while (
        $row =
        mysqli_fetch_assoc(
            $gamingResult
        )
    ) {

        $gamingPosts[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <link
        rel="icon"
        href="images/favicon.png"
        type="image/x-icon"
    >

    <title>
        <?php echo htmlspecialchars($video['title']); ?>
        |
        <?php echo htmlspecialchars($siteName); ?>
    </title>

    <link
        href="css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="css/style.css"
        rel="stylesheet"
    >

</head>

<body>


<!-- =====================================================
     HEADER TOP
===================================================== -->
<?php include "includes/header.php"; ?>

<!-- =====================================================
     HEADER / NAVBAR
===================================================== -->
<header class="main_header_area" id="header">
    <div class="container">
        <div class="header_menu">
            <?php include "includes/navbar.php"; ?>
        </div>
    </div>
</header>


<!-- =====================================================
     PAGE HEADER
===================================================== -->

<div class="container">

    <div class="pages_heder">

        <h2>
            <?php echo htmlspecialchars($video['title']); ?>
        </h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>

            <li>
                <a href="news.php">
                    Blog
                </a>
            </li>

            <li>
                Video Details
            </li>

        </ol>

    </div>

</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<section class="post_section news_post">

    <div class="container">

        <div class="row post_section_inner">


            <!-- =================================================
                 MAIN VIDEO
            ================================================== -->

            <div class="col-lg-8">

                <div class="news_left_sidebar">

                    <div class="news_item news_details">


                        <!-- META -->

                        <h6>

                            <span>

                                <?php
                                echo date(
                                    "F d, Y",
                                    strtotime(
                                        $video['created_at']
                                    )
                                );
                                ?>

                            </span>


                            <?php if (!empty($video['author_name'])) { ?>

                                <a href="#author">

                                    Post by :
                                    <?php
                                    echo htmlspecialchars(
                                        $video['author_name']
                                    );
                                    ?>

                                </a>

                            <?php } ?>


                            <?php if (!empty($video['category_name'])) { ?>

                                <a
                                    href="news.php?category=<?php echo urlencode($video['category_slug']); ?>"
                                    class="investing"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $video['category_name']
                                    );
                                    ?>

                                </a>

                            <?php } ?>

                        </h6>


                        <!-- TITLE -->

                        <a
                            href="#"
                            class="news_heding"
                        >

                            <?php
                            echo htmlspecialchars(
                                $video['title']
                            );
                            ?>

                        </a>


                        <!-- VIDEO -->

                        <?php if (!empty($video['video_url'])) { ?>

                            <iframe
                                src="<?php echo htmlspecialchars($video['video_url']); ?>"
                                frameborder="0"
                                allow="autoplay; fullscreen; picture-in-picture"
                                allowfullscreen
                            ></iframe>

                        <?php } elseif (!empty($video['thumbnail'])) { ?>

                            <img
                                src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                alt="<?php echo htmlspecialchars($video['title']); ?>"
                            >

                        <?php } ?>


                        <!-- DESCRIPTION -->

                        <?php if (!empty($video['description'])) { ?>

                            <div class="post_content">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $video['description']
                                    )
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <!-- SHARE -->

                        <div class="share_row row">

                            <div class="col-sm-6 share_area">

                                <ul>

                                    <li>
                                        Share:
                                    </li>

                                    <li>

                                        <a
                                            href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getMoraSiteBaseUrl() . '/video-details.php?id=' . $videoId); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >

                                            <i class="fa fa-facebook"></i>

                                        </a>

                                    </li>

                                    <li>

                                        <a
                                            href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(getMoraSiteBaseUrl() . '/video-details.php?id=' . $videoId); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >

                                            <i class="fa fa-linkedin"></i>

                                        </a>

                                    </li>

                                    <li>

                                        <a
                                            href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getMoraSiteBaseUrl() . '/video-details.php?id=' . $videoId); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >

                                            <i class="fa fa-twitter"></i>

                                        </a>

                                    </li>

                                </ul>

                            </div>


                            <div class="col-sm-6 back_to">

                                <a href="news.php">

                                    <i class="fa fa-arrow-left"></i>

                                    Back to blog

                                </a>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         AUTHOR
                    ================================================== -->

                    <?php if (!empty($video['author_name'])) { ?>

                        <div
                            class="author_area"
                            id="author"
                        >

                            <h2>
                                Author
                            </h2>

                            <div class="media">

                                <div class="media-left">

                                    <?php if (!empty($video['author_image'])) { ?>

                                        <img
                                            src="<?php echo htmlspecialchars($video['author_image']); ?>"
                                            alt="<?php echo htmlspecialchars($video['author_name']); ?>"
                                        >

                                    <?php } ?>

                                    <a href="#">

                                        <?php
                                        echo htmlspecialchars(
                                            $video['author_name']
                                        );
                                        ?>

                                    </a>

                                </div>


                                <div class="media-body">

                                    <h6>

                                        <?php
                                        echo date(
                                            "d M, Y",
                                            strtotime(
                                                $video['created_at']
                                            )
                                        );
                                        ?>

                                    </h6>

                                    <p>

                                        <?php
                                        echo htmlspecialchars(
                                            $video['author_bio'] ?? ''
                                        );
                                        ?>

                                    </p>

                                </div>

                            </div>

                        </div>

                    <?php } ?>


                    <!-- =================================================
                         COMMENTS
                    ================================================== -->

                    <div class="comment_area">

                        <h2>

                            Comment
                            (<?php echo $commentCount; ?>)

                        </h2>


                        <?php if (!empty($comments)) { ?>

                            <?php foreach ($comments as $comment) { ?>

                                <div class="media">

                                    <div class="media-left">

                                        <img
                                            src="images/post/author-2.jpg"
                                            alt="User"
                                            class="rounded-circle"
                                        >

                                        <a href="#">

                                            <?php
                                            echo htmlspecialchars(
                                                $comment['name']
                                            );
                                            ?>

                                        </a>

                                    </div>


                                    <div class="media-body">

                                        <p>

                                            <?php
                                            echo nl2br(
                                                htmlspecialchars(
                                                    $comment['comment']
                                                )
                                            );
                                            ?>

                                        </p>


                                        <h6>

                                            <?php
                                            echo date(
                                                "d M, Y",
                                                strtotime(
                                                    $comment['created_at']
                                                )
                                            );
                                            ?>

                                        </h6>

                                        <a
                                            href="#reply"
                                            class="reply_btn"
                                        >
                                            Reply
                                        </a>

                                    </div>

                                </div>

                            <?php } ?>

                        <?php } else { ?>

                            <p>
                                No comments yet.
                            </p>

                        <?php } ?>

                    </div>


                    <!-- =================================================
                         COMMENT FORM
                    ================================================== -->

                    <div
                        class="leave_reply"
                        id="reply"
                    >

                        <h2>
                            Leave a Reply
                        </h2>


                        <?php if ($commentMessage !== '') { ?>

                            <div
                                class="alert alert-<?php echo htmlspecialchars($commentMessageType); ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $commentMessage
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <form
                            class="row login_from"
                            method="POST"
                            action="video-details.php?id=<?php echo $videoId; ?>"
                        >

                            <div class="form-group col-12">

                                <textarea
                                    class="form-control"
                                    name="message"
                                    placeholder="Your Comment"
                                    required
                                ></textarea>

                            </div>


                            <div class="form-group col-12">

                                <input
                                    type="text"
                                    class="form-control"
                                    name="name"
                                    placeholder="Name"
                                    required
                                >

                            </div>


                            <div class="form-group col-12">

                                <input
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    placeholder="Email"
                                    required
                                >

                            </div>


                            <div class="form-group larg_btn col-12">

                                <button
                                    class="sm_btn"
                                    type="submit"
                                    name="submit_comment"
                                >

                                    Submit Reply

                                    <i class="fa fa-arrow-right"></i>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 RIGHT SIDEBAR
            ================================================== -->

            <div class="col-lg-4 right_sidebar">


                <!-- SEARCH -->

                <form
                    action="news.php"
                    method="GET"
                    class="input-group"
                >

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search"
                    >

                    <div class="input-group-append">

                        <button
                            type="submit"
                            class="input-group-text"
                        >

                            <i class="fa fa-search"></i>

                        </button>

                    </div>

                </form>


                <!-- CATEGORIES -->

                <div class="categories">

                    <h3>
                        Post Categories
                    </h3>

                    <ul class="cpost_categories">

                        <?php foreach ($categories as $category) { ?>

                            <li>

                                <a
                                    href="news.php?category=<?php echo urlencode($category['slug']); ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category['name']
                                    );
                                    ?>

                                    <span>

                                        <?php
                                        echo str_pad(
                                            $category['total_posts'],
                                            2,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                        ?>

                                    </span>

                                </a>

                            </li>

                        <?php } ?>

                    </ul>

                </div>


                <!-- TRENDING NEWS -->

                <div class="latest_news_widget">

                    <h2>
                        Trending News
                    </h2>


                    <?php foreach ($trendingPosts as $post) { ?>

                        <div class="widget">

                            <a
                                href="news-details.php?id=<?php echo $post['id']; ?>"
                            >

                                <img
                                    src="<?php echo htmlspecialchars($post['image'] ?? 'images/post/news-1.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($post['title']); ?>"
                                >

                            </a>


                            <a
                                href="news-details.php?id=<?php echo $post['id']; ?>"
                                class="w_heding"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $post['title']
                                );
                                ?>

                            </a>

                        </div>

                    <?php } ?>


                    <a
                        href="news.php"
                        class="load_more_btn"
                    >
                        Load more..
                    </a>

                </div>


                <!-- TEXT WIDGET -->

                <div class="text_widget_area">

                    <?php foreach ($textPosts as $post) { ?>

                        <div class="text_widget">

                            <a
                                href="news-details.php?id=<?php echo $post['id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $post['title']
                                );
                                ?>

                            </a>

                            <h6>

                                <?php
                                echo date(
                                    "H:i",
                                    strtotime(
                                        $post['created_at']
                                    )
                                );
                                ?>

                                ,

                                <?php
                                echo htmlspecialchars(
                                    $post['category_name']
                                    ?? 'News'
                                );
                                ?>

                            </h6>

                        </div>

                    <?php } ?>

                </div>


                <!-- ADVERTISEMENT -->

                <?php if ($advertisement) { ?>

                    <div class="advertisement">

                        <a
                            href="<?php echo htmlspecialchars($advertisement['link'] ?? '#'); ?>"
                            target="_blank"
                        >

                            <img
                                src="<?php echo htmlspecialchars($advertisement['image']); ?>"
                                alt="<?php echo htmlspecialchars($advertisement['title'] ?? 'Advertisement'); ?>"
                            >

                        </a>

                    </div>

                <?php } ?>


                <!-- VIDEO WIDGET -->

                <?php if (!empty($video['thumbnail'])) { ?>

                    <div class="video_widget">

                        <div class="watch_video">

                            <a
                                href="video-details.php?id=<?php echo $videoId; ?>"
                                class="video_thumbnail"
                            >

                                <img
                                    src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                    alt="<?php echo htmlspecialchars($video['title']); ?>"
                                >

                                <span class="play_btn">

                                    <i class="flaticon-play-button">
                                        ▶
                                    </i>

                                </span>

                            </a>


                            <a
                                href="video-details.php?id=<?php echo $videoId; ?>"
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


                <!-- SOCIAL WIDGET -->

                <ul class="social_widget">

                    <?php if (!empty($socialLinks['facebook'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>"
                                target="_blank"
                            >

                                <i class="fa fa-facebook"></i>

                            </a>

                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['twitter'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                                target="_blank"
                                class="twitter"
                            >

                                <i class="fa fa-twitter"></i>

                            </a>

                        </li>

                    <?php } ?>


                    <?php if (!empty($socialLinks['linkedin'])) { ?>

                        <li>

                            <a
                                href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                                target="_blank"
                                class="linkedin"
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
                                class="instagram"
                            >

                                <i class="fa fa-instagram"></i>

                            </a>

                        </li>

                    <?php } ?>

                </ul>


                <!-- =================================================
                     TECHNOLOGY / GAMING
                ================================================== -->

                <div class="tabs_widge">

                    <div
                        class="nav nav-tabs"
                        id="nav-tab"
                        role="tablist"
                    >

                        <a
                            class="active"
                            data-toggle="tab"
                            href="#technology"
                        >
                            Technology
                        </a>

                        <a
                            data-toggle="tab"
                            href="#gaming"
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
                        >

                            <?php foreach ($technologyPosts as $post) { ?>

                                <div class="text_widget">

                                    <a
                                        href="news-details.php?id=<?php echo $post['id']; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $post['title']
                                        );
                                        ?>

                                    </a>

                                    <h6>

                                        <?php
                                        echo date(
                                            "H:i",
                                            strtotime(
                                                $post['created_at']
                                            )
                                        );
                                        ?>

                                        ,

                                        <?php
                                        echo htmlspecialchars(
                                            $post['category_name']
                                        );
                                        ?>

                                    </h6>

                                </div>

                            <?php } ?>

                        </div>


                        <!-- GAMING -->

                        <div
                            class="tab-pane fade text_widget_area"
                            id="gaming"
                        >

                            <?php foreach ($gamingPosts as $post) { ?>

                                <div class="text_widget">

                                    <a
                                        href="news-details.php?id=<?php echo $post['id']; ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $post['title']
                                        );
                                        ?>

                                    </a>

                                    <h6>

                                        <?php
                                        echo date(
                                            "H:i",
                                            strtotime(
                                                $post['created_at']
                                            )
                                        );
                                        ?>

                                        ,

                                        <?php
                                        echo htmlspecialchars(
                                            $post['category_name']
                                        );
                                        ?>

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


<!-- =====================================================
     TOPICS
===================================================== -->

<section class="topic_area">

    <div class="container">

        <div class="topics_row row">

            <div class="col-12">

                <h2>
                    Topics
                </h2>

            </div>


            <?php foreach ($topics as $topic) { ?>

                <div class="col-sm-4">

                    <a
                        href="news.php?category=<?php echo urlencode($topic['slug']); ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $topic['name']
                        );
                        ?>

                    </a>

                </div>

            <?php } ?>

        </div>

    </div>

</section>


<!-- =====================================================
     CONNECT WITH US
===================================================== -->

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
                    >

                        <i class="fa fa-youtube"></i>

                    </a>

                </li>

            <?php } ?>


        </ul>

    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer_area">

    <div class="container">

        <div class="footer_inner row">


            <div class="col-lg-5 col-md-6 footer_logo">

                <a href="index.php">

                    <img
                        src="images/logo.png"
                        alt="<?php echo htmlspecialchars($siteName); ?>"
                    >

                </a>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $siteDescription
                    );
                    ?>

                </p>


                <address>

                    <span>
                        LOCATION
                    </span>

                    <p>

                        <?php
                        echo htmlspecialchars(
                            $location
                        );
                        ?>

                    </p>

                </address>

            </div>


            <div class="col-lg-5 col-md-6">

                <div class="subscribe">

                    <h4>
                        Subscribe
                    </h4>

                    <p>
                        Sign up for our mailing list to get latest updates and offers
                    </p>

                    <form
                        action="subscribe.php"
                        method="POST"
                    >

                        <div class="input-group">

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Email Address"
                                required
                            >

                            <div class="input-group-append">

                                <button
                                    type="submit"
                                    class="input-group-text"
                                >

                                    <i class="fa fa-paper-plane"></i>

                                </button>

                            </div>

                        </div>

                    </form>


                    <h6>

                        Working Hours :
                        <?php
                        echo htmlspecialchars(
                            $workingDays
                        );
                        ?>

                        <span>

                            Close :
                            <?php
                            echo htmlspecialchars(
                                $closingDay
                            );
                            ?>

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

            Copyright ©

            <?php
            echo htmlspecialchars(
                $copyrightYear
            );
            ?>

            <a href="index.php">

                <?php
                echo htmlspecialchars(
                    $siteName
                );
                ?>

            </a>

            Developed by

            <a
                href="<?php echo htmlspecialchars($developerUrl); ?>"
                target="_blank"
            >

                <?php
                echo htmlspecialchars(
                    $developerName
                );
                ?>

            </a>

        </p>

    </div>

</footer>


<!-- =====================================================
     SCROLL TOP
===================================================== -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- =====================================================
     PRELOADER
===================================================== -->

<div class="preloader"></div>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="js/theme.js"></script>

</body>

</html>