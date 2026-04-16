<?php
session_start();
include_once __DIR__ . '/includes/auth-handler.php';
logoutUser();
header('Location: login.php');
exit();
?>
