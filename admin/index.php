<?php
session_start();

require_once __DIR__ . "/../includes/db.php";

// Allow only an authenticated admin to access the admin dashboard.
if (
    isset($_SESSION['user_id'], $_SESSION['user_role']) &&
    $_SESSION['user_role'] === 'admin'
) {
    header("Location: dashboard.php");
    exit;
}

// Remove an existing non-admin CMS session.
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin') {
    unset(
        $_SESSION['user_id'],
        $_SESSION['user_name'],
        $_SESSION['user_email'],
        $_SESSION['user_role']
    );
}

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = $result ? mysqli_fetch_assoc($result) : null;

            mysqli_stmt_close($stmt);

            // Only users with the admin role can sign in here.
            if (
                $user &&
                isset($user['password'], $user['role']) &&
                $user['role'] === 'admin' &&
                password_verify($password, $user['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = 'admin';

                header("Location: dashboard.php");
                exit;
            }

            // Same message for an unknown email, wrong password,
            // or a non-admin account.
            $error = "Invalid email or password.";
        } else {
            $error = "Unable to process login right now. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Mora CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login_container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login_box {
            background: #fff;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.10);
        }

        .login_box h2 {
            text-align: center;
            margin-bottom: 8px;
            color: #222;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }

        .form_group {
            margin-bottom: 18px;
        }

        .form_group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #333;
        }

        .form_group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }

        .form_group input:focus {
            border-color: #007bff;
        }

        .login_btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .login_btn:hover {
            background: #0056b3;
        }

        .error_message {
            background: #ffe5e5;
            color: #c00;
            padding: 10px;
            margin-bottom: 18px;
            border-radius: 5px;
            text-align: center;
        }

        .back_home {
            text-align: center;
            margin-top: 20px;
        }

        .back_home a {
            color: #007bff;
            text-decoration: none;
        }

        .back_home a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login_container">
        <div class="login_box">
            <h2>Mora CMS</h2>

            <p class="subtitle">Administrator Login</p>

            <?php if ($error !== ""): ?>
                <div class="error_message" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form_group">
                    <label for="email">Admin Email</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter admin email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form_group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter admin password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="login_btn">
                    Login as Admin
                </button>
            </form>

            <div class="back_home">
                <a href="../index.php">← Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
