<?php
/**
 * Mora Blog - Email Diagnostic & Test Utility
 * Use this file to test email delivery and diagnose SMTP configuration issues.
 */

require_once __DIR__ . '/includes/mailer.php';

$recipient = trim($_POST['to'] ?? ($_GET['to'] ?? 'mahorsahab244@gmail.com'));
$triggerSend = isset($_POST['send_test']) || isset($_GET['auto']);
$debugOutput = '';
$sendResult = null;
$statusMessage = '';

if ($triggerSend) {
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $sendResult = false;
        $statusMessage = 'Invalid email address provided.';
    } else {
        $baseUrl = getMoraSiteBaseUrl();
        $testSubject = 'Hello from Mora Blog';
        $testBody = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 580px; margin: 0 auto; padding: 25px; border: 1px solid #e0e0e0; border-radius: 8px; background:#ffffff;'>
                <h2 style='color: #2c3e50; margin-top: 0;'>Welcome to Mora Blog</h2>
                <p>Hello,</p>
                <p>This is a verification message to confirm that your email service is operational and messages are delivered securely.</p>
                <div style='background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0; font-size: 14px;'>
                    <p style='margin: 5px 0;'><strong>Platform:</strong> Mora Blog Official</p>
                    <p style='margin: 5px 0;'><strong>Date:</strong> " . date('F j, Y, g:i a') . "</p>
                </div>
                <p>No further action is required on your part.</p>
                <p style='color: #888888; font-size: 12px; margin-top: 25px; border-top: 1px solid #eeeeee; padding-top: 15px;'>
                    &copy; " . date('Y') . " Mora Blog. All rights reserved.
                </p>
            </div>
        ";

        $sendResult = sendMoraEmail($recipient, $testSubject, $testBody, true, $debugOutput);

        if ($sendResult) {
            $statusMessage = "Test email sent successfully to {$recipient}!";
        } else {
            $statusMessage = "Failed to send email. " . getMoraMailerLastError();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mora Blog - Email Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .debug-box { background: #1e1e1e; color: #a9b7c6; font-family: monospace; font-size: 12px; max-height: 350px; overflow-y: auto; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body class="py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0 text-primary">Mora Blog - Email Diagnostics</h3>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Home</a>
                </div>
                <p class="text-muted">Use this tool to test live email delivery and view SMTP connection details.</p>

                <div class="bg-light p-3 rounded mb-4">
                    <h6 class="text-uppercase text-secondary fw-bold mb-2" style="font-size: 12px; letter-spacing: 0.5px;">Environment Information</h6>
                    <div class="row g-2 small">
                        <div class="col-sm-6"><strong>Detected Base URL:</strong> <code><?php echo htmlspecialchars(getMoraSiteBaseUrl()); ?></code></div>
                        <div class="col-sm-6"><strong>HTTP Host:</strong> <code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A'); ?></code></div>
                        <div class="col-sm-6"><strong>SMTP Host:</strong> <code>smtp.gmail.com:587 (STARTTLS)</code></div>
                        <div class="col-sm-6"><strong>Sender Account:</strong> <code>amitmahor613@gmail.com</code></div>
                        <div class="col-sm-6"><strong>PHP Version:</strong> <code><?php echo PHP_VERSION; ?></code></div>
                        <div class="col-sm-6"><strong>OpenSSL Extension:</strong> <code><?php echo extension_loaded('openssl') ? 'Enabled' : 'Disabled'; ?></code></div>
                    </div>
                </div>

                <?php if ($sendResult === true): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <div>
                            <h5 class="alert-heading mb-1">SUCCESS</h5>
                            <p class="mb-0"><?php echo htmlspecialchars($statusMessage); ?></p>
                            <small class="text-muted">Please check your inbox (and <strong>Spam / Junk</strong> folder if not in Inbox).</small>
                        </div>
                    </div>
                <?php elseif ($sendResult === false): ?>
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading mb-1">FAILED TO SEND</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($statusMessage); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="test-email.php">
                    <div class="mb-3">
                        <label for="to" class="form-label fw-semibold">Recipient Email Address:</label>
                        <input type="email" class="form-control form-control-lg" id="to" name="to" value="<?php echo htmlspecialchars($recipient); ?>" required>
                        <div class="form-text">Enter any email address to verify delivery (Gmail, Outlook, Yahoo, etc.).</div>
                    </div>
                    <button type="submit" name="send_test" value="1" class="btn btn-primary btn-lg w-100">
                        Send Test Email Now
                    </button>
                </form>

                <?php if (!empty($debugOutput)): ?>
                    <div class="mt-4">
                        <h6 class="fw-bold mb-2">SMTP Handshake Debug Log:</h6>
                        <pre class="debug-box"><?php echo htmlspecialchars($debugOutput); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
