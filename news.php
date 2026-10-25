<?php

require_once __DIR__ . '/includes/maintenance-check.php';

include "includes/db.php";



/* =========================================================
   TOTAL PUBLISHED POSTS
   All News count
========================================================= */

$totalPostsQuery = "
    SELECT COUNT(*) AS total
    FROM posts
    WHERE status = 'published'
";

$totalPostsResult = mysqli_query($conn, $totalPostsQuery);

$totalPosts = 0;

if ($totalPostsResult) {

    $totalPostsRow = mysqli_fetch_assoc($totalPostsResult);

    $totalPosts = (int) $totalPostsRow['total'];
}


/* =========================================================
   SELECTED CATEGORY
========================================================= */

$selectedCategory = isset($_GET['category'])
    ? trim($_GET['category'])
    : '';


/* =========================================================
   SEARCH
========================================================= */

$searchKeyword = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


/* =========================================================
   FETCH POSTS
========================================================= */

$postsQuery = "
    SELECT
        p.id,
        p.title,
        p.slug,
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


/* =========================================================
   CATEGORY FILTER
========================================================= */

if ($selectedCategory !== '') {

    $categorySafe = mysqli_real_escape_string(
        $conn,
        $selectedCategory
    );

    $postsQuery .= "
        AND c.slug = '$categorySafe'
    ";
}


/* =========================================================
   SEARCH FILTER
========================================================= */

if ($searchKeyword !== '') {

    $searchSafe = mysqli_real_escape_string(
        $conn,
        $searchKeyword
    );

    $postsQuery .= "
        AND (
            p.title LIKE '%$searchSafe%'
            OR p.excerpt LIKE '%$searchSafe%'
            OR p.content LIKE '%$searchSafe%'
        )
    ";
}


/* =========================================================
   ORDER POSTS
========================================================= */

$postsQuery .= "
    ORDER BY p.created_at DESC
";


/* =========================================================
   EXECUTE POSTS QUERY
========================================================= */

$postsResult = mysqli_query($conn, $postsQuery);

$posts = [];

if ($postsResult) {

    while ($row = mysqli_fetch_assoc($postsResult)) {

        $posts[] = $row;
    }
}


/* =========================================================
   POST CATEGORIES
========================================================= */

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
    GROUP BY
        c.id,
        c.name,
        c.slug
    ORDER BY c.id ASC
";

$categoryResult = mysqli_query(
    $conn,
    $categoryQuery
);

$categories = [];

if ($categoryResult) {

    while ($row = mysqli_fetch_assoc($categoryResult)) {

        $categories[] = $row;
    }
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

$trendingResult = mysqli_query(
    $conn,
    $trendingQuery
);


/* =========================================================
   TEXT WIDGET POSTS
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

$textWidgetResult = mysqli_query(
    $conn,
    $textWidgetQuery
);


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

$advertisementResult = mysqli_query(
    $conn,
    $advertisementQuery
);


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

$videoWidgetResult = mysqli_query(
    $conn,
    $videoWidgetQuery
);


/* =========================================================
   SECOND ADVERTISEMENT
========================================================= */

$advertisement2Query = "
    SELECT
        id,
        title,
        image,
        link
    FROM advertisements
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1 OFFSET 1
";

$advertisement2Result = mysqli_query(
    $conn,
    $advertisement2Query
);


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

$socialResult = mysqli_query(
    $conn,
    $socialQuery
);

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
        p.image,
        p.created_at
    FROM posts p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE p.status = 'published'
      AND LOWER(c.name) = 'technology'
    ORDER BY p.created_at DESC
    LIMIT 4
";

$technologyResult = mysqli_query(
    $conn,
    $technologyQuery
);


/* =========================================================
   GAMING POSTS
========================================================= */

$gamingQuery = "
    SELECT
        p.id,
        p.title,
        p.image,
        p.created_at
    FROM posts p
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE p.status = 'published'
      AND LOWER(c.name) = 'gaming'
    ORDER BY p.created_at DESC
    LIMIT 4
";

$gamingResult = mysqli_query(
    $conn,
    $gamingQuery
);


/* =========================================================
   SITE SETTINGS
========================================================= */

$siteSettingsQuery = "
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

$siteSettingsResult = mysqli_query(
    $conn,
    $siteSettingsQuery
);

$siteSettings = null;

if (
    $siteSettingsResult &&
    mysqli_num_rows($siteSettingsResult) > 0
) {

    $siteSettings = mysqli_fetch_assoc(
        $siteSettingsResult
    );
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
    <?php
    if (!empty($selectedCategory)) {
        echo htmlspecialchars(ucwords(str_replace('-', ' ', $selectedCategory)));
    } else {
        echo "Blog List";
    }
    ?>
</h2>
            <ol class="breadcrumb">
                <li><a href="index.php">Home</a></li> 
                <li><a href="">Blog</a></li> 
              <li>
    <a href="news.php" class="active">
        <?php
        if (!empty($selectedCategory)) {
            echo htmlspecialchars(ucwords(str_replace('-', ' ', $selectedCategory)));
        } else {
            echo "Blog v1";
        }
        ?>
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
                       <?php foreach ($posts as $post) { ?>

    <div class="news_item">

        <h6>
            <?php echo date("F d, Y", strtotime($post['created_at'])); ?>
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

        <p>
            <?php echo htmlspecialchars($post['excerpt']); ?>
        </p>

        <a
            href="news-details.php?id=<?php echo $post['id']; ?>"
            class="red_btn"
        >
            Continue Reading
            <i class="fa fa-arrow-right"></i>
        </a>

    </div>

<?php } ?>
                        <div class="col-12 p-0">
                            <nav class="pagination_area">
                                <ul class="pagination">
                                    <li class="page-item disabled"><a class="page-link active" href="#" tabindex="-1">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item"><a class="page-link" href="#"><i class="fa fa-arrow-right"></i></a></li>
                                </ul>
                            </nav>
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

            <button
                type="submit"
                class="input-group-text"
                style="border: none;"
            >
                <i class="fa fa-search"></i>
            </button>

        </div>

    </div>

</form>
                    <div class="categories">

    <h3>Post Categories</h3>

    <ul class="cpost_categories">

        <li>
          <a
    href="news.php"
    class="<?php echo empty($selectedCategory) ? 'active' : ''; ?>"
>
    All News
    <span><?php echo $totalPosts; ?></span>
</a>
        </li>

       <?php foreach ($categories as $category) { ?>

    <li>

        <a
            href="news.php?category=<?php echo urlencode($category['slug']); ?>"
            class="<?php echo $selectedCategory === $category['slug'] ? 'active' : ''; ?>"
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
                    
                    <!-- Latest Bitcoin News -->
                    <div class="latest_news_widget">

    <h2>Trending News</h2>

    <?php if ($trendingResult && mysqli_num_rows($trendingResult) > 0) { ?>

        <?php
        $trendingCount = 0;

        while ($trending = mysqli_fetch_assoc($trendingResult)) {

            $trendingCount++;

            $widgetClass = ($trendingCount == 3)
                ? 'widget border-0'
                : 'widget';
        ?>

            <div class="<?php echo $widgetClass; ?>">

                <a href="news-details.php?id=<?php echo $trending['id']; ?>">

                    <?php if (!empty($trending['image'])) { ?>

                        <img
                            src="<?php echo htmlspecialchars($trending['image']); ?>"
                            alt="<?php echo htmlspecialchars($trending['title']); ?>"
                        >

                    <?php } ?>

                </a>

                <a
                    href="news-details.php?id=<?php echo $trending['id']; ?>"
                    class="w_heding"
                >
                    <?php echo htmlspecialchars($trending['title']); ?>
                </a>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="widget border-0">

            <p>No trending news available.</p>

        </div>

    <?php } ?>

    <a href="news.php" class="load_more_btn">
        Load more..
    </a>

</div>
                    <!-- Text widget -->
                    <div class="text_widget_area">

    <h2>Text Widget</h2>

    <?php if ($textWidgetResult && mysqli_num_rows($textWidgetResult) > 0) { ?>

        <?php while ($textWidget = mysqli_fetch_assoc($textWidgetResult)) { ?>

            <div class="text_widget">

                <a
                    href="news-details.php?id=<?php echo $textWidget['id']; ?>"
                    class="w_heding"
                >
                    <?php echo htmlspecialchars($textWidget['title']); ?>
                </a>

                <p>
                    <?php
                    echo htmlspecialchars(
                        $textWidget['category_name'] ?? ''
                    );
                    ?>
                    <span>
                        <?php
                        echo date(
                            'M d, Y',
                            strtotime($textWidget['created_at'])
                        );
                        ?>
                    </span>
                </p>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="text_widget">
            <p>No posts available.</p>
        </div>

    <?php } ?>

</div>
                    <!-- Advertisement -->
                    <?php if ($advertisementResult && mysqli_num_rows($advertisementResult) > 0) { ?>

    <?php $advertisement = mysqli_fetch_assoc($advertisementResult); ?>

    <div class="advertisement">

        <a
            href="<?php echo htmlspecialchars($advertisement['link'] ?? '#'); ?>"
            target="_blank"
            rel="noopener noreferrer"
        >

            <?php if (!empty($advertisement['image'])) { ?>

                <img
                    src="<?php echo htmlspecialchars($advertisement['image']); ?>"
                    alt="<?php echo htmlspecialchars($advertisement['title']); ?>"
                >

            <?php } ?>

        </a>

    </div>

<?php } ?>
                    <!-- Video widget -->
                   <?php if ($videoWidgetResult && mysqli_num_rows($videoWidgetResult) > 0) { ?>

    <?php $videoWidget = mysqli_fetch_assoc($videoWidgetResult); ?>

    <div class="video_widget">

        

        <a href="video-details.php?id=<?php echo $videoWidget['id']; ?>">

            <?php if (!empty($videoWidget['thumbnail'])) { ?>

                <img
                    src="<?php echo htmlspecialchars($videoWidget['thumbnail']); ?>"
                    alt="<?php echo htmlspecialchars($videoWidget['title']); ?>"
                >

            <?php } ?>

            <span class="play_btn">
                <i class="fa fa-play"></i>
            </span>

        </a>

        <a
            href="video-details.php?id=<?php echo $videoWidget['id']; ?>"
            class="w_heding"
        >
            <?php echo htmlspecialchars($videoWidget['title']); ?>
        </a>

    </div>

<?php } ?>
                    <!-- Advertisement -->
                 <?php if ($advertisement2Result && mysqli_num_rows($advertisement2Result) > 0) { ?>

    <?php $advertisement2 = mysqli_fetch_assoc($advertisement2Result); ?>

    <div class="advertisement">

        <a
            href="<?php echo htmlspecialchars($advertisement2['link'] ?? '#'); ?>"
            target="_blank"
            rel="noopener noreferrer"
        >

            <?php if (!empty($advertisement2['image'])) { ?>

                <img
                    src="<?php echo htmlspecialchars($advertisement2['image']); ?>"
                    alt="<?php echo htmlspecialchars($advertisement2['title']); ?>"
                >

            <?php } ?>

        </a>

    </div>

<?php } ?>
                    <!-- Social Sidget -->
                    <div class="social_widget">

    <h2>Follow Us</h2>

    <ul class="social_icon">

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
                    target="_blank"
                    rel="noopener noreferrer"
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

        <?php if (!empty($socialLinks['pinterest'])) { ?>

            <li>
                <a
                    href="<?php echo htmlspecialchars($socialLinks['pinterest']); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fa fa-pinterest"></i>
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

</div> 
                    <!-- Tabs Widget -->
                    <div class="technology_gaming_area">

    <div class="technology_gaming_tab">

        <ul class="nav nav-tabs" role="tablist">

            <li class="nav-item">
                <a
                    class="nav-link active"
                    data-toggle="tab"
                    href="#technology"
                    role="tab"
                >
                    Technology
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link"
                    data-toggle="tab"
                    href="#gaming"
                    role="tab"
                >
                    Gaming
                </a>
            </li>

        </ul>

        <div class="tab-content">

            <!-- TECHNOLOGY -->
            <div
                class="tab-pane fade show active"
                id="technology"
                role="tabpanel"
            >

                <?php if ($technologyResult && mysqli_num_rows($technologyResult) > 0) { ?>

                    <?php while ($technology = mysqli_fetch_assoc($technologyResult)) { ?>

                        <div class="widget">

                            <a href="news-details.php?id=<?php echo $technology['id']; ?>">

                                <?php if (!empty($technology['image'])) { ?>

                                    <img
                                        src="<?php echo htmlspecialchars($technology['image']); ?>"
                                        alt="<?php echo htmlspecialchars($technology['title']); ?>"
                                    >

                                <?php } ?>

                            </a>

                            <a
                                href="news-details.php?id=<?php echo $technology['id']; ?>"
                                class="w_heding"
                            >
                                <?php echo htmlspecialchars($technology['title']); ?>
                            </a>

                            <p>
                                <?php
                                echo date(
                                    'M d, Y',
                                    strtotime($technology['created_at'])
                                );
                                ?>
                            </p>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <p>No Technology posts available.</p>

                <?php } ?>

            </div>


            <!-- GAMING -->
            <div
                class="tab-pane fade"
                id="gaming"
                role="tabpanel"
            >

                <?php if ($gamingResult && mysqli_num_rows($gamingResult) > 0) { ?>

                    <?php while ($gaming = mysqli_fetch_assoc($gamingResult)) { ?>

                        <div class="widget">

                            <a href="news-details.php?id=<?php echo $gaming['id']; ?>">

                                <?php if (!empty($gaming['image'])) { ?>

                                    <img
                                        src="<?php echo htmlspecialchars($gaming['image']); ?>"
                                        alt="<?php echo htmlspecialchars($gaming['title']); ?>"
                                    >

                                <?php } ?>

                            </a>

                            <a
                                href="news-details.php?id=<?php echo $gaming['id']; ?>"
                                class="w_heding"
                            >
                                <?php echo htmlspecialchars($gaming['title']); ?>
                            </a>

                            <p>
                                <?php
                                echo date(
                                    'M d, Y',
                                    strtotime($gaming['created_at'])
                                );
                                ?>
                            </p>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <p>No Gaming posts available.</p>

                <?php } ?>

            </div>

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