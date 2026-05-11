<?php
// admincheck.php - Verifica sessione e privilegi admin.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../include/DBHandler.php';

if (!isset($_SESSION['userId'])) {
    header('Location: ../userpages/loginForm.php'); 
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../userpages/runner.php'); 
    exit;
}
