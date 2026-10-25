<?php

require_once __DIR__ . '/includes/maintenance-check.php';

include "includes/db.php";


/* =====================================================
   GET POST ID
===================================================== */

$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;


/* =====================================================
   DEFAULT VARIABLES
===================================================== */

$post = null;
$comments = [];
$relatedPosts = [];
$socialLinks = [];
$categories = [];

$commentMessage = '';


/* =====================================================
   GET MAIN POST
===================================================== */

if ($postId > 0) {

    $postQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.excerpt,
            p.content,
            p.category_id,
            p.created_at,
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

    if ($postResult) {
        $post = mysqli_fetch_assoc($postResult);
    }
}


/* =====================================================
   IF POST NOT FOUND
===================================================== */

if (!$post) {
    header("Location: error.php");
    exit;
}


/* =====================================================
   COMMENT SUBMISSION
===================================================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_comment'])
) {

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


        if ($insertCommentResult) {

            header(
                "Location: news-details-3.php?id="
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


/* =====================================================
   COMMENT SUCCESS MESSAGE
===================================================== */

if (
    isset($_GET['comment'])
    && $_GET['comment'] === 'success'
) {

    $commentMessage =
        "Your comment has been submitted successfully.";

}


/* =====================================================
   GET COMMENTS
===================================================== */

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

$commentsResult = mysqli_query(
    $conn,
    $commentsQuery
);

if ($commentsResult) {

    while ($row = mysqli_fetch_assoc($commentsResult)) {

        $comments[] = $row;

    }

}


/* =====================================================
   GET RELATED POSTS
===================================================== */

$categoryId = (int) ($post['category_id'] ?? 0);

$relatedQuery = "
    SELECT
        p.id,
        p.title,
        p.image,
        p.created_at
    FROM posts p
    WHERE p.status = 'published'
      AND p.id != $postId
";

if ($categoryId > 0) {

    $relatedQuery .= "
        AND p.category_id = $categoryId
    ";

}

$relatedQuery .= "
    ORDER BY p.created_at DESC
    LIMIT 2
";


$relatedResult = mysqli_query(
    $conn,
    $relatedQuery
);

if ($relatedResult) {

    while ($row = mysqli_fetch_assoc($relatedResult)) {

        $relatedPosts[] = $row;

    }

}


/* =====================================================
   GET SOCIAL LINKS
===================================================== */

$socialQuery = "
    SELECT
        platform,
        url
    FROM social_links
    WHERE status = 'active'
    ORDER BY id ASC
";

$socialResult = mysqli_query(
    $conn,
    $socialQuery
);

if ($socialResult) {

    while ($row = mysqli_fetch_assoc($socialResult)) {

        $socialLinks[$row['platform']] = $row['url'];

    }

}


/* =====================================================
   GET CATEGORIES / TOPICS
===================================================== */

$categoryQuery = "
    SELECT
        id,
        name,
        slug
    FROM categories
    ORDER BY name ASC
    LIMIT 9
";

$categoryResult = mysqli_query(
    $conn,
    $categoryQuery
);

if ($categoryResult) {

    while ($row = mysqli_fetch_assoc($categoryResult)) {

        $categories[] = $row;

    }

}


/* =====================================================
   POST DATA
===================================================== */

$commentCount = count($comments);

$postDate = date(
    "F d, Y",
    strtotime($post['created_at'])
);

$authorName = !empty($post['author_name'])
    ? $post['author_name']
    : 'Admin';

$categoryName = !empty($post['category_name'])
    ? $post['category_name']
    : 'General';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <link
        rel="icon"
        href="images/favicon.png"
        type="image/x-icon"
    />

    <!-- Theme title -->

    <title>
        <?php echo htmlspecialchars($post['title']); ?>
        | Mora Blog
    </title>

    <!-- Theme style CSS -->

    <link
        href="css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="css/font-awesome.min.css"
        rel="stylesheet"
    >

    <link
        href="css/style.css"
        rel="stylesheet"
    >

</head>


<body class="background-transparent">


<!-- =====================================================
     HEADER TOP AREA
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
            Blog Single style 3
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
                <a
                    href="news-details-3.php?id=<?php echo $postId; ?>"
                    class="active"
                >
                    Blog details v3
                </a>
            </li>

        </ol>

    </div>

</div>


<!-- =====================================================
     POST SECTION
===================================================== -->

<section class="post_section full_width_single fws_2">

    <div class="container">

        <div class="row post_section_inner">


            <!-- LEFT SIDEBAR -->

            <div class="col-12">

                <div class="news_left_sidebar nd_2">


                    <!-- =================================================
                         NEWS ITEM
                    ================================================= -->

                    <div class="news_item news_details">


                        <!-- POST META -->

                        <div class="total_like_comment_area">

                            <h6>

                                <span>

                                    <?php
                                    echo htmlspecialchars($postDate);
                                    ?>

                                </span>


                                <a href="#">

                                    Post by :

                                    <?php
                                    echo htmlspecialchars($authorName);
                                    ?>

                                </a>


                                <a
                                    href="#"
                                    class="investing"
                                >

                                    <?php
                                    echo htmlspecialchars($categoryName);
                                    ?>

                                </a>

                            </h6>


                            <ul class="total_like_comment">

                                <li>

                                    <a href="#">

                                        <?php if (!empty($post['image'])) { ?>

                                            <img
                                                src="<?php echo htmlspecialchars($post['image']); ?>"
                                                alt=""
                                                class="rounded-circle"
                                                style="width:40px;height:40px;object-fit:cover;"
                                            >

                                        <?php } ?>

                                        <?php
                                        echo htmlspecialchars($authorName);
                                        ?>

                                    </a>

                                </li>


                                <li>

                                    <a href="#">

                                        <i class="fa fa-hand-o-right"></i>

                                        120

                                    </a>

                                </li>


                                <li>

                                    <a href="#">

                                        <i class="fa fa-comments"></i>

                                        <?php
                                        echo $commentCount;
                                        ?>

                                    </a>

                                </li>

                            </ul>

                        </div>


                        <!-- =================================================
                             POST TITLE
                        ================================================= -->

                        <a
                            href="news-details-3.php?id=<?php echo $postId; ?>"
                            class="news_heding"
                        >

                            <?php
                            echo htmlspecialchars($post['title']);
                            ?>

                        </a>


                        <!-- =================================================
                             POST IMAGE SLIDER
                        ================================================= -->

                        <div
                            class="carousel slide banner_slider col-12"
                            data-ride="carousel"
                        >

                            <div class="carousel-inner">


                                <div class="carousel-item active">

                                    <?php if (!empty($post['image'])) { ?>

                                        <img
                                            src="<?php echo htmlspecialchars($post['image']); ?>"
                                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                                        >

                                    <?php } ?>

                                </div>


                                <div class="carousel-item">

                                    <?php if (!empty($post['image'])) { ?>

                                        <img
                                            src="<?php echo htmlspecialchars($post['image']); ?>"
                                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                                        >

                                    <?php } ?>

                                </div>


                            </div>


                            <a
                                class="carousel-control-prev"
                                href=".banner_slider"
                                data-slide="prev"
                            >

                                <i class="fa fa-arrow-left"></i>

                            </a>


                            <a
                                class="carousel-control-next"
                                href=".banner_slider"
                                data-slide="next"
                            >

                                <i class="fa fa-arrow-right"></i>

                            </a>

                        </div>


                        <!-- =================================================
                             POST CONTENT
                        ================================================= -->

                        <div class="post_dynamic_content">

                            <?php

                            echo $post['content'];

                            ?>

                        </div>


                        <!-- =================================================
                             RELATED IMAGES
                        ================================================= -->

                        <?php if (!empty($relatedPosts)) { ?>

                            <div class="row sub_post">

                                <?php foreach (
                                    $relatedPosts
                                    as $related
                                ) { ?>

                                    <div class="col-6">

                                        <div class="sub_img">

                                            <?php if (
                                                !empty(
                                                    $related['image']
                                                )
                                            ) { ?>

                                                <img
                                                    src="<?php echo htmlspecialchars($related['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($related['title']); ?>"
                                                >

                                            <?php } ?>

                                        </div>

                                    </div>

                                <?php } ?>

                            </div>

                        <?php } ?>


                        <!-- =================================================
                             SHARE ROW
                        ================================================= -->

                        <div
                            class="share_row row justify-content-between"
                        >


                            <div
                                class="col-sm-6 share_area"
                            >

                                <ul>

                                    <li>
                                        Share:
                                    </li>


                                    <?php if (
                                        !empty(
                                            $socialLinks['facebook']
                                        )
                                    ) { ?>

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


                                    <?php if (
                                        !empty(
                                            $socialLinks['google']
                                        )
                                    ) { ?>

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


                                    <?php if (
                                        !empty(
                                            $socialLinks['linkedin']
                                        )
                                    ) { ?>

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


                                    <?php if (
                                        !empty(
                                            $socialLinks['twitter']
                                        )
                                    ) { ?>

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


                            <div
                                class="col-sm-6 back_to"
                            >

                                <a href="news.php">

                                    <i class="fa fa-arrow-left"></i>

                                    Back to blog

                                </a>

                            </div>


                            <!-- =================================================
                                 RELATED SMALL POSTS
                            ================================================= -->

                            <?php foreach (
                                $relatedPosts
                                as $related
                            ) { ?>

                                <div class="col-sm-4">

                                    <div class="sm_post">

                                        <?php if (
                                            !empty(
                                                $related['image']
                                            )
                                        ) { ?>

                                            <img
                                                src="<?php echo htmlspecialchars($related['image']); ?>"
                                                alt="<?php echo htmlspecialchars($related['title']); ?>"
                                            >

                                        <?php } ?>


                                        <a
                                            href="news-details-3.php?id=<?php echo $related['id']; ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $related['title']
                                            );
                                            ?>

                                        </a>

                                    </div>

                                </div>

                            <?php } ?>


                        </div>

                    </div>


                    <!-- =================================================
                         AUTHOR AREA
                    ================================================= -->

                    <div class="author_area">

                        <h2>
                            Author
                        </h2>


                        <div class="media">

                            <div class="media-left">

                                <img
                                    src="images/post/author-1.jpg"
                                    alt=""
                                >

                                <a href="#">

                                    <?php
                                    echo htmlspecialchars($authorName);
                                    ?>

                                </a>

                            </div>


                            <div class="media-body">

                                <h6>

                                    <?php
                                    echo date(
                                        "d M, Y",
                                        strtotime(
                                            $post['created_at']
                                        )
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


                    <!-- =================================================
                         COMMENT AREA
                    ================================================= -->

                    <div class="comment_area">

                        <h2>

                            Comment
                            (<?php echo $commentCount; ?>)

                        </h2>


                        <?php if (!empty($comments)) { ?>


                            <?php foreach (
                                $comments as $index => $comment
                            ) { ?>


                                <div
                                    class="media
                                    <?php
                                    if (
                                        $index ===
                                        count($comments) - 1
                                    ) {
                                        echo 'end_media';
                                    }
                                    ?>"
                                >


                                    <div class="media-left">

                                        <?php

                                        $commentImages = [
                                            'images/post/author-2.jpg',
                                            'images/post/author-3.jpg',
                                            'images/post/author-4.jpg'
                                        ];

                                        $commentImage =
                                            $commentImages[
                                                $index
                                                %
                                                count(
                                                    $commentImages
                                                )
                                            ];

                                        ?>


                                        <img
                                            src="<?php echo $commentImage; ?>"
                                            alt=""
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
                                                    $comment[
                                                        'created_at'
                                                    ]
                                                )
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
                                No comments yet.
                                Be the first to comment.
                            </p>


                        <?php } ?>

                    </div>


                    <!-- =================================================
                         CONNECT WITH
                    ================================================= -->

                    <ul class="connect_with_s">

                        <li class="heding">
                            Connect with:
                        </li>

                        <li>
                            <a href="#">
                                Login with Facebook
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Login with Twitter
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Login with Google+
                            </a>
                        </li>

                    </ul>


                    <!-- =================================================
                         LEAVE REPLY
                    ================================================= -->

                    <div class="leave_reply">

                        <h2>
                            Leave a Reply
                        </h2>


                        <?php if (
                            !empty($commentMessage)
                        ) { ?>

                            <div class="alert alert-info">

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
                            action="news-details-3.php?id=<?php echo $postId; ?>"
                        >


                            <div
                                class="form-group col-12"
                            >

                                <textarea
                                    class="form-control"
                                    id="message"
                                    name="message"
                                    placeholder="Your Comment"
                                    required
                                ></textarea>

                            </div>


                            <div
                                class="form-group col-12"
                            >

                                <input
                                    type="text"
                                    class="form-control"
                                    name="name"
                                    placeholder="Name"
                                    required
                                >

                            </div>


                            <div
                                class="form-group col-12"
                            >

                                <input
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    placeholder="Email"
                                    required
                                >

                            </div>


                            <div
                                class="form-group larg_btn col-12"
                            >

                                <button
                                    class="sm_btn"
                                    type="submit"
                                    name="submit_comment"
                                >

                                    Submit Reply

                                    <i
                                        class="fa fa-arrow-right"
                                    ></i>

                                </button>

                            </div>


                        </form>

                    </div>


                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     TOPICS
===================================================== -->

<section class="topic_area ta_2">

    <div class="container">

        <div class="topics_row row">

            <div class="col-12">

                <h2>
                    Topics
                </h2>

            </div>


            <?php foreach (
                $categories as $category
            ) { ?>

                <div
                    class="col-lg-4 col-md-6"
                >

                    <a
                        href="news.php?category=<?php echo urlencode($category['slug']); ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $category['name']
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


            <?php if (
                !empty($socialLinks['google'])
            ) { ?>

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


            <?php if (
                !empty($socialLinks['linkedin'])
            ) { ?>

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


            <?php if (
                !empty($socialLinks['instagram'])
            ) { ?>

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


            <?php if (
                !empty($socialLinks['twitter'])
            ) { ?>

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


            <?php if (
                !empty($socialLinks['youtube'])
            ) { ?>

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


<!-- =====================================================
     FOOTER
===================================================== -->

<?php include "includes/footer.php"; ?>


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