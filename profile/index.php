<?php
// Keep the existing dashboard at /profile.php as the main profile page.
session_start();

if (!isset($_SESSION['registered_user_id'])) {
    header("Location: ../login.php");
    exit;
}

header("Location: ../profile.php");
exit;
