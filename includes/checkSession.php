<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
    include 'includes/logout.php';
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    include 'includes/logout.php';
}
?>