<?php
if (session_status() === PHP_SESSION_NONE) { 
  session_start();
}

  require_once('../include/DBHandler.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = htmlspecialchars($_POST['username']);
  $password = $_POST['password'];
  $stmt = DBHandler::getPDO()->prepare("SELECT userId, username, password, role FROM users WHERE username = :u");
  $stmt->bindParam(':u', $username, PDO::PARAM_STR);
  $stmt->execute();
  if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch();
    if (password_verify($password, $user['password'])) {
      $_SESSION['userId'] = $user['userId'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      header('Location: ../userpages/runner.php'); exit;
    }
  }
  header('Location: ../userpages/loginForm.php?error=1'); exit;
}
header('Location: ../userpages/loginForm.php'); exit;
?>
