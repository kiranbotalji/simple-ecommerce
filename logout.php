<?php
// File: logout.php — main logic for logout page.
session_start();
session_unset();
session_destroy();

// Remove "Remember Me" cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, "/");
}

header("Location: index.php");
exit();
?>
