<?php

/**
 * Common helper functions for Mora Blog
 */

if (!function_exists('slugify')) {
    function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        if (function_exists('iconv')) {
            $trans = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            if ($trans !== false) {
                $text = $trans;
            }
        }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'item-' . time();
        }
        return $text;
    }
}

if (!function_exists('create_unique_slug')) {
    function create_unique_slug($conn, $table, $text, $excludeId = 0) {
        $slug = slugify($text);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $safeSlug = mysqli_real_escape_string($conn, $slug);
            $query = "SELECT id FROM `" . mysqli_real_escape_string($conn, $table) . "` WHERE slug = '$safeSlug' AND id != " . (int)$excludeId . " LIMIT 1";
            $res = mysqli_query($conn, $query);
            if ($res && mysqli_num_rows($res) > 0) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            } else {
                break;
            }
        }
        return $slug;
    }
}

if (!function_exists('generate_excerpt')) {
    function generate_excerpt($content, $length = 160) {
        $plain = strip_tags($content);
        $plain = trim(preg_replace('/\s+/', ' ', $plain));
        if (mb_strlen($plain) > $length) {
            return mb_substr($plain, 0, $length) . '...';
        }
        return $plain;
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

