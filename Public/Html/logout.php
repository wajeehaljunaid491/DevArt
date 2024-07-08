<?php
session_start();
session_unset();
session_destroy();

// Redirect to the previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
