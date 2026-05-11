<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('../include/DBHandler.php');

define('ADMIN_CODE', 'admin');  // codice admin

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $adminCode = isset($_POST['admin_code']) ? trim($_POST['admin_code']) : '';

    $check = DBHandler::getPDO()->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
    $check->bindParam(':u', $username, PDO::PARAM_STR);
    $check->execute();

    if ($check->fetchColumn() > 0) {
        $error = "Username già in uso. Scegline un altro.";
    } else {
        $role = 'user';
        if ($adminCode !== '' && $adminCode === ADMIN_CODE) {
            $role = 'admin';
        } else if ($adminCode !== '' && $adminCode !== ADMIN_CODE) {
            $error = "Codice non valido.";
        }

        if ($error === '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = DBHandler::getPDO()->prepare(
                "INSERT INTO users (username, password, role) VALUES (:u, :p, :r)"
            );
            $stmt->bindParam(':u', $username, PDO::PARAM_STR);
            $stmt->bindParam(':p', $hashed, PDO::PARAM_STR);
            $stmt->bindParam(':r', $role, PDO::PARAM_STR);
            $stmt->execute();
            header('Location: loginForm.php?registered=1');
            exit;
        }
    }
}

include '../include/login_style.php';
?>
<div class="auth-card">
  <div class="auth-logo"><img src="../assets/logo.png" alt="Arcade"></div>
  <div style="color:var(--gold);font-size:1.4rem;font-weight:700;margin-bottom:.2rem;text-align:center;">Arcade</div>
  <div class="auth-title">Crea account</div>

  <?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required maxlength="32" autofocus>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
      <label class="form-label">Codice admin <span style="color:var(--text2);font-size:.75rem;">(opzionale)</span></label>
      <input type="password" name="admin_code" class="form-control" placeholder="Lascia vuoto se non sei admin">
    </div>
    <button type="submit" class="btn-gold" style="margin-top:.5rem;">Registrati</button>
  </form>

  <hr class="auth-sep">
  <div class="auth-footer">Hai già un account? <a class="auth-link" href="loginForm.php">Accedi</a></div>
  <div class="auth-footer" style="margin-top:.6rem;"><a class="auth-link" href="../index.php">← Torna indietro</a></div>

</div>
</body></html>
