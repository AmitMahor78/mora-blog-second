<?php
require_once __DIR__ . '/includes/maintenance-check.php';
require_once __DIR__ . '/includes/db.php';

// Fetch Site Settings for meta tags & title
$settingsQuery = "SELECT site_name, logo_path FROM site_settings WHERE id = 1 LIMIT 1";
$settingsResult = mysqli_query($conn, $settingsQuery);
$siteName = 'Mora';
if ($settingsResult && $row = mysqli_fetch_assoc($settingsResult)) {
    if (!empty($row['site_name'])) {
        $siteName = $row['site_name'];
    }
}

// Fetch About Settings
$aboutQuery = mysqli_query($conn, "SELECT * FROM about_settings WHERE id = 1 LIMIT 1");
$about = null;
if ($aboutQuery && mysqli_num_rows($aboutQuery) > 0) {
    $about = mysqli_fetch_assoc($aboutQuery);
}

// Fallback defaults
$pageTitle = !empty($about['page_title']) ? $about['page_title'] : 'About Us';
$breadcrumbTitle = !empty($about['breadcrumb_title']) ? $about['breadcrumb_title'] : 'About';
$heroHeadline = !empty($about['hero_headline']) ? $about['hero_headline'] : 'Mora connects people to inspiring content, reliable information, and modern experiences.';
$heroImage = !empty($about['hero_image']) ? $about['hero_image'] : 'images/about.jpg';
$mainStory = !empty($about['main_story']) ? $about['main_story'] : '';

// Build interactive tabs array
$tabs = [];
$tabConfigs = [
    ['key' => 'mission', 'title' => 'mission_title', 'content' => 'mission_content', 'default_title' => 'Mission'],
    ['key' => 'org', 'title' => 'organization_title', 'content' => 'organization_content', 'default_title' => 'Organization'],
    ['key' => 'resp', 'title' => 'responsibilities_title', 'content' => 'responsibilities_content', 'default_title' => 'Responsibilities'],
    ['key' => 'funding', 'title' => 'funding_title', 'content' => 'funding_content', 'default_title' => 'Funding'],
    ['key' => 'vision', 'title' => 'vision_title', 'content' => 'vision_content', 'default_title' => 'Lumens'],
    ['key' => 'dist', 'title' => 'distribution_title', 'content' => 'distribution_content', 'default_title' => 'Lumen Distribution'],
    ['key' => 'comm', 'title' => 'community_title', 'content' => 'community_content', 'default_title' => 'Direct Signup Program'],
    ['key' => 'jobs', 'title' => 'jobs_title', 'content' => 'jobs_content', 'default_title' => 'Jobs'],
    ['key' => 'team', 'title' => 'team_title', 'content' => 'team_content', 'default_title' => 'Team'],
];

foreach ($tabConfigs as $cfg) {
    $title = !empty($about[$cfg['title']]) ? $about[$cfg['title']] : $cfg['default_title'];
    $content = $about[$cfg['content']] ?? '';
    if (trim($content) !== '' || trim($title) !== '') {
        $tabs[] = [
            'id' => $cfg['key'],
            'title' => $title,
            'content' => $content
        ];
    }
}

// Fetch categories for Topics section
$categories = [];
$showTopics = !empty($about['show_topics']);
if ($showTopics) {
    $catRes = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC LIMIT 12");
    if ($catRes) {
        while ($c = mysqli_fetch_assoc($catRes)) {
            $categories[] = $c;
        }
    }
}
$topicsTitle = !empty($about['topics_title']) ? $about['topics_title'] : 'Topics';

// Fetch social links
$socialRes = mysqli_query($conn, "SELECT platform, url FROM social_links WHERE status = 'active' ORDER BY id ASC");
$socialLinks = [];
if ($socialRes) {
    while ($s = mysqli_fetch_assoc($socialRes)) {
        $socialLinks[$s['platform']] = $s['url'];
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
    <title><?= htmlspecialchars($pageTitle . ' | ' . $siteName, ENT_QUOTES, 'UTF-8') ?></title> 
    
    <!-- Theme style CSS --> 
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> 
    <link href="css/modern.css" rel="stylesheet"> 
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="background-transparent"> 
    
    <!-- Header Top Area -->
    <?php include "includes/header.php"; ?>
    <!-- Header Top Area -->  
    
    <!-- Main Header Area -->
    <header class="main_header_area" id="header"> 
       <div class="container">
            <div class="header_menu"> 
                <?php include "includes/navbar.php"; ?>
            </div>
        </div>
    </header>
    <!-- Main Header Area -->  
     
    <!-- Pages Header Area --> 
    <div class="container">
        <div class="pages_heder">
            <h2><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
            <ol class="breadcrumb">
                <li><a href="index.php">Home</a></li> 
                <li><a href="about.php" class="active"><?= htmlspecialchars($breadcrumbTitle, ENT_QUOTES, 'UTF-8') ?></a></li>
            </ol>
        </div>
    </div> 
    <!-- End Pages Header Area -->  
    
    <!-- About Content Area --> 
    <section class="about_content">
        <div class="container">
            <h2><?= nl2br(htmlspecialchars($heroHeadline, ENT_QUOTES, 'UTF-8')) ?></h2>

            <?php if (!empty($heroImage)): ?>
                <img
                    src="<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>"
                    style="border-radius: 8px; width: 100%; max-height: 540px; object-fit: cover;"
                >
            <?php endif; ?>

            <?php if (!empty($mainStory)): ?>
                <div style="margin-top: 25px;">
                    <?php
                    $paragraphs = preg_split('/(\r\n|\n|\r){2,}/', trim($mainStory));
                    foreach ($paragraphs as $p) {
                        if (trim($p) !== '') {
                            echo '<p>' . nl2br(htmlspecialchars(trim($p), ENT_QUOTES, 'UTF-8')) . '</p>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($tabs)): ?>
                <div class="mission_area row">
                    <div class="col-md-3"> 
                        <div class="nav tab_nav" role="tablist">
                            <?php foreach ($tabs as $index => $tab): ?>
                                <a
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    data-toggle="pill"
                                    href="#tab_<?= htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8') ?>"
                                    role="tab"
                                >
                                    <?= htmlspecialchars($tab['title'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="tab-content col-md-9" id="v-pills-tabContent">
                        <?php foreach ($tabs as $index => $tab): ?>
                            <div
                                class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                                id="tab_<?= htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8') ?>"
                                role="tabpanel"
                            >
                                <h4><?= htmlspecialchars($tab['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <?php
                                $tabParas = preg_split('/(\r\n|\n|\r){2,}/', trim($tab['content']));
                                foreach ($tabParas as $tp) {
                                    if (trim($tp) !== '') {
                                        echo '<p>' . nl2br(htmlspecialchars(trim($tp), ENT_QUOTES, 'UTF-8')) . '</p>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($showTopics && !empty($categories)): ?>
                <div class="topics_row row">
                    <div class="col-12">
                        <h2><?= htmlspecialchars($topicsTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-lg-4 col-md-6">
                            <a href="news.php?category=<?= (int)$cat['id'] ?>">
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section> 
    <!-- End About Content Area -->  
    
    <!-- Connect with us -->
    <section class="connect_with_us">
        <div class="container">
            <h2>Connect with us</h2>
            <ul class="contact_with_socail">
                <?php if (!empty($socialLinks['facebook'])): ?>
                    <li><a href="<?= htmlspecialchars($socialLinks['facebook'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook"></i></a></li>
                <?php endif; ?>
                <?php if (!empty($socialLinks['twitter'])): ?>
                    <li><a href="<?= htmlspecialchars($socialLinks['twitter'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-twitter"></i></a></li>
                <?php endif; ?>
                <?php if (!empty($socialLinks['linkedin'])): ?>
                    <li><a href="<?= htmlspecialchars($socialLinks['linkedin'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-linkedin"></i></a></li>
                <?php endif; ?>
                <?php if (!empty($socialLinks['instagram'])): ?>
                    <li><a href="<?= htmlspecialchars($socialLinks['instagram'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram"></i></a></li>
                <?php endif; ?>
                <?php if (!empty($socialLinks['google'])): ?>
                    <li><a href="<?= htmlspecialchars($socialLinks['google'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-google-plus"></i></a></li>
                <?php endif; ?>
                <?php if (!empty($socialLinks['pinterest'])): ?>
                    <li><a href="<?= htmlspecialchars($socialLinks['pinterest'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-pinterest"></i></a></li>
                <?php endif; ?>
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
    
    <!-- jQuery v3.3.1 -->
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
