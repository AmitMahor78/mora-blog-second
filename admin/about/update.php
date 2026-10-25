<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$pageTitle = trim($_POST['page_title'] ?? 'About Us');
$breadcrumbTitle = trim($_POST['breadcrumb_title'] ?? 'About');
$heroHeadline = trim($_POST['hero_headline'] ?? '');
$mainStory = trim($_POST['main_story'] ?? '');

$missionTitle = trim($_POST['mission_title'] ?? 'Mission');
$missionContent = trim($_POST['mission_content'] ?? '');

$orgTitle = trim($_POST['organization_title'] ?? 'Organization');
$orgContent = trim($_POST['organization_content'] ?? '');

$respTitle = trim($_POST['responsibilities_title'] ?? 'Responsibilities');
$respContent = trim($_POST['responsibilities_content'] ?? '');

$fundingTitle = trim($_POST['funding_title'] ?? 'Funding');
$fundingContent = trim($_POST['funding_content'] ?? '');

$visionTitle = trim($_POST['vision_title'] ?? 'Lumens');
$visionContent = trim($_POST['vision_content'] ?? '');

$distTitle = trim($_POST['distribution_title'] ?? 'Lumen Distribution');
$distContent = trim($_POST['distribution_content'] ?? '');

$commTitle = trim($_POST['community_title'] ?? 'Direct Signup Program');
$commContent = trim($_POST['community_content'] ?? '');

$jobsTitle = trim($_POST['jobs_title'] ?? 'Jobs');
$jobsContent = trim($_POST['jobs_content'] ?? '');

$teamTitle = trim($_POST['team_title'] ?? 'Team');
$teamContent = trim($_POST['team_content'] ?? '');

$showTopics = isset($_POST['show_topics']) && (int)$_POST['show_topics'] === 1 ? 1 : 0;
$topicsTitle = trim($_POST['topics_title'] ?? 'Topics');

if ($pageTitle === '') {
    $pageTitle = 'About Us';
}

if ($heroHeadline === '') {
    $_SESSION['about_message'] = 'Please enter a main headline for the About page.';
    $_SESSION['about_message_type'] = 'error';
    header("Location: index.php");
    exit;
}

// Fetch current image from DB
$currentStmt = $conn->prepare("SELECT hero_image FROM about_settings WHERE id = 1 LIMIT 1");
$currentStmt->execute();
$currentRes = $currentStmt->get_result()->fetch_assoc();
$currentStmt->close();

$heroImage = $currentRes['hero_image'] ?? 'images/about.jpg';

// Process Image Upload
$uploadError = $_FILES['hero_image']['error'] ?? UPLOAD_ERR_NO_FILE;

if ($uploadError !== UPLOAD_ERR_NO_FILE) {
    if ($uploadError !== UPLOAD_ERR_OK) {
        $_SESSION['about_message'] = 'Image upload failed. Please try again.';
        $_SESSION['about_message_type'] = 'error';
        header("Location: index.php");
        exit;
    }

    if (($_FILES['hero_image']['size'] ?? 0) > 3 * 1024 * 1024) {
        $_SESSION['about_message'] = 'Uploaded image must be 3 MB or smaller.';
        $_SESSION['about_message_type'] = 'error';
        header("Location: index.php");
        exit;
    }

    $tmpFile = $_FILES['hero_image']['tmp_name'];

    if (!is_uploaded_file($tmpFile)) {
        $_SESSION['about_message'] = 'Invalid file uploaded.';
        $_SESSION['about_message_type'] = 'error';
        header("Location: index.php");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpFile);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    if (!isset($allowedTypes[$mimeType]) || @getimagesize($tmpFile) === false) {
        $_SESSION['about_message'] = 'Please upload a valid JPG, PNG, WEBP, or GIF image.';
        $_SESSION['about_message_type'] = 'error';
        header("Location: index.php");
        exit;
    }

    $uploadDir = __DIR__ . '/../../uploads/about/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = 'about_' . bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($tmpFile, $destination)) {
        $_SESSION['about_message'] = 'Failed to save the uploaded image.';
        $_SESSION['about_message_type'] = 'error';
        header("Location: index.php");
        exit;
    }

    $heroImage = 'uploads/about/' . $newFileName;
}

// Update Database
$updateSql = "UPDATE about_settings SET
    page_title = ?,
    breadcrumb_title = ?,
    hero_headline = ?,
    hero_image = ?,
    main_story = ?,
    mission_title = ?,
    mission_content = ?,
    organization_title = ?,
    organization_content = ?,
    responsibilities_title = ?,
    responsibilities_content = ?,
    funding_title = ?,
    funding_content = ?,
    vision_title = ?,
    vision_content = ?,
    distribution_title = ?,
    distribution_content = ?,
    community_title = ?,
    community_content = ?,
    jobs_title = ?,
    jobs_content = ?,
    team_title = ?,
    team_content = ?,
    show_topics = ?,
    topics_title = ?,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1";

$stmt = $conn->prepare($updateSql);

if (!$stmt) {
    $_SESSION['about_message'] = 'Database error: ' . $conn->error;
    $_SESSION['about_message_type'] = 'error';
    header("Location: index.php");
    exit;
}

$stmt->bind_param(
    "sssssssssssssssssssssssis",
    $pageTitle,
    $breadcrumbTitle,
    $heroHeadline,
    $heroImage,
    $mainStory,
    $missionTitle,
    $missionContent,
    $orgTitle,
    $orgContent,
    $respTitle,
    $respContent,
    $fundingTitle,
    $fundingContent,
    $visionTitle,
    $visionContent,
    $distTitle,
    $distContent,
    $commTitle,
    $commContent,
    $jobsTitle,
    $jobsContent,
    $teamTitle,
    $teamContent,
    $showTopics,
    $topicsTitle
);

if ($stmt->execute()) {
    $_SESSION['about_message'] = 'About page content updated successfully!';
    $_SESSION['about_message_type'] = 'success';
} else {
    $_SESSION['about_message'] = 'Unable to update About page: ' . $stmt->error;
    $_SESSION['about_message_type'] = 'error';
}

$stmt->close();
header("Location: index.php");
exit;
