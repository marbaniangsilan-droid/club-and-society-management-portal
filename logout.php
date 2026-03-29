<?php
// 1. Resume the current session so the server knows which one to destroy
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Empty all the session variables (user_id, role, email, etc.)
$_SESSION = array();

// 3. Completely destroy the session on the server
session_destroy();

// 4. Redirect the user back to the login page (or index.php)
header("Location: index.php");
exit(); // Always exit immediately after a header redirect
