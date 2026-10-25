<?php

include "includes/db.php";

/* =========================================================
   PAGINATION
========================================================= */

$postsPerPage = 5;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $postsPerPage;


/* =========================================================
   TOTAL TRAVEL POSTS
========================================================= */

$countQuery = "
    SELECT COUNT(*) AS total
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
      AND c.slug = 'travel'
";

$countResult = mysqli_query($conn, $countQuery);

$totalPosts = 0;

if ($countResult) {

    $countRow = mysqli_fetch_assoc($countResult);

    $totalPosts = (int) $countRow['total'];
}

$totalPages = ceil($totalPosts / $postsPerPage);


/* =========================================================
   FETCH TRAVEL POSTS
========================================================= */

$travelQuery = "
    SELECT 
        p.id,
        p.title,
        p.slug,
        p.excerpt,
        p.image,
        p.created_at,
        c.name AS category_name,
        a.name AS author_name
    FROM posts p
    LEFT JOIN categories c 
        ON p.category_id = c.id
    LEFT JOIN users a 
        ON p.author_id = a.id
    WHERE p.status = 'published'
      AND c.slug = 'travel'
    ORDER BY p.created_at DESC
    LIMIT $postsPerPage OFFSET $offset
";

$travelResult = mysqli_query($conn, $travelQuery);


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
    GROUP BY c.id, c.name, c.slug
    ORDER BY c.id ASC
";

$categoryResult = mysqli_query($conn, $categoryQuery);


/* Total Published Posts */

$totalPostsQuery = "
    SELECT COUNT(*) AS total
    FROM posts
    WHERE status = 'published'
";

$totalPostsResult = mysqli_query($conn, $totalPostsQuery);

$allNewsCount = 0;

if ($totalPostsResult) {

    $totalPostsRow = mysqli_fetch_assoc($totalPostsResult);

    $allNewsCount = (int) $totalPostsRow['total'];
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

$socialWidgetQuery = "
    SELECT 
        platform,
        url
    FROM social_links
    WHERE status = 'active'
    ORDER BY id ASC
";

$socialWidgetResult = mysqli_query($conn, $socialWidgetQuery);

$socialWidgetLinks = [];

if ($socialWidgetResult) {
    while ($social = mysqli_fetch_assoc($socialWidgetResult)) {
        $socialWidgetLinks[$social['platform']] = $social['url'];
    }
}


/* =========================================================
   TECHNOLOGY & GAMING TABS
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
    LIMIT 3
";

$technologyResult = mysqli_query($conn, $technologyQuery);


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
    LIMIT 3
";

$gamingResult = mysqli_query($conn, $gamingQuery);




/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$searchResult = null;

if ($search !== '') {

    $searchSafe = mysqli_real_escape_string($conn, $search);

    $searchQuery = "
        SELECT
            p.id,
            p.title,
            p.image,
            p.created_at
        FROM posts p
        WHERE p.status = 'published'
          AND (
              p.title LIKE '%$searchSafe%'
              OR p.excerpt LIKE '%$searchSafe%'
              OR p.content LIKE '%$searchSafe%'
          )
        ORDER BY p.created_at DESC
    ";

    $searchResult = mysqli_query($conn, $searchQuery);
}



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

$siteSettingsResult = mysqli_query($conn, $siteSettingsQuery);

$siteSettings = null;

if ($siteSettingsResult && mysqli_num_rows($siteSettingsResult) > 0) {
    $siteSettings = mysqli_fetch_assoc($siteSettingsResult);
}


?>
