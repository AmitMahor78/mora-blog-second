
<?php
session_start();

require_once __DIR__ . '/includes/maintenance-check.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        // Use the same generic response for existing and
        // non-existing email addresses.
        $message = 'If an account exists with this email, '
                 . 'a password reset link will be sent. '
                 . 'Please check your inbox and spam folder.';

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email
             FROM registered_users
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            if ($user) {
                try {
                    // Generate a cryptographically secure token.
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);

                    mysqli_begin_transaction($conn);

                    // Invalidate earlier outstanding reset links.
                    $deleteStmt = mysqli_prepare(
                        $conn,
                        "DELETE FROM password_resets WHERE user_id = ?"
                    );

                    if (!$deleteStmt) {
                        throw new RuntimeException(
                            'Unable to prepare reset request.'
                        );
                    }

                    mysqli_stmt_bind_param(
                        $deleteStmt,
                        'i',
                        $user['id']
                    );

                    if (!mysqli_stmt_execute($deleteStmt)) {
                        mysqli_stmt_close($deleteStmt);
                        throw new RuntimeException(
                            'Unable to save reset request.'
                        );
                    }

                    mysqli_stmt_close($deleteStmt);

                    // Expiration is based on the database clock.
                    $insertStmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO password_resets
                         (user_id, token_hash, expires_at)
                         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))"
                    );

                    if (!$insertStmt) {
                        throw new RuntimeException(
                            'Unable to prepare reset token.'
                        );
                    }

                    mysqli_stmt_bind_param(
                        $insertStmt,
                        'is',
                        $user['id'],
                        $tokenHash
                    );

                    if (!mysqli_stmt_execute($insertStmt)) {
                        mysqli_stmt_close($insertStmt);
                        throw new RuntimeException(
                            'Unable to save reset token.'
                        );
                    }

                    mysqli_stmt_close($insertStmt);
                    mysqli_commit($conn);

                    // Dynamic base URL for localhost or live domain
                    $resetLink = getMoraSiteBaseUrl()
                        . '/reset-password.php?token='
                        . urlencode($token);

                    $safeName = htmlspecialchars(
                        $user['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $safeLink = htmlspecialchars(
                        $resetLink,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $body = "
                        <div style='font-family:Arial,sans-serif;
                                    line-height:1.6'>
                            <h2>Mora Blog Password Reset</h2>

                            <p>Hello {$safeName},</p>

                            <p>
                                We received a request to reset
                                your Mora Blog password.
                            </p>

                            <p>
                                <a href='{$safeLink}'
                                   style='display:inline-block;
                                          padding:12px 20px;
                                          background:#0d6efd;
                                          color:#ffffff;
                                          text-decoration:none;
                                          border-radius:5px'>
                                    Reset My Password
                                </a>
                            </p>

                            <p>
                                This link expires in 30 minutes
                                and can only be used once.
                            </p>

                            <p>
                                If you did not request this,
                                you can ignore this email.
                            </p>

                            <p>Mora Blog Team</p>
                        </div>
                    ";

                    if (!sendMoraEmail(
                        $user['email'],
                        'Mora Blog - Reset Your Password',
                        $body
                    )) {
                        error_log(
                            'Password reset email could not be sent.'
                        );
                    }

                } catch (Throwable $e) {
                  @mysqli_rollback($conn);

                    error_log(
                        'Password reset request error: '
                        . $e->getMessage()
                    );
                }
            }
        } else {
            error_log('Unable to prepare password reset lookup.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | Mora Blog</title>
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
            width: 100%;
            padding: 12px;
            margin: 12px 0 18px;
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
            overflow-wrap: anywhere;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
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
    <h2>Forgot Password?</h2>

    <p>
        Enter your registered email address to receive
        a secure password reset link.
    </p>

    <?php if ($message !== ''): ?>
        <div class="message <?= htmlspecialchars($messageType); ?>">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php">
        <label for="email">Email Address</label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your registered email"
            required
            maxlength="254"
            autocomplete="email"
            value="<?= htmlspecialchars(
                $_POST['email'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
        >

        <button type="submit">Send Reset Link</button>
    </form>

    <a class="back-link" href="login.php">
        Back to Login
    </a>
</div>

</body>
</html>
