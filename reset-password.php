
<?php
session_start();

require_once __DIR__ . '/includes/maintenance-check.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$message = '';
$messageType = 'error';
$token = $_POST['token'] ?? $_GET['token'] ?? '';
$validToken = false;
$userEmail = '';

if (
    !is_string($token) ||
    !preg_match('/\A[a-f0-9]{64}\z/', $token)
) {
    $token = '';
} else {
    $tokenHash = hash('sha256', $token);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (
            !is_string($password) ||
            !is_string($confirmPassword) ||
            $password === '' ||
            $confirmPassword === ''
        ) {
            $message = 'Please enter and confirm your new password.';
        } elseif (strlen($password) < 8) {
            $message = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $message = 'Passwords do not match.';
        } else {
            try {
                mysqli_begin_transaction($conn);

                // Lock the token row while checking and consuming it.
                $stmt = mysqli_prepare(
                    $conn,
                    "SELECT pr.id, pr.user_id, ru.email
                     FROM password_resets pr
                     INNER JOIN registered_users ru
                        ON ru.id = pr.user_id
                     WHERE pr.token_hash = ?
                       AND pr.expires_at > NOW()
                       AND pr.used_at IS NULL
                     LIMIT 1
                     FOR UPDATE"
                );

                if (!$stmt) {
                    throw new RuntimeException(
                        'Unable to validate reset token.'
                    );
                }

                mysqli_stmt_bind_param($stmt, 's', $tokenHash);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $reset = mysqli_fetch_assoc($result);

                mysqli_stmt_close($stmt);

                if (!$reset) {
                    mysqli_rollback($conn);
                    $message = 'This reset link is invalid, expired, '
                             . 'or already used. Please request a new link.';
                } else {
                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    if ($hashedPassword === false) {
                        throw new RuntimeException(
                            'Unable to secure the new password.'
                        );
                    }

                    $updateStmt = mysqli_prepare(
                        $conn,
                        "UPDATE registered_users
                         SET password = ?
                         WHERE id = ?"
                    );

                    if (!$updateStmt) {
                        throw new RuntimeException(
                            'Unable to prepare password update.'
                        );
                    }

                    mysqli_stmt_bind_param(
                        $updateStmt,
                        'si',
                        $hashedPassword,
                        $reset['user_id']
                    );

                    if (!mysqli_stmt_execute($updateStmt)) {
                        mysqli_stmt_close($updateStmt);
                        throw new RuntimeException(
                            'Unable to update password.'
                        );
                    }

                    mysqli_stmt_close($updateStmt);

                    $usedStmt = mysqli_prepare(
                        $conn,
                        "UPDATE password_resets
                         SET used_at = NOW()
                         WHERE id = ? AND used_at IS NULL"
                    );

                    if (!$usedStmt) {
                        throw new RuntimeException(
                            'Unable to invalidate reset token.'
                        );
                    }

                    mysqli_stmt_bind_param(
                        $usedStmt,
                        'i',
                        $reset['id']
                    );

                    if (
                        !mysqli_stmt_execute($usedStmt) ||
                        mysqli_stmt_affected_rows($usedStmt) !== 1
                    ) {
                        mysqli_stmt_close($usedStmt);
                        throw new RuntimeException(
                            'Unable to consume reset token.'
                        );
                    }

                    mysqli_stmt_close($usedStmt);

                    mysqli_commit($conn);

                    // Send a notification, never the new password.
                    $userEmail = $reset['email'];

                    try {
                        $notificationSent = sendMoraEmail(
                            $userEmail,
                            'Mora Blog - Password Changed',
                            '<p>Your Mora Blog password was reset '
                            . 'successfully.</p>'
                            . '<p>If you did not make this change, '
                            . 'please contact the administrator.</p>'
                        );

                        if (!$notificationSent) {
                            error_log(
                                'Password reset confirmation email failed.'
                            );
                        }
                    } catch (Throwable $mailError) {
                        error_log(
                            'Password reset confirmation failed: '
                            . $mailError->getMessage()
                        );
                    }

                    header('Location: login.php?reset=success');
                    exit;
                }
            } catch (Throwable $e) {
                @mysqli_rollback($conn);

                error_log(
                    'Password reset error: ' . $e->getMessage()
                );

                $message = 'Unable to reset password right now. '
                         . 'Please request a new reset link if needed.';
            }
        }
    }

    // Validate the token before showing the reset form.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM password_resets
         WHERE token_hash = ?
           AND expires_at > NOW()
           AND used_at IS NULL
         LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $tokenHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $validToken = mysqli_stmt_num_rows($stmt) === 1;

        mysqli_stmt_close($stmt);
    }

    if (!$validToken && $message === '') {
        $message = 'This reset link is invalid, expired, '
                 . 'or already used. Please request a new link.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | Mora Blog</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .reset-card {
            max-width: 480px;
            margin: 70px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
        }

        .reset-card h2 { margin-top: 0; }

        .reset-card input {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0 18px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .reset-card button {
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 5px;
            background: #0d6efd;
            color: white;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 5px;
            background: #f8d7da;
            color: #721c24;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="reset-card">
    <h2>Reset Your Password</h2>

    <?php if ($message !== ''): ?>
        <div class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($validToken): ?>
        <form action="reset-password.php" method="POST">
            <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars(
                    $token,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
            >

            <label for="password">New Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="confirm_password">Confirm New Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>

    <a class="back-link" href="login.php">Back to Login</a>
</div>

</body>
</html>
