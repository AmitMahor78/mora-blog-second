<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../vendor/autoload.php';

// Global error storage for diagnostics
$GLOBALS['mora_last_mail_error'] = '';

if (!function_exists('getMoraMailerLastError')) {
    function getMoraMailerLastError(): string {
        return $GLOBALS['mora_last_mail_error'] ?? '';
    }
}

if (!function_exists('getMoraSiteBaseUrl')) {
    /**
     * Dynamically detects the site's base URL (protocol + domain + root folder)
     * Works seamlessly on localhost (e.g. http://localhost/mora-blog-second)
     * and live servers (e.g. https://mora-blog.infinityfreeapp.com).
     */
    function getMoraSiteBaseUrl(): string {
        if (empty($_SERVER['HTTP_HOST'])) {
            return 'http://localhost/mora-blog-second';
        }

        $isHttps = (
            (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
        );

        $protocol = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = dirname($scriptName);
        $dir = str_replace('\\', '/', $dir);

        // Strip subdirectories like /admin or /includes or /php
        $dir = preg_replace('~/+(admin|php|includes)(/.*)?$~i', '', $dir);
        $dir = rtrim($dir, '/');

        return $protocol . $host . $dir;
    }
}

function sendMoraEmail(
    string $to,
    string $subject,
    string $htmlBody,
    bool $debug = false,
    string &$debugOutput = null
): bool {
    $mail = new PHPMailer(true);

    try {
        // Always capture debug log internally so if an error occurs we have full visibility
        $debugBuffer = '';
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function ($str, $level) use (&$debugBuffer) {
            $debugBuffer .= "$level: $str\n";
        };

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'amitmahor613@gmail.com';
        $mail->Password   = 'lbhv alsd vsxg sitm';

        // InfinityFree free hosting ONLY supports Port 587 with STARTTLS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 25;

        // Bypass local SSL cert verification for shared hosting
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('amitmahor613@gmail.com', 'Mora Blog');
        $mail->addReplyTo('amitmahor613@gmail.com', 'Mora Blog Support');
        $mail->addAddress($to);

        // Transactional Headers
        $mail->XMailer = 'PHPMailer (Mora Blog)';
        $mail->addCustomHeader('Auto-Submitted', 'auto-generated');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $htmlBody)));

        $mail->send();

        if (isset($debugBuffer)) {
            $debugOutput = $debugBuffer;
        }

        $GLOBALS['mora_last_mail_error'] = '';
        return true;

    } catch (\Throwable $e) {
        $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
        $GLOBALS['mora_last_mail_error'] = $errorMessage;

        if (isset($debugBuffer)) {
            $debugBuffer .= "\n[FINAL ERROR]: " . $errorMessage;
            $debugOutput = $debugBuffer;
        }

        error_log('Mora Blog email error: ' . $errorMessage);
        return false;
    }
}
