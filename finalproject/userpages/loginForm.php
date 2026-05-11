<?php include '../include/login_style.php'; ?>
<div class="auth-card">
  <div class="auth-logo"><img src="../assets/logo.png" alt="Arcade"></div>
  <div style="color:var(--gold);font-size:1.4rem;font-weight:700;margin-bottom:.2rem;text-align:center;">Arcade</div>
  <div class="auth-title">Accedi per giocare</div>
  <?php if(isset($_GET['error'])): ?><div class="alert alert-danger">Username o password errati.</div><?php endif; ?>
  <?php if(isset($_GET['registered'])): ?><div class="alert alert-success">Account creato! Ora puoi accedere.</div><?php endif; ?>
  <form method="POST" action="login.php">
    <div class="form-group">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required autofocus>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn-gold">Accedi</button>
  </form>
  <hr class="auth-sep">
  <div class="auth-footer">Non hai un account? <a class="auth-link" href="register.php">Registrati</a></div>
  <div class="auth-footer" style="margin-top:.6rem;"><a class="auth-link" href="../index.php">← Torna indietro</a></div>
</div>
</body></html>
