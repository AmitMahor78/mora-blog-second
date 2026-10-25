<?php

require_once __DIR__ . '/includes/maintenance-check.php';

include "includes/db.php";

// Get Post ID

$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch Post

$postQuery = "
    SELECT 
        p.id,
        p.title,
        p.excerpt,
        p.content,
        p.image,
        p.created_at,
        p.author_id,
        p.category_id,
        c.name AS category_name,
        c.slug AS category_slug
    FROM posts p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE p.id = $postId
    AND p.status = 'published'
    LIMIT 1
";

$postResult = mysqli_query($conn, $postQuery);

$post = mysqli_fetch_assoc($postResult);

// If Post Not Found

if (!$post) {
    header("Location: news.php");
    exit;
}

// Handle New Comment

$commentError = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $commentName = trim($_POST['name'] ?? '');
    $commentEmail = trim($_POST['email'] ?? '');
    $commentMessage = trim($_POST['message'] ?? '');

    // Validate fields
    if (
        $commentName === '' ||
        $commentEmail === '' ||
        $commentMessage === ''
    ) {

        $commentError = 'Please fill all fields.';

    } elseif (!filter_var($commentEmail, FILTER_VALIDATE_EMAIL)) {

        $commentError = 'Please enter a valid email address.';

    } else {

        // Insert Comment using Prepared Statement
        $insertCommentQuery = "
            INSERT INTO comments
            (post_id, name, email, comment)
            VALUES (?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($conn, $insertCommentQuery);

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $postId,
            $commentName,
            $commentEmail,
            $commentMessage
        );

        if (mysqli_stmt_execute($stmt)) {

            // Refresh page after successful comment
            header("Location: news-details.php?id=$postId#comments");
            exit;

        } else {

            $commentError = 'Unable to submit comment. Please try again.';
        }

        mysqli_stmt_close($stmt);
    }
}




// Previous Post

$previousQuery = "
    SELECT id, title, image
    FROM posts
    WHERE id < $postId
    AND status = 'published'
    ORDER BY id DESC
    LIMIT 1
";

$previousResult = mysqli_query($conn, $previousQuery);
$previousPost = mysqli_fetch_assoc($previousResult);


// Next Post

$nextQuery = "
    SELECT id, title, image
    FROM posts
    WHERE id > $postId
    AND status = 'published'
    ORDER BY id ASC
    LIMIT 1
";

$nextResult = mysqli_query($conn, $nextQuery);
$nextPost = mysqli_fetch_assoc($nextResult);


// Fetch Categories

$categoryQuery = "
    SELECT 
        c.id,
        c.name,
        c.slug,
        COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p
        ON c.id = p.category_id
        AND p.status = 'published'
    GROUP BY c.id, c.name, c.slug
    ORDER BY c.id ASC
";

$categoryResult = mysqli_query($conn, $categoryQuery);

$categories = [];

while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row;
}

// Trending Posts

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

$trendingResult = mysqli_query($conn, $trendingQuery);

$trendingPosts = [];

while ($row = mysqli_fetch_assoc($trendingResult)) {
    $trendingPosts[] = $row;
}


// Technology Posts

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

$technologyPosts = [];

while ($row = mysqli_fetch_assoc($technologyResult)) {
    $technologyPosts[] = $row;
}


// Gaming Posts

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

$gamingPosts = [];

while ($row = mysqli_fetch_assoc($gamingResult)) {
    $gamingPosts[] = $row;
}


// Latest Text Widget Posts

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

$textWidgetPosts = [];

while ($row = mysqli_fetch_assoc($textWidgetResult)) {
    $textWidgetPosts[] = $row;
}




// Fetch Author

$postAuthorId = (int) ($post['author_id'] ?? 0);

$authorQuery = "
    SELECT
        u.id,
        u.name,
        u.created_at,
        a.image,
        COALESCE(a.bio, 'Mora Blog contributor and author.') AS bio
    FROM users u
    LEFT JOIN authors a ON u.id = a.id
    WHERE u.id = $postAuthorId
    LIMIT 1
";

$authorResult = mysqli_query($conn, $authorQuery);

$author = mysqli_fetch_assoc($authorResult);

// Fallback to first author if post author_id is missing
if (!$author) {
    $fallbackResult = mysqli_query($conn, "
        SELECT u.id, u.name, u.created_at, a.image, COALESCE(a.bio, 'Mora Blog contributor.') AS bio
        FROM users u
        LEFT JOIN authors a ON u.id = a.id
        WHERE u.role IN ('author', 'admin')
        LIMIT 1
    ");
    $author = mysqli_fetch_assoc($fallbackResult);
}


// Fetch Comments

$commentsQuery = "
    SELECT
        id,
        name,
        email,
        comment,
        created_at
    FROM comments
    WHERE post_id = $postId
    ORDER BY created_at DESC
";

$commentsResult = mysqli_query($conn, $commentsQuery);

$comments = [];

while ($row = mysqli_fetch_assoc($commentsResult)) {
    $comments[] = $row;
}

$commentCount = count($comments);


// Advertisement
$advertisementQuery = "
    SELECT id, title, image, link
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


// Related Posts
$relatedPosts = [];

$relatedQuery = "
    SELECT id, title, image, created_at
    FROM posts
    WHERE category_id = " . (int)$post['category_id'] . "
      AND id != " . (int)$post['id'] . "
      AND status = 'published'
    ORDER BY created_at DESC
    LIMIT 3
";

$relatedResult = mysqli_query($conn, $relatedQuery);

if ($relatedResult) {
    while ($relatedRow = mysqli_fetch_assoc($relatedResult)) {
        $relatedPosts[] = $relatedRow;
    }
}



// Fetch Video
$videoQuery = "
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

$videoResult = mysqli_query($conn, $videoQuery);
$video = null;

if ($videoResult) {
    $video = mysqli_fetch_assoc($videoResult);
}


// Advertisement 2
$advertisement2Query = "
    SELECT id, title, image, link
    FROM advertisements
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1
";

$advertisement2Result = mysqli_query($conn, $advertisement2Query);
$advertisement2 = null;

if ($advertisement2Result) {
    $advertisement2 = mysqli_fetch_assoc($advertisement2Result);
}




// Fetch Social Links
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

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="icon" href="images/favicon.png" type="image/x-icon" />
    <!-- Theme tittle -->
   <title>
    <?php echo htmlspecialchars($post['title']); ?> | Mora Blog
</title>
    
    <!-- Theme style CSS -->     
    <link href="css/bootstrap.min.css" rel="stylesheet"> 
    <link href="css/style.css" rel="stylesheet"> 
    
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
     
    <!-- Pages Heder Area --> 
    <div class="container">
<div class="pages_heder">
    <h2>
        <?php echo htmlspecialchars($post['title']); ?>
    </h2>
            <ol class="breadcrumb">
                <li><a href="index.php">Home</a></li> 
                <li><a href="">Blog</a></li> 
               <li>
    <a href="news-details.php?id=<?php echo $post['id']; ?>" class="active">
        Blog details
    </a>
</li>
            </ol>
        </div>
    </div> 
    <!-- End Pages Heder Area --> 
    
    <!-- Post section -->
    <section class="post_section news_post">
        <div class="container">
            <div class="row post_section_inner">
                <!-- Left_sidebar --> 
                <div class="col-lg-8">  
                    <div class="news_left_sidebar">
                        <!-- News Item -->
                        <div class="news_item news_details">
                            <h6>

    <span>
        <?php echo date("F d, Y", strtotime($post['created_at'])); ?>
    </span>

   <?php if ($author) { ?>

    <a href="#author">
        Post by :
        <?php echo htmlspecialchars($author['name']); ?>
    </a>

<?php } else { ?>

    <a href="#">
        Post by : Admin
    </a>

<?php } ?>

    <a
        href="news.php?category=<?php echo urlencode($post['category_slug']); ?>"
        class="investing"
    >
        <?php echo htmlspecialchars($post['category_name'] ?? 'News'); ?>
    </a>

</h6>

<a
    href="news-details.php?id=<?php echo $post['id']; ?>"
    class="news_heding"
>
    <?php echo htmlspecialchars($post['title']); ?>
</a>

<?php if (!empty($post['image'])) { ?>

    <img
        src="<?php echo htmlspecialchars($post['image']); ?>"
        alt="<?php echo htmlspecialchars($post['title']); ?>"
    >

<?php } ?>

<p class="fast_p">
    <?php echo htmlspecialchars($post['excerpt']); ?>
</p>

<div class="post_content">

    <?php echo nl2br(htmlspecialchars($post['content'])); ?>

</div>
                           
                           
                            
                            <div class="share_row row">
                                <div class="col-sm-6 share_area">

    <ul>

        <li>Share:</li>

        <!-- Facebook -->
        <li>
            <a
                href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getMoraSiteBaseUrl() . '/news-details.php?id=' . $post['id']); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fa fa-facebook"></i>
            </a>
        </li>

        <!-- Google Plus removed because it is discontinued -->

        <!-- LinkedIn -->
        <li>
            <a
                href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(getMoraSiteBaseUrl() . '/news-details.php?id=' . $post['id']); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fa fa-linkedin"></i>
            </a>
        </li>

        <!-- Twitter/X -->
        <li>
            <a
                href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getMoraSiteBaseUrl() . '/news-details.php?id=' . $post['id']); ?>&text=<?php echo urlencode($post['title']); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fa fa-twitter"></i>
            </a>
        </li>

    </ul>

</div>
                               <!-- Back to Blog -->

<div class="col-sm-6 back_to">

    <a href="news.php">

        <i class="fa fa-arrow-left"></i>

        Back to blog

    </a>

</div>


<!-- Previous Post -->

<?php if ($previousPost) { ?>

<div class="col-sm-6">

    <div class="sm_post">

        <?php if (!empty($previousPost['image'])) { ?>

            <img
                src="<?php echo htmlspecialchars($previousPost['image']); ?>"
                alt="<?php echo htmlspecialchars($previousPost['title']); ?>"
            >

        <?php } ?>

        <a href="news-details.php?id=<?php echo $previousPost['id']; ?>">

            <?php echo htmlspecialchars($previousPost['title']); ?>

        </a>

    </div>

</div>

<?php } ?>


<!-- Next Post -->

<?php if ($nextPost) { ?>

<div class="col-sm-6">

    <div class="sm_post">

        <?php if (!empty($nextPost['image'])) { ?>

            <img
                src="<?php echo htmlspecialchars($nextPost['image']); ?>"
                alt="<?php echo htmlspecialchars($nextPost['title']); ?>"
            >

        <?php } ?>

        <a href="news-details.php?id=<?php echo $nextPost['id']; ?>">

            <?php echo htmlspecialchars($nextPost['title']); ?>

        </a>

    </div>

</div>

<?php } ?>


<!-- Related Posts -->

<?php if (!empty($relatedPosts)) { ?>

<div class="row tranding_post_area">

    <div class="col-12">

        <div class="feature_tittle">

            <h2>Related Posts</h2>

        </div>

    </div>


    <?php foreach ($relatedPosts as $related) { ?>

        <div class="col-md-4">

            <div class="tranding_post">

                <?php if (!empty($related['image'])) { ?>

                    <a
                        href="news-details.php?id=<?php echo $related['id']; ?>"
                        class="post_img"
                    >

                        <img
                            src="<?php echo htmlspecialchars($related['image']); ?>"
                            alt="<?php echo htmlspecialchars($related['title']); ?>"
                        >

                    </a>

                <?php } ?>


                <div class="post_content">

                    <a
                        href="news-details.php?id=<?php echo $related['id']; ?>"
                        class="t_heding"
                    >

                        <?php echo htmlspecialchars($related['title']); ?>

                    </a>


                    <h6>

                        <?php
                        echo date(
                            "M d, Y",
                            strtotime($related['created_at'])
                        );
                        ?>

                    </h6>

                </div>

            </div>

        </div>

    <?php } ?>

</div>

<?php } ?>
                            </div> 
                        </div> 
                   <!-- Dynamic Author -->

<?php if ($author) { ?>

<div class="author_area">

    <h2>Author</h2>

    <div class="media">

        <div class="media-left">

            <img
                src="<?php echo htmlspecialchars(!empty($author['image']) ? $author['image'] : 'images/post/author-1.jpg'); ?>"
                alt="<?php echo htmlspecialchars($author['name']); ?>"
            >

            <a href="#">
                <?php echo htmlspecialchars($author['name']); ?>
            </a>

        </div>

        <div class="media-body">

            <h6>
                <?php
                echo date(
                    "d M, Y",
                    strtotime($author['created_at'] ?? date('Y-m-d'))
                );
                ?>
            </h6>

            <p>
                <?php echo htmlspecialchars($author['bio']); ?>
            </p>

        </div>

    </div>

</div>

<?php } ?>
                        <!-- Dynamic Comments -->

<div class="comment_area" id="comments">

    <h2>
        Comment (<?php echo $commentCount; ?>)
    </h2>

    <?php if (!empty($comments)) { ?>

        <?php foreach ($comments as $index => $comment) { ?>

            <div class="media <?php echo $index === count($comments) - 1 ? 'end_media' : ''; ?>">

                <div class="media-left">

                    <img
                        src="images/post/author-2.jpg"
                        alt="<?php echo htmlspecialchars($comment['name']); ?>"
                        class="rounded-circle"
                    >

                    <a href="#">
                        <?php echo htmlspecialchars($comment['name']); ?>
                    </a>

                </div>

                <div class="media-body <?php echo $index === count($comments) - 1 ? 'border-0' : ''; ?>">

                    <p>
                        <?php echo htmlspecialchars($comment['comment']); ?>
                    </p>

                    <h6>
                        <?php
                        echo date(
                            "d M, Y",
                            strtotime($comment['created_at'])
                        );
                        ?>
                    </h6>

                    <a href="#" class="reply_btn">
                        Reply
                    </a>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="media end_media">

            <div class="media-body border-0">

                <p>
                    No comments yet. Be the first to comment.
                </p>

            </div>

        </div>

    <?php } ?>

</div>
                        <div class="leave_reply">

    <h2>Leave a Reply</h2>

    <?php if (!empty($commentError)) { ?>

        <div class="alert alert-danger">
            <?php echo htmlspecialchars($commentError); ?>
        </div>

    <?php } ?>

    <form
        action="news-details.php?id=<?php echo $post['id']; ?>#comments"
        method="POST"
        class="row login_from"
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
            >
                Submit Reply
                <i class="fa fa-arrow-right"></i>
            </button>

        </div>

    </form>

</div>
                    </div>
                </div>
                
                <!-- End left_sidebar -->
                <div class="col-lg-4 right_sidebar">  
    <form action="news.php" method="GET">

    <div class="input-group">

        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search"
            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
        >

        <div class="input-group-append">
            <button type="submit" class="input-group-text">
                <i class="fa fa-search"></i>
            </button>
        </div>

    </div>

</form>
<div class="categories">

    <h3>Post Categories</h3>

    <ul class="cpost_categories">

        <!-- All News -->
        <li>
            <a href="news.php">
                All News
                <span>
                    <?php
                    $totalQuery = "
                        SELECT COUNT(*) AS total
                        FROM posts
                        WHERE status = 'published'
                    ";

                    $totalResult = mysqli_query($conn, $totalQuery);
                    $totalRow = mysqli_fetch_assoc($totalResult);

                    echo $totalRow['total'];
                    ?>
                </span>
            </a>
        </li>

        <!-- Dynamic Categories -->
        <?php foreach ($categories as $category) { ?>

            <li>
                <a
                    href="news.php?category=<?php echo urlencode($category['slug']); ?>"
                    class="<?php echo $category['slug'] === $post['category_slug'] ? 'active' : ''; ?>"
                >

                    <?php echo htmlspecialchars($category['name']); ?>

                    <span>
                        <?php echo $category['post_count']; ?>
                    </span>

                </a>
            </li>

        <?php } ?>

    </ul>

</div>
                    
                    <!-- Latest Bitcoin News -->
                    <div class="latest_news_widget">

    <h2>Trending News</h2>

    <?php foreach ($trendingPosts as $index => $trending) { ?>

        <div class="widget <?php echo $index === count($trendingPosts) - 1 ? 'border-0' : ''; ?>">

            <?php if (!empty($trending['image'])) { ?>

                <a href="news-details.php?id=<?php echo $trending['id']; ?>">

                    <img
                        src="<?php echo htmlspecialchars($trending['image']); ?>"
                        alt="<?php echo htmlspecialchars($trending['title']); ?>"
                    >

                </a>

            <?php } ?>

            <a
                href="news-details.php?id=<?php echo $trending['id']; ?>"
                class="w_heding"
            >
                <?php echo htmlspecialchars($trending['title']); ?>
            </a>

        </div>

    <?php } ?>

    <a href="news.php" class="load_more_btn">
        Load more..
    </a>

</div>
                
<!-- Dynamic Text Widget -->

<div class="text_widget_area">

    <?php foreach ($textWidgetPosts as $index => $textPost) { ?>

        <div class="text_widget <?php echo $index === count($textWidgetPosts) - 1 ? 'm-0' : ''; ?>">

            <a href="news-details.php?id=<?php echo $textPost['id']; ?>">
                <?php echo htmlspecialchars($textPost['title']); ?>
            </a>

            <h6>
                <?php
                echo date(
                    "g:i",
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

    <?php } ?>

    <?php if (empty($textWidgetPosts)) { ?>

        <div class="text_widget m-0">

            <h6>
                No latest posts available.
            </h6>

        </div>

    <?php } ?>

</div>
                    <!-- Advertisement -->
                    <!-- Dynamic Advertisement -->


<?php if ($advertisement) { ?>

<div class="advertisement">
    <a href="<?php echo htmlspecialchars($advertisement['link']); ?>">
        <img src="<?php echo htmlspecialchars($advertisement['image']); ?>"
             alt="<?php echo htmlspecialchars($advertisement['title']); ?>">
    </a>
</div>

<?php } ?>
 <!-- Dynamic Video Widget -->
<?php if ($video) { ?>

<div class="video_widget">
    <div class="watch_video">

        <a
            href="<?php echo htmlspecialchars($video['video_url']); ?>"
            class="video_thumbnail"
        >
            <img
                src="<?php echo htmlspecialchars($video['thumbnail']); ?>"
                alt="<?php echo htmlspecialchars($video['title']); ?>"
            >

            <span class="play_btn">
                <i class="flaticon-play-button">5.16</i>
            </span>
        </a>

        <a
            href="<?php echo htmlspecialchars($video['video_url']); ?>"
            class="video_heding"
        >
            <?php echo htmlspecialchars($video['title']); ?>
        </a>

    </div>
</div>

<?php } ?>

<!-- Dynamic Advertisement 2 -->
<?php if ($advertisement2) { ?>

<div class="advertisement_2">

    <a
        href="<?php echo htmlspecialchars($advertisement2['link'] ?? '#'); ?>"
        target="_blank"
        rel="noopener noreferrer"
    >
        <img
            src="<?php echo htmlspecialchars($advertisement2['image']); ?>"
            alt="<?php echo htmlspecialchars($advertisement2['title'] ?? 'Advertisement'); ?>"
        >
    </a>

</div>

<?php } ?>
                    <!-- Social Sidget -->
                    <!-- Dynamic Social Widget -->
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
                    <!-- Tabs Widget -->
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


        <!-- Technology Tab -->

        <div
            class="tab-pane fade show active text_widget_area"
            id="technology"
            role="tabpanel"
        >

            <?php foreach ($technologyPosts as $index => $technology) { ?>

                <div
                    class="text_widget <?php echo $index === count($technologyPosts) - 1 ? 'm-0' : ''; ?>"
                >

                    <a
                        href="news-details.php?id=<?php echo $technology['id']; ?>"
                    >
                        <?php echo htmlspecialchars($technology['title']); ?>
                    </a>

                    <h6>

                        <?php
                        echo date(
                            "g:i",
                            strtotime($technology['created_at'])
                        );
                        ?>,

                        <?php
                        echo htmlspecialchars(
                            $technology['category_name']
                        );
                        ?>

                        <span class="tech">
                            Technology
                        </span>

                    </h6>

                </div>

            <?php } ?>


            <?php if (empty($technologyPosts)) { ?>

                <div class="text_widget m-0">

                    <h6>
                        No Technology posts available.
                    </h6>

                </div>

            <?php } ?>

        </div>


        <!-- Gaming Tab -->

        <div
            class="tab-pane fade text_widget_area"
            id="gaming"
            role="tabpanel"
        >

            <?php foreach ($gamingPosts as $index => $gaming) { ?>

                <div
                    class="text_widget <?php echo $index === count($gamingPosts) - 1 ? 'm-0' : ''; ?>"
                >

                    <a
                        href="news-details.php?id=<?php echo $gaming['id']; ?>"
                    >
                        <?php echo htmlspecialchars($gaming['title']); ?>
                    </a>

                    <h6>

                        <?php
                        echo date(
                            "g:i",
                            strtotime($gaming['created_at'])
                        );
                        ?>,

                        <?php
                        echo htmlspecialchars(
                            $gaming['category_name']
                        );
                        ?>

                        <span class="gaming">
                            Gaming
                        </span>

                    </h6>

                </div>

            <?php } ?>


            <?php if (empty($gamingPosts)) { ?>

                <div class="text_widget m-0">

                    <h6>
                        No Gaming posts available.
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
    <!-- Dynamic Topics Section -->

<section class="topic_area">

    <div class="container">

        <div class="section_title">

            <h2>Topics</h2>

        </div>

        <div class="row">

            <?php if (!empty($categories)) { ?>

                <?php foreach ($categories as $category) { ?>

                    <div class="col-lg-4 col-md-6">

                        <a
                            href="news.php?category=<?php echo urlencode($category['slug']); ?>"
                        >
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="col-12">

                    <p>No topics available.</p>

                </div>

            <?php } ?>

        </div>

    </div>

</section>
    
    <!-- Connect with us -->
   <!-- Connect with us -->
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

            <li>
                <a href="#">
                    <i class="fa fa-youtube"></i>
                </a>
            </li>

        </ul>

    </div>

</section>
<!-- End Connect with us -->
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