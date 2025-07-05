<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kia_apartment');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function hasPermission($required_roles) {
    if (!isLoggedIn()) return false;
    $user_role = getUserRole();
    return in_array($user_role, $required_roles);
}

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'booked':
            return 'status-booked';
        case 'checkin':
            return 'status-checkin';
        case 'checkout':
            return 'status-checkout';
        case 'completed':
            return 'status-completed';
        case 'cancelled':
            return 'status-cancelled';
        case 'no_show':
            return 'status-no-show';
        default:
            return 'status-default';
    }
}

function buildUrl($params = []) {
    $current_params = $_GET;
    $merged_params = array_merge($current_params, $params);
    
    // Remove empty parameters
    $merged_params = array_filter($merged_params, function($value) {
        return $value !== '' && $value !== null;
    });
    
    $query_string = http_build_query($merged_params);
    $base_url = strtok($_SERVER["REQUEST_URI"], '?');
    
    return $base_url . ($query_string ? '?' . $query_string : '');
}
?>
