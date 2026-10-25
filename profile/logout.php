<?php
session_start();

// Log out the registered customer session without changing other users' records.
unset(
    $_SESSION['registered_user_id'],
    $_SESSION['registered_user_name'],
    $_SESSION['registered_user_email'],
    $_SESSION['csrf_token']
);

session_regenerate_id(true);
header("Location: ../login.php");
exit;
