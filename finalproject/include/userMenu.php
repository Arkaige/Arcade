<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Arcade</title>
  <?php include '../include/theme.php'; ?>
</head>
<body>
<img src="../assets/arcade.png" class="arcade-deco" alt="">
<?php 
$p = basename($_SERVER['PHP_SELF']); 
$isAdmin = (isset($_SESSION['role']) ? $_SESSION['role'] : '') === 'admin'; 
?>
<nav class="navbar">
  <a class="navbar-brand" href="../index.php"><img src="../assets/logo.png" style="width:22px;height:22px;object-fit:contain;vertical-align:middle;margin-right:5px;"> Arcade</a>
  <div class="nav-sep" style="height:20px;margin:0 .5rem;"></div>
  <button class="nav-toggle" onclick="document.getElementById('navc').classList.toggle('open')">☰</button>
  <div class="nav-collapse" id="navc">
    <ul class="navbar-links">
      <li><a href="runner.php"        class="<?= $p==='runner.php'?'active':'' ?>">🏃 Runner</a></li>
      <li><a href="slingshot.php"   class="<?= $p==='slingshot.php'?'active':'' ?>">🎯 Slingshot</a></li>
      <li><a href="leaderboard.php" class="<?= $p==='leaderboard.php'?'active':'' ?>">🏆 Leaderboard</a></li>
      <?php if($isAdmin): ?>
        <li><div class="nav-sep"></div></li>
        <li><a href="../adminpages/manageUsers.php"  class="admin-link <?php if(in_array($p,['manageUsers.php'])) { echo 'active'; } ?>">👥 Utenti</a></li>
        <li><a href="../adminpages/manageScores.php" class="admin-link <?php if(in_array($p,['manageScores.php'])) { echo 'active'; } ?>">🗑 Punteggi</a></li>
        <li><a href="../adminpages/stats.php"        class="admin-link <?php if(in_array($p,['stats.php'])) { echo 'active'; } ?>">📊 Statistiche</a></li>
      <?php endif; ?>
    </ul>
    <div class="navbar-right">
      <span class="navbar-user">
        👤 <?= htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : '') ?>
        <?php if($isAdmin): ?>
          <span class="badge badge-admin" style="margin-left:5px;">admin</span>
        <?php endif; ?>
      </span>
      <a href="../include/logout.php" class="btn btn-outline-muted btn-sm">Logout</a>
    </div>
  </div>
</nav>
</body>