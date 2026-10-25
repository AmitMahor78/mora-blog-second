
<?php
require_once __DIR__ . '/includes/maintenance-check.php';
require_once __DIR__ . '/includes/db.php';

$message = "";
$messageType = "danger";

$token = trim($_GET['token'] ?? '');

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {

    $message = "Invalid verification link. Please check the link in your email.";

} else {

    // Hash the token before comparing it with the database.
    $tokenHash = hash('sha256', $token);

    $sql = "
        SELECT id
        FROM registered_users
        WHERE verification_token_hash = ?
          AND is_verified = 0
          AND verification_expires_at > NOW()
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        $message = "Something went wrong. Please try again later.";

    } else {

        mysqli_stmt_bind_param($stmt, "s", $tokenHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $userId);

        $tokenFound = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$tokenFound) {

            $message = "This verification link is invalid, expired, or already used. Please register again or request a new verification link.";

        } else {

            // Verify the account and invalidate the token.
            $updateSql = "
                UPDATE registered_users
                SET is_verified = 1,
                    verification_token_hash = NULL,
                    verification_expires_at = NULL
                WHERE id = ?
                  AND is_verified = 0
            ";

            $updateStmt = mysqli_prepare($conn, $updateSql);

            if (!$updateStmt) {

                $message = "Unable to verify your account right now. Please try again.";

            } else {

                mysqli_stmt_bind_param($updateStmt, "i", $userId);

                if (mysqli_stmt_execute($updateStmt)
                    && mysqli_stmt_affected_rows($updateStmt) === 1) {

                    $message = "Your email has been verified successfully! You can now log in to Mora Blog.";
                    $messageType = "success";

                } else {

                    $message = "Your account could not be verified. The link may already have been used.";
                }

                mysqli_stmt_close($updateStmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | Mora Blog</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <section class="login_area">
        <div class="container">
            <div class="login_inner">

                <h2>Email Verification</h2>

                <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>

                <?php if ($messageType === "success") { ?>
                    <p>
                        <a href="login.php" class="defult_btn">
                            Go to Login
                        </a>
                    </p>
                <?php } else { ?>
                    <p>
                        <a href="register.php" class="defult_btn">
                            Back to Registration
                        </a>
                    </p>
                    <p>
                        <a href="login.php">Go to Login</a>
                    </p>
                <?php } ?>

            </div>
        </div>
    </section>

</body>
</html>
