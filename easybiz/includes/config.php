<?php
// ============================================================
// includes/config.php
// Central configuration file — edit your database credentials here
// Include this file at the top of every PHP page
// ============================================================

// --- DATABASE SETTINGS ---
// Change these to match your hosting environment (e.g. cPanel / XAMPP)
define('DB_HOST', 'localhost');     // usually 'localhost' on most hosts
define('DB_USER', 'root');          // your MySQL username
define('DB_PASS', '');              // your MySQL password
define('DB_NAME', 'easybiz_db');    // the database name you created

// --- APP SETTINGS ---
define('APP_NAME', 'EasyBiz');      // app name shown in titles and messages
define('FREE_LIMIT', 5);           // number of free orders allowed before paywall
define('SUB_PRICE', 3000);         // subscription price in FCFA
define('SUB_DAYS', 30);            // how many days a subscription lasts

// --- ADMIN SETTINGS ---
define('ADMIN_SESSION_NAME', 'easybiz_admin'); // session key for admin login

// ============================================================
// Create the database connection using MySQLi
// This variable $conn is available in every file that includes config.php
// ============================================================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if connection failed and stop script with error message
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
    // If you see this error, check DB_HOST, DB_USER, DB_PASS, DB_NAME above
}

// Set character encoding to UTF-8 to support all languages and symbols
$conn->set_charset("utf8mb4");

// ============================================================
// Start the PHP session (needed for login state, flash messages, etc.)
// Only start if session is not already active
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // start session to store user login data
}

// ============================================================
// Helper: check if a regular user is logged in
// Redirects to login page if not authenticated
// Use this at the top of any page that requires login
// ============================================================
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        // User not logged in — redirect to login page
        header("Location: /login.php");
        exit(); // stop page from loading further
    }
}

// ============================================================
// Helper: check if admin is logged in
// Redirects to admin login if not authenticated
// ============================================================
function require_admin() {
    if (!isset($_SESSION[ADMIN_SESSION_NAME])) {
        header("Location: /admin/login.php");
        exit();
    }
}

// ============================================================
// Helper: safely escape user input before using in HTML
// Prevents XSS (cross-site scripting) attacks
// Always use this when outputting user data to the page
// ============================================================
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ============================================================
// Helper: check if user's subscription is still active
// Compares today's date to the subscription end date
// Returns true if premium and not expired, false otherwise
// ============================================================
function is_premium($user) {
    if ($user['plan'] !== 'premium') return false; // not premium at all
    if (empty($user['sub_end'])) return false;     // no end date set
    return strtotime($user['sub_end']) >= strtotime(date('Y-m-d')); // not expired yet
}

// ============================================================
// Helper: get full user data from DB by user_id
// Returns associative array of user row, or false if not found
// ============================================================
function get_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id); // "i" = integer type
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // return as associative array
}

// ============================================================
// Helper: get business profile for a user
// Returns profile row or false if user hasn't set one up yet
// ============================================================
function get_profile($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM business_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
