<?php
require_once __DIR__ . '/inc/config.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

header('Location: login.php');
exit();
