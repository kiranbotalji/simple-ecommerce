<?php
// File: logout.php — main logic for logout page.
session_start();
// Clear only user data to avoid ending admin session in the same browser
unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['email'], $_SESSION['photo'], $_SESSION['cart']);
// Remove "Remember Me" cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, "/");
}
header("Location: index.php");
exit();
?>
