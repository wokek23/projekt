<?php
if (session_status() != 2) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
    include 'includes/logout.php';
}
?>