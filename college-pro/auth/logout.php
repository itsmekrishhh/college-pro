<?php
/**
 * Logout Page
 * Destroys session and logs user out
 */

require_once '../config/db.php';

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect to home page
header('Location: /college-pro/index.php');
exit();

?>

