<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Arcade</title>
  <?php include '../include/theme.php'; ?>
</head>
<body>
<img src="../assets/arcade.png" class="arcade-deco" alt="">
<?php $p = basename($_SERVER['PHP_SELF']); ?>
  <nav class="navbar">
    <a class="navbar-brand" href="../userpages/runner.php"><img src="../assets/logo.png" style="width:22px;height:22px;object-fit:contain;vertical-align:middle;margin-right:5px;"> Arcade</a>
    <div class="nav-sep" style="height:20px;margin:0 .5rem;"></div>
    <button class="nav-toggle" onclick="document.getElementById('anavc').classList.toggle('open')">☰</button>
    <div class="nav-collapse" id="anavc">
      <ul class="navbar-links">
        <li><a href="../userpages/runner.php">🏃 Runner</a></li>
        <li><a href="../userpages/slingshot.php">🎯 Slingshot</a></li>
        <li><a href="../userpages/leaderboard.php">🏆 Leaderboard</a></li>
        <li><div class="nav-sep"></div></li>
        <li><a href="manageUsers.php" class="admin-link <?= $p === 'manageUsers.php' ? 'active' : '' ?>">👥 Utenti</a></li>
        <li><a href="manageScores.php" class="admin-link <?= $p === 'manageScores.php' ? 'active' : '' ?>">🗑 Punteggi</a></li>
        <li><a href="stats.php" class="admin-link <?= $p ==='stats.php' ? 'active' : '' ?>">📊 Statistiche</a></li>
      </ul>
      <div class="navbar-right">
        <span class="navbar-user">
          👤 <?= htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : '') ?>
          <span class="badge badge-admin" style="margin-left:5px;">admin</span>
        </span>
        <a href="../include/logout.php" class="btn btn-outline-muted btn-sm">Logout</a>
      </div>
    </div>
  </nav>
</body>