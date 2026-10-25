<?php

require_once __DIR__ . '/includes/maintenance-check.php';

include "includes/db.php";

/* =========================================================
   GET POST ID
========================================================= */

$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($postId <= 0) {
    header("Location: news.php");
    exit;
}


/* =========================================================
   FETCH MAIN POST
========================================================= */

$postQuery = "
    SELECT
        p.id,
        p.title,
        p.content,
        p.excerpt,
        p.image,
        p.created_at,
        p.category_id,
        c.name AS category_name,
        c.slug AS category_slug,
        a.name AS author_name
    FROM posts p
    LEFT JOIN categories c
        ON p.category_id = c.id
    LEFT JOIN users a
        ON p.author_id = a.id
    WHERE p.id = $postId
      AND p.status = 'published'
    LIMIT 1
";

$postResult = mysqli_query($conn, $postQuery);

if (!$postResult) {
    die("Post Query Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($postResult) === 0) {
    header("Location: news.php");
    exit;
}

$post = mysqli_fetch_assoc($postResult);


/* =========================================================
   AUTHOR
========================================================= */

$authorName = !empty($post['author_name'])
    ? $post['author_name']
    : 'Admin';


/* =========================================================
   FETCH RELATED POSTS
========================================================= */

$currentCategoryId = (int) ($post['category_id'] ?? 0);

$relatedPosts = [];

if ($currentCategoryId > 0) {

    $relatedQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.created_at
        FROM posts p
        WHERE p.status = 'published'
          AND p.id != $postId
          AND p.category_id = $currentCategoryId
        ORDER BY p.created_at DESC
        LIMIT 2
    ";

    $relatedResult = mysqli_query($conn, $relatedQuery);

    if (!$relatedResult) {
        die("Related Posts Query Error: " . mysqli_error($conn));
    }

    while ($related = mysqli_fetch_assoc($relatedResult)) {
        $relatedPosts[] = $related;
    }
}


/* =========================================================
   FALLBACK RELATED POSTS
========================================================= */

if (count($relatedPosts) < 2) {

    $remainingPosts = 2 - count($relatedPosts);

    $relatedIdsCondition = "";

    if (count($relatedPosts) > 0) {

        $excludeIds = [$postId];

        foreach ($relatedPosts as $relatedPost) {
            $excludeIds[] = (int) $relatedPost['id'];
        }

        $excludeIds = implode(",", $excludeIds);

        $relatedIdsCondition = "AND p.id NOT IN ($excludeIds)";

    } else {

        $relatedIdsCondition = "AND p.id != $postId";
    }


    $fallbackQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.created_at
        FROM posts p
        WHERE p.status = 'published'
          $relatedIdsCondition
        ORDER BY p.created_at DESC
        LIMIT $remainingPosts
    ";

    $fallbackResult = mysqli_query($conn, $fallbackQuery);

    if (!$fallbackResult) {
        die("Fallback Related Posts Query Error: " . mysqli_error($conn));
    }

    while ($related = mysqli_fetch_assoc($fallbackResult)) {
        $relatedPosts[] = $related;
    }
}


/* =========================================================
   FETCH COMMENTS
========================================================= */

$commentsQuery = "
    SELECT
        id,
        name,
        email,
        comment,
        created_at
    FROM comments
    WHERE post_id = $postId
    ORDER BY created_at ASC
";

$commentsResult = mysqli_query($conn, $commentsQuery);

$comments = [];

if ($commentsResult) {
    while ($row = mysqli_fetch_assoc($commentsResult)) {
        $comments[] = $row;
    }
}

/* =========================================================
   FETCH SOCIAL LINKS
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

$socialResult = mysqli_query($conn, $socialQuery);

if (!$socialResult) {
    die("Social Links Query Error: " . mysqli_error($conn));
}

while ($social = mysqli_fetch_assoc($socialResult)) {

    $socialLinks[$social['platform']] = $social['url'];
}


/* =========================================================
   FETCH CATEGORIES / TOPICS
========================================================= */

$categories = [];

$categoriesQuery = "
    SELECT
        id,
        name,
        slug
    FROM categories
    ORDER BY name ASC
";

$categoriesResult = mysqli_query($conn, $categoriesQuery);

if (!$categoriesResult) {
    die("Categories Query Error: " . mysqli_error($conn));
}

while ($category = mysqli_fetch_assoc($categoriesResult)) {

    $categories[] = $category;
}


/* =========================================================
   FETCH SITE SETTINGS
========================================================= */

$settings = [];

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

if ($settingsResult) {

    $settings = mysqli_fetch_assoc($settingsResult);

}


/* =========================================================
   COMMENT SUBMISSION
========================================================= */


$commentMessage = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_comment'])
) {

    /* -----------------------------------------
       GET FORM DATA
    ----------------------------------------- */

    $commentName = trim($_POST['name'] ?? '');

    $commentEmail = trim($_POST['email'] ?? '');

    $commentText = trim($_POST['message'] ?? '');


    /* -----------------------------------------
       VALIDATION
    ----------------------------------------- */

    if (
        $commentName === ''
        || $commentEmail === ''
        || $commentText === ''
    ) {

        $commentMessage = "Please fill all fields.";

    } elseif (
        !filter_var($commentEmail, FILTER_VALIDATE_EMAIL)
    ) {

        $commentMessage = "Please enter a valid email address.";

    } else {

        /* -----------------------------------------
           ESCAPE DATA
        ----------------------------------------- */

        $commentNameSafe = mysqli_real_escape_string(
            $conn,
            $commentName
        );

        $commentEmailSafe = mysqli_real_escape_string(
            $conn,
            $commentEmail
        );

        $commentTextSafe = mysqli_real_escape_string(
            $conn,
            $commentText
        );


        /* -----------------------------------------
           INSERT COMMENT
        ----------------------------------------- */

        $insertCommentQuery = "
            INSERT INTO comments
            (
                post_id,
                name,
                email,
                comment
            )
            VALUES
            (
                $postId,
                '$commentNameSafe',
                '$commentEmailSafe',
                '$commentTextSafe'
            )
        ";


        $insertCommentResult = mysqli_query(
            $conn,
            $insertCommentQuery
        );


        /* -----------------------------------------
           SUCCESS / ERROR
        ----------------------------------------- */

        if ($insertCommentResult) {

            header(
                "Location: news-details-2.php?id="
                . $postId
                . "&comment=success"
            );

            exit;

        } else {

            $commentMessage =
                "Comment Error: "
                . mysqli_error($conn);

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

    <title>
        <?php echo htmlspecialchars($post['title']); ?>
    </title>

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

</head>

<body>


<!-- =========================================================
     HEADER TOP AREA
========================================================= -->

<?php include "includes/header.php"; ?>


<!-- =========================================================
     MAIN NAVBAR
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

        <h2>Blog Single style 2</h2>

        <ol class="breadcrumb">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="news.php">Blog</a>
            </li>

            <li>
                <a href="news-details-2.php?id=<?php echo $postId; ?>" class="active">
                    Blog details v2
                </a>
            </li>

        </ol>

    </div>

</div>


<!-- =========================================================
     POST SECTION
========================================================= -->

<section class="post_section full_width_single">

    <div class="container">

        <div class="row post_section_inner">

            <div class="col-12">

                <div class="news_left_sidebar">


                    <!-- =====================================================
                         NEWS ITEM
                    ====================================================== -->

                    <div class="news_item news_details">


                        <!-- POST META -->

                        <h6>

                            <span>
                                <?php
                                echo date(
                                    "F d, Y",
                                    strtotime($post['created_at'])
                                );
                                ?>
                            </span>

                            <a href="#">
                                Post by :
                                <?php
                                echo htmlspecialchars($authorName);
                                ?>
                            </a>

                            <a
                                href="news.php?category=<?php echo urlencode($post['category_slug']); ?>"
                                class="investing"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $post['category_name'] ?? 'General'
                                );
                                ?>
                            </a>

                        </h6>


                        <!-- POST TITLE -->

                        <a
                            href="news-details-2.php?id=<?php echo $postId; ?>"
                            class="news_heding"
                        >

                            <?php
                            echo htmlspecialchars($post['title']);
                            ?>

                        </a>


                        <!-- MAIN IMAGE -->

                        <?php if (!empty($post['image'])) { ?>

                            <img
                                src="<?php echo htmlspecialchars($post['image']); ?>"
                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                            >

                        <?php } ?>


                        <!-- POST CONTENT -->

                        <div class="post_content">

                            <?php
                            echo $post['content'];
                            ?>

                        </div>


                        <!-- =================================================
                             EXTRA TEMPLATE IMAGES
                        ================================================== -->

                        <div class="row half_post">

                            <div class="col-md-6">

                                <img
                                    src="images/post/blog-single-3.jpg"
                                    alt="Blog Image"
                                >

                            </div>

                            <div class="col-md-6">

                                <p>
                                    However, with mobile subscriber growth
                                    slowing and competition intensifying,
                                    many telecom operators are questioning
                                    whether they've had the best of times
                                    and the future outlook may not be so
                                    promising.
                                </p>

                                <p>
                                    I have collected twenty of my favorite
                                    quotes and hope that you will get some
                                    inspiration from them.
                                </p>

                            </div>

                        </div>


                        <p class="down_photo">

                            I have had many times when I needed some
                            encouragement and had several of these business
                            quotes on my private notice board.

                        </p>


                        <img
                            src="images/post/blog-single-4.jpg"
                            alt="Blog Image"
                        >


                        <!-- =================================================
                             INTERESTING SECTION
                        ================================================== -->

                        <div class="some_interesting row">

                            <h2 class="col-12">
                                New mindset and thriving ecosystem drive
                                future applications
                            </h2>


                            <div class="col-md-4">

                                <div class="interesting">

                                    <h4>Integration</h4>

                                    <p>
                                        As both examples illustrate,
                                        a data integration strategy is key
                                        to deeply understanding your user base.
                                    </p>

                                </div>


                                <div class="interesting">

                                    <h4 class="hc_3">
                                        Integration Is Critical
                                    </h4>

                                    <p>
                                        Huawei is cultivating an ecosystem
                                        that brings together global and local
                                        business partners.
                                    </p>

                                </div>

                            </div>


                            <div class="col-md-8">

                                <img
                                    src="images/post/blog-single-5.jpg"
                                    alt="Blog Image"
                                >

                            </div>

                        </div>


                        <p class="end_pera">

                            There’s a very good chance that you could save
                            hundreds of pounds in just a few minutes.
                            Business Insurance calculations can help protect
                            your business from unexpected situations.

                        </p>


                        <!-- =================================================
                             SHARE AREA
                        ================================================== -->

                        <div class="share_row row justify-content-between">

                            <div class="col-sm-6 share_area">

                                <ul>

                                    <li>Share:</li>

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

                                </ul>

                            </div>


                            <!-- BACK TO BLOG -->

                            <div class="col-sm-6 back_to">

                                <a href="news.php">

                                    <i class="fa fa-arrow-left"></i>

                                    Back to blog

                                </a>

                            </div>


                            <!-- RELATED POST 1 -->

                            <?php if (isset($relatedPosts[0])) { ?>

                                <div class="col-sm-4">

                                    <div class="sm_post">

                                        <?php if (!empty($relatedPosts[0]['image'])) { ?>

                                            <img
                                                src="<?php echo htmlspecialchars($relatedPosts[0]['image']); ?>"
                                                alt="<?php echo htmlspecialchars($relatedPosts[0]['title']); ?>"
                                            >

                                        <?php } ?>

                                        <a
                                            href="news-details-2.php?id=<?php echo $relatedPosts[0]['id']; ?>"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $relatedPosts[0]['title']
                                            );
                                            ?>
                                        </a>

                                    </div>

                                </div>

                            <?php } ?>


                            <!-- RELATED POST 2 -->

                            <?php if (isset($relatedPosts[1])) { ?>

                                <div class="col-sm-4">

                                    <div class="sm_post">

                                        <?php if (!empty($relatedPosts[1]['image'])) { ?>

                                            <img
                                                src="<?php echo htmlspecialchars($relatedPosts[1]['image']); ?>"
                                                alt="<?php echo htmlspecialchars($relatedPosts[1]['title']); ?>"
                                            >

                                        <?php } ?>

                                        <a
                                            href="news-details-2.php?id=<?php echo $relatedPosts[1]['id']; ?>"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $relatedPosts[1]['title']
                                            );
                                            ?>
                                        </a>

                                    </div>

                                </div>

                            <?php } ?>

                        </div>

                    </div>


                    <!-- =====================================================
                         AUTHOR AREA
                    ====================================================== -->

                    <div class="author_area">

                        <h2>Author</h2>

                        <div class="media">

                            <div class="media-left">

                                <?php if (!empty($author['image'])) { ?>

                                    <img
                                        src="<?php echo htmlspecialchars($author['image']); ?>"
                                        alt="<?php echo htmlspecialchars($authorName); ?>"
                                    >

                                <?php } else { ?>

                                    <img
                                        src="images/post/author-1.jpg"
                                        alt="<?php echo htmlspecialchars($authorName); ?>"
                                    >

                                <?php } ?>

                                <a href="#">
                                    <?php echo htmlspecialchars($authorName); ?>
                                </a>

                            </div>


                            <div class="media-body">

                                <h6>
                                    <?php
                                    echo date(
                                        "d M,Y",
                                        strtotime($post['created_at'])
                                    );
                                    ?>
                                </h6>

                                <p>

                                    <?php
                                    if (!empty($author['bio'])) {

                                        echo htmlspecialchars($author['bio']);

                                    } else {

                                        echo "Author information is available with this article.";

                                    }
                                    ?>

                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- =====================================================
                         COMMENTS
                    ====================================================== -->

                    <div class="comment_area">

                        <h2>
                            Comment
                            (<?php echo count($comments); ?>)
                        </h2>


                        <?php if (count($comments) > 0) { ?>

                            <?php foreach ($comments as $index => $comment) { ?>

                                <div
                                    class="media <?php echo $index === count($comments) - 1 ? 'end_media' : ''; ?>"
                                >

                                    <div class="media-left">

                                        <?php
                                        $commentImages = [
                                            "images/post/author-2.jpg",
                                            "images/post/author-3.jpg",
                                            "images/post/author-4.jpg"
                                        ];

                                        $commentImage =
                                            $commentImages[
                                                $index % count($commentImages)
                                            ];
                                        ?>

                                        <img
                                            src="<?php echo $commentImage; ?>"
                                            alt="<?php echo htmlspecialchars($comment['name']); ?>"
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
                                                "d M,Y",
                                                strtotime($comment['created_at'])
                                            );
                                            ?>
                                        </h6>

                                        <a
                                            href="#"
                                            class="reply_btn"
                                        >
                                            Reply
                                        </a>

                                    </div>

                                </div>

                            <?php } ?>

                        <?php } else { ?>

                            <p>
                                No comments yet. Be the first to comment.
                            </p>

                        <?php } ?>

                    </div>


                    <!-- =====================================================
                         LEAVE REPLY
                    ====================================================== -->

                    <div class="leave_reply">

                        <h2>Leave a Reply</h2>


                        <?php if ($commentMessage !== '') { ?>

                            <p>
                                <?php
                                echo htmlspecialchars($commentMessage);
                                ?>
                            </p>

                        <?php } ?>


                        <?php if (isset($_GET['comment']) && $_GET['comment'] === 'success') { ?>

                            <p>
                                Your comment has been submitted successfully
                                and is waiting for approval.
                            </p>

                        <?php } ?>


                        <form
                            class="row login_from"
                            method="POST"
                            action="news-details-2.php?id=<?php echo $postId; ?>"
                        >

                            <div class="form-group col-12">

                                <textarea
                                    class="form-control"
                                    id="message"
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

        </div>

    </div>

</section>


<!-- =========================================================
     TOPICS
========================================================= -->

<section class="topic_area">

    <div class="container">

        <div class="topics_row row">

            <div class="col-12">

                <h2>Topics</h2>

            </div>


            <?php foreach ($categories as $category) { ?>

                <div class="col-lg-4 col-md-6">

                    <a
                        href="news.php?category=<?php echo urlencode($category['slug']); ?>"
                    >

                        <?php
                        echo htmlspecialchars($category['name']);
                        ?>

                    </a>

                </div>

            <?php } ?>

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


<!-- Scroll Top Button -->

<button class="scroll-top">

    <i class="fa fa-angle-double-up"></i>

</button>


<!-- Preloader -->

<div class="preloader"></div>


<!-- =========================================================
     SCRIPTS
========================================================= -->

<script src="js/jquery-3.3.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="vendors/animate-css/wow.min.js"></script>

<script src="js/theme.js"></script>


</body>

</html>