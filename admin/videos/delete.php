<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($videoId <= 0) {
    header("Location: index.php?error=Invalid video ID");
    exit;
}


/*
 * First get the thumbnail path.
 */
$videoQuery = "
    SELECT id, thumbnail
    FROM videos
    WHERE id = $videoId
    LIMIT 1
";

$videoResult = mysqli_query($conn, $videoQuery);

if (!$videoResult || mysqli_num_rows($videoResult) === 0) {
    header("Location: index.php?error=Video not found");
    exit;
}

$video = mysqli_fetch_assoc($videoResult);


/*
 * Delete video from database.
 */
$deleteQuery = "
    DELETE FROM videos
    WHERE id = $videoId
";

if (mysqli_query($conn, $deleteQuery)) {

    /*
     * Delete only thumbnails uploaded
     * through our video upload system.
     */
    if (
        !empty($video['thumbnail']) &&
        strpos($video['thumbnail'], 'uploads/videos/') === 0
    ) {

        $thumbnailFile = "../../" . $video['thumbnail'];

        if (file_exists($thumbnailFile)) {
            unlink($thumbnailFile);
        }
    }

    header("Location: index.php?success=Video deleted successfully");
    exit;

} else {

    header(
        "Location: index.php?error=" .
        urlencode("Failed to delete video: " . mysqli_error($conn))
    );

    exit;
}
?>