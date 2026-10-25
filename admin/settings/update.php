
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

$siteName = trim($_POST['site_name'] ?? '');
$description = trim($_POST['site_description'] ?? '');
$location = trim($_POST['location'] ?? '');
$workingDays = trim($_POST['working_days'] ?? '');
$closingDay = trim($_POST['closing_day'] ?? '');
$copyrightYear = trim($_POST['copyright_year'] ?? '');
$developerName = trim($_POST['developer_name'] ?? '');
$developerUrl = trim($_POST['developer_url'] ?? '');
$status = $_POST['status'] ?? '';

$maintenanceMode = $_POST['maintenance_mode'] ?? '0';

if (!in_array((string)$maintenanceMode, ['0', '1'], true)) {
    $_SESSION['settings_message'] = 'Invalid maintenance mode value.';
    header("Location: index.php");
    exit;
}

$maintenanceMode = (int)$maintenanceMode;

// Keep the current logo unless a new valid image is uploaded.
$logoPath = '';

$currentLogoStmt = $conn->prepare(
    "SELECT logo_path FROM site_settings WHERE id = 1 LIMIT 1"
);
$currentLogoStmt->execute();
$currentLogo = $currentLogoStmt->get_result()->fetch_assoc();
$currentLogoStmt->close();

$logoPath = $currentLogo['logo_path'] ?? '';

$uploadDirectory = __DIR__ . '/../../uploads/site-settings/';
$uploadError = $_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE;

if ($uploadError !== UPLOAD_ERR_NO_FILE) {

    if ($uploadError !== UPLOAD_ERR_OK) {
        $_SESSION['settings_message'] = 'Logo upload failed. Please try again.';
        header("Location: index.php");
        exit;
    }

    if (($_FILES['logo']['size'] ?? 0) > 2 * 1024 * 1024) {
        $_SESSION['settings_message'] = 'Logo must be 2 MB or smaller.';
        header("Location: index.php");
        exit;
    }

    $tmpFile = $_FILES['logo']['tmp_name'];

    if (!is_uploaded_file($tmpFile)) {
        $_SESSION['settings_message'] = 'Invalid uploaded file.';
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
        $_SESSION['settings_message'] = 'Please upload a valid JPG, PNG, WEBP or GIF image.';
        header("Location: index.php");
        exit;
    }

    if (!is_dir($uploadDirectory) &&
        !mkdir($uploadDirectory, 0755, true) &&
        !is_dir($uploadDirectory)) {
        $_SESSION['settings_message'] = 'Could not create the logo upload directory.';
        header("Location: index.php");
        exit;
    }

    $fileName = 'logo_' . bin2hex(random_bytes(16))
              . '.' . $allowedTypes[$mimeType];

    if (!move_uploaded_file($tmpFile, $uploadDirectory . $fileName)) {
        $_SESSION['settings_message'] = 'Could not save the uploaded logo.';
        header("Location: index.php");
        exit;
    }

    $logoPath = 'uploads/site-settings/' . $fileName;
}


if ($siteName === '') {
    $_SESSION['settings_message'] = 'Website name is required.';
    header("Location: index.php");
    exit;
}

if (!in_array($status, ['active', 'inactive'], true)) {
    $_SESSION['settings_message'] = 'Invalid website status.';
    header("Location: index.php");
    exit;
}

if (
    $copyrightYear !== '' &&
    (!ctype_digit($copyrightYear) ||
     (int)$copyrightYear < 1900 ||
     (int)$copyrightYear > 2200)
) {
    $_SESSION['settings_message'] = 'Please enter a valid copyright year.';
    header("Location: index.php");
    exit;
}

if ($developerUrl !== '') {
    $urlParts = parse_url($developerUrl);

    if (
        !filter_var($developerUrl, FILTER_VALIDATE_URL) ||
        !in_array(strtolower($urlParts['scheme'] ?? ''), ['http', 'https'], true)
    ) {
        $_SESSION['settings_message'] = 'Please enter a valid HTTP or HTTPS developer URL.';
        header("Location: index.php");
        exit;
    }
}

$check = $conn->prepare("SELECT id FROM site_settings WHERE id = 1 LIMIT 1");
$check->execute();
$exists = $check->get_result()->fetch_assoc();
$check->close();

if (!$exists) {
    $_SESSION['settings_message'] = 'Settings record not found.';
    header("Location: index.php");
    exit;
}

$sql = "UPDATE site_settings
        SET site_name = ?,
            site_description = ?,
            location = ?,
            working_days = ?,
            closing_day = ?,
            copyright_year = ?,
            developer_name = ?,
            developer_url = ?,
            status = ?,
            logo_path = ?,
            maintenance_mode = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssssssi",
    $siteName,
    $description,
    $location,
    $workingDays,
    $closingDay,
    $copyrightYear,
    $developerName,
    $developerUrl,
    $status,
    $logoPath,
    $maintenanceMode
);

if ($stmt->execute()) {
    $_SESSION['settings_message'] = 'Site settings updated successfully.';
} else {
    $_SESSION['settings_message'] = 'Unable to update settings. Please try again.';
}

$stmt->close();

header("Location: index.php");
exit;
