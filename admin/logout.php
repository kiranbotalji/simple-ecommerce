<?php
// File: logout.php — main logic for logout page.
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
