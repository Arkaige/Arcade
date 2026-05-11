<?php
// Gestione routing delle pagine

$json = file_get_contents('../include/pages.json');
$pageName = basename($_SERVER['PHP_SELF']);
$obj = json_decode($json);

if (in_array($pageName, $obj->loggedInPages)) {
    require '../include/header.php';
}

if (in_array($pageName, $obj->DBPages) && !in_array($pageName, $obj->loggedInPages)) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once('../include/DBHandler.php');
}

if (in_array($pageName, $obj->userpages)) {
    include '../include/userMenu.php';
}
?>
