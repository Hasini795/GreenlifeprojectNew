<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the main home page (index.html) after logout
header("location: index.php");
exit;
?>
