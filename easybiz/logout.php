<?php
// ============================================================
// logout.php — Log the user out
// Destroys the session and redirects to login page
// ============================================================
include 'includes/config.php';

// Clear all session data stored for this user
$_SESSION = []; // empty the session array

// Destroy the session completely on the server
session_destroy();

// Redirect to login page after logout
header("Location: login.php");
exit(); // stop script from continuing
?>
