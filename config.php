<?php
/**
 * Configuration & TMDB API Helper Functions
 * Project: CinePulse - PHP & TMDB Movie Application
 */

// Suppress deprecation warnings in production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default Working TMDB API Key (Can be overridden by user in UI session or GET/POST)
define('DEFAULT_TMDB_API_KEY', '802b2c4b88ea1183e50e6b285a27696e');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/');

/**
 * Get active API Key (Session override or default)
 */
function getActiveApiKey() {
    if (!empty($_SESSION['user_tmdb_api_key'])) {
        return trim($_SESSION['user_tmdb_api_key']);
    }
    if (!empty($_COOKIE['user_tmdb_api_key'])) {
        return trim($_COOKIE['user_tmdb_api_key']);
    }
    return DEFAULT_TMDB_API_KEY;
}

/**
 * Perform cURL request to TMDB API
 */
function getTMDBData($endpoint, $params = []) {
    $apiKey = getActiveApiKey();
    $params['api_key'] = $apiKey;
    
    // Default language to English
    if (!isset($params['language'])) {
        $params['language'] = 'en-US';
    }

    $queryString = http_build_query($params);
    $url = TMDB_BASE_URL . '/' . ltrim($endpoint, '/') . '?' . $queryString;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CinePulse/1.0 (PHP Movie App)');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    @curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return [
            'error' => true,
            'message' => $curlError ?: "HTTP Error Code: $httpCode",
            'results' => []
        ];
    }

    $data = json_decode($response, true);
    return $data ?: ['error' => true, 'message' => 'Invalid JSON response', 'results' => []];
}

/**
 * Helper to build Poster Image URL
 */
function getPosterUrl($path, $size = 'w500') {
    if (empty($path)) {
        return 'https://via.placeholder.com/500x750/121026/8b5cf6?text=No+Poster+Available';
    }
    return TMDB_IMAGE_BASE . $size . $path;
}

/**
 * Helper to build Backdrop Image URL
 */
function getBackdropUrl($path, $size = 'original') {
    if (empty($path)) {
        return 'https://via.placeholder.com/1920x1080/0f0d1a/8b5cf6?text=No+Backdrop+Available';
    }
    return TMDB_IMAGE_BASE . $size . $path;
}

/**
 * Helper to format release date
 */
function formatDate($dateStr) {
    if (empty($dateStr)) return 'N/A';
    $timestamp = strtotime($dateStr);
    return $timestamp ? date('M d, Y', $timestamp) : $dateStr;
}

/**
 * Helper to format year only
 */
function formatYear($dateStr) {
    if (empty($dateStr)) return 'N/A';
    return date('Y', strtotime($dateStr));
}

/**
 * Helper to format runtime minutes into hours and minutes
 */
function formatRuntime($minutes) {
    if (empty($minutes)) return 'N/A';
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 0) {
        return "{$hours}h {$mins}m";
    }
    return "{$mins}m";
}

/**
 * Helper to format rating with 1 decimal place
 */
function formatRating($rating) {
    return number_format((float)$rating, 1, '.', '');
}

/**
 * Helper to get rating color class
 */
function getRatingClass($rating) {
    if ($rating >= 7.5) return 'rating-high';
    if ($rating >= 6.0) return 'rating-mid';
    return 'rating-low';
}
