<?php
// Verifica che l'utente sia autenticato.
if (!isset($_SESSION['userId'])) {
    header('Location: loginForm.php');
    exit;
}
?>
