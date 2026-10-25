<?php
session_start();


if (
    !isset($_SESSION['user_id'], $_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../includes/db.php";
require_once __DIR__ . "/../../includes/mailer.php";


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, status, can_post
     FROM registered_users
     WHERE id = ?"
);

if (!$stmt) {
    die("Unable to load user details.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: index.php");
    exit;
}

$status = $user['status'];
$canPost = (int)($user['can_post'] ?? 0);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $canPost = isset($_POST['can_post']) && (int)$_POST['can_post'] === 1 ? 1 : 0;
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Please select a valid status.";
    } elseif ($newPassword !== '' || $confirmPassword !== '') {
        if ($newPassword === '' || $confirmPassword === '') {
            $error = "Enter and confirm the new password.";
        } elseif (strlen($newPassword) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        }
    }

    if ($error === '') {
        mysqli_begin_transaction($conn);

        try {
            // Update account status and posting permission.
            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE registered_users SET status = ?, can_post = ? WHERE id = ?"
            );

            if (!$updateStmt) {
                throw new Exception("Unable to prepare status update.");
            }

            mysqli_stmt_bind_param($updateStmt, "sii", $status, $canPost, $id);

            if (!mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                throw new Exception("Unable to update account details.");
            }

            mysqli_stmt_close($updateStmt);

            // Update password only when a new password was entered.
            if ($newPassword !== '') {
                $hashedPassword = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

                $passwordStmt = mysqli_prepare(
                    $conn,
                    "UPDATE registered_users SET password = ? WHERE id = ?"
                );

                if (!$passwordStmt) {
                    throw new Exception("Unable to prepare password update.");
                }

                mysqli_stmt_bind_param(
                    $passwordStmt,
                    "si",
                    $hashedPassword,
                    $id
                );

                if (!mysqli_stmt_execute($passwordStmt)) {
                    mysqli_stmt_close($passwordStmt);
                    throw new Exception("Unable to update password.");
                }

                mysqli_stmt_close($passwordStmt);
            }

            mysqli_commit($conn);

            $statusChanged = ($status !== $user['status']);
            $postingChanged = ($canPost !== (int)$user['can_post']);
            $passwordChanged = ($newPassword !== '');

            $mailSent = true;

            if ($statusChanged || $postingChanged || $passwordChanged) {
                $changes = [];

                if ($statusChanged) {
                    $safeStatus = htmlspecialchars(
                        $status,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $changes[] = "<p>Your account status is now: <strong>{$safeStatus}</strong>.</p>";
                }

                if ($postingChanged) {
                    $permStatus = $canPost ? "Enabled (You can now create and submit blog posts)" : "Disabled (Posting permission revoked)";
                    $changes[] = "<p>Your post submission permission is now: <strong>{$permStatus}</strong>.</p>";
                }

    if ($passwordChanged) {
        $changes[] = "
            <p>Your account password has been changed by
            the administrator.</p>
            <p>For your security, the new password has not
            been included in this email.</p>
        ";
    }

    $safeName = htmlspecialchars(
        $user['name'],
        ENT_QUOTES,
        'UTF-8'
    );

    $body = "
        <div style='font-family:Arial,sans-serif;line-height:1.6'>
            <h2>Mora Blog Account Update</h2>
            <p>Hello {$safeName},</p>
            <p>Your account has been updated.</p>
            " . implode("\n", $changes) . "
            <p>If you did not expect this change,
            please contact the administrator.</p>
            <p>Regards,<br>Mora Blog Team</p>
        </div>
    ";

    $mailSent = sendMoraEmail(
        $user['email'],
        'Mora Blog - Account Update',
        $body
    );
}

header(
    "Location: index.php?success=updated&mail=" .
    ($mailSent ? 'sent' : 'failed')
);
exit;
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = "Unable to update user. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Registered User | Mora Blog</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .topbar {
            margin-left: 260px;
            padding: 20px 25px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        .topbar h2 { margin: 0; font-size: 23px; }

        .container {
            margin-left: 260px;
            padding: 25px;
        }

        .form-card {
            max-width: 650px;
            padding: 25px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 15px;
        }

        input[readonly] {
            background: #e9ecef;
            color: #555;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save { background: #0d6efd; }
        .btn-back { background: #6c757d; }

        .error {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 5px;
            background: #f8d7da;
            color: #842029;
        }

        @media (max-width: 768px) {
            .topbar, .container { margin-left: 220px; }
        }

        @media (max-width: 480px) {
            .topbar, .container {
                margin-left: 200px;
                padding: 15px 10px;
            }

            .form-card { padding: 18px; }
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<header class="topbar">
    <h2>Edit Registered User</h2>
</header>

<main class="container">
    <div class="form-card">
        <h3>Update User Status</h3>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int)$id; ?>" method="POST">

            <div class="form-group">
                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    value="<?= htmlspecialchars($user['name']); ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    value="<?= htmlspecialchars($user['email']); ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="status">Account Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>
                        Active
                    </option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : ''; ?>>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="can_post">Blog Posting Permission</label>
                <select id="can_post" name="can_post" required>
                    <option value="1" <?= $canPost === 1 ? 'selected' : ''; ?>>
                        Allowed (Enabled) — User can write and submit blog posts
                    </option>
                    <option value="0" <?= $canPost === 0 ? 'selected' : ''; ?>>
                        Not Allowed (Disabled) — User cannot write or submit posts
                    </option>
                </select>
                <small style="display:block; margin-top:6px; color:#64748b; font-size:12.5px;">
                    Controls whether this user has permission to create and publish posts from their profile dashboard.
                </small>
            </div>
<div class="form-group">
    <label for="new_password">New Password</label>
    <input
        type="password"
        id="new_password"
        name="new_password"
        placeholder="Leave blank to keep current password"
        minlength="8"
        autocomplete="new-password"
    >
</div>

<div class="form-group">
    <label for="confirm_password">Confirm New Password</label>
    <input
        type="password"
        id="confirm_password"
        name="confirm_password"
        placeholder="Re-enter new password"
        minlength="8"
        autocomplete="new-password"
    >
</div>

            <button type="submit" class="btn btn-save">Update Status</button>
            <a href="index.php" class="btn btn-back">Cancel</a>

        </form>
    </div>
</main>
</body>
</html>
