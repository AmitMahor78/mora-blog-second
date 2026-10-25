<?php

include __DIR__ . "/includes/db.php";

require_once __DIR__ . '/includes/maintenance-check.php';

// Default message
$message = "";
$messageType = "";

// Check form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get email
    $email = trim($_POST['email'] ?? '');

    // Validate email
    if (empty($email)) {

        $message = "Please enter your email address.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        // Check if email already exists
        $checkQuery = "
            SELECT id, status
            FROM subscribers
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $checkQuery);

        if (!$stmt) {

            $message = "Something went wrong. Please try again.";
            $messageType = "error";

        } else {

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {

                $subscriber = mysqli_fetch_assoc($result);

                // Already active
                if ($subscriber['status'] === 'active') {

                    $message = "You are already subscribed!";
                    $messageType = "info";

                } else {

                    // Reactivate inactive subscriber
                    $updateQuery = "
                        UPDATE subscribers
                        SET status = 'active'
                        WHERE id = ?
                    ";

                    $updateStmt = mysqli_prepare($conn, $updateQuery);

                    if ($updateStmt) {

                        mysqli_stmt_bind_param(
                            $updateStmt,
                            "i",
                            $subscriber['id']
                        );

                        if (mysqli_stmt_execute($updateStmt)) {

                            $message = "Welcome back! Your subscription has been activated.";
                            $messageType = "success";

                        } else {

                            $message = "Unable to activate your subscription.";
                            $messageType = "error";
                        }

                        mysqli_stmt_close($updateStmt);

                    } else {

                        $message = "Something went wrong. Please try again.";
                        $messageType = "error";
                    }
                }

            } else {

                // Insert new subscriber
                $insertQuery = "
                    INSERT INTO subscribers
                    (email, status)
                    VALUES (?, 'active')
                ";

                $insertStmt = mysqli_prepare($conn, $insertQuery);

                if ($insertStmt) {

                    mysqli_stmt_bind_param(
                        $insertStmt,
                        "s",
                        $email
                    );

                    if (mysqli_stmt_execute($insertStmt)) {

                        $message = "Thank you for subscribing!";
                        $messageType = "success";

                    } else {

                        $message = "Unable to subscribe. Please try again.";
                        $messageType = "error";
                    }

                    mysqli_stmt_close($insertStmt);

                } else {

                    $message = "Something went wrong. Please try again.";
                    $messageType = "error";
                }
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Subscription</title>

    <style>

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .subscription-message {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: #ffffff;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .subscription-message h2 {
            margin-bottom: 15px;
        }

        .subscription-message p {
            margin-bottom: 25px;
            font-size: 16px;
        }

        .success {
            color: #28a745;
        }

        .error {
            color: #dc3545;
        }

        .info {
            color: #007bff;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #333;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background: #555;
        }

    </style>

</head>

<body>

    <div class="subscription-message">

        <?php if ($messageType === "success") { ?>

            <h2 class="success">
                Subscription Successful!
            </h2>

        <?php } elseif ($messageType === "info") { ?>

            <h2 class="info">
                Already Subscribed
            </h2>

        <?php } else { ?>

            <h2 class="error">
                Subscription Error
            </h2>

        <?php } ?>


        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>


        <a
            href="index.php"
            class="back-button"
        >
            Back to Home
        </a>

    </div>

</body>

</html>