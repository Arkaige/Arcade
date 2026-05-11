<?php
require '../include/admincheck.php';
$pdo = DBHandler::getPDO();

$totUsers   = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totMatches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$totAdmins  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();

$statsByGame = $pdo->query("
  SELECT g.gameName, g.gameId,
         COUNT(m.matchId) AS partite,
         COALESCE(AVG(m.score),0) AS media,
         COALESCE(MAX(m.score),0) AS massimo,
         COUNT(DISTINCT m.userId) AS giocatori
  FROM gametype g LEFT JOIN matches m ON m.gameId=g.gameId
  GROUP BY g.gameId
")->fetchAll();

$topPlayers = $pdo->query("
  SELECT u.username, COUNT(m.matchId) AS partite
  FROM users u LEFT JOIN matches m ON m.userId=u.userId
  GROUP BY u.userId ORDER BY partite DESC LIMIT 5
")->fetchAll();

$recent = $pdo->query("
  SELECT u.username, g.gameName, m.score, m.playedAt
  FROM matches m JOIN users u ON m.userId=u.userId JOIN gametype g ON m.gameId=g.gameId
  ORDER BY m.playedAt DESC LIMIT 10
")->fetchAll();

include '../include/adminMenu.php';
?>
<style>
.stat-card { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius); padding:1.25rem 1.5rem; }
.stat-num { font-size:2rem; font-weight:700; color:var(--gold); line-height:1; }
.stat-lbl { color:var(--text2); font-size:.78rem; text-transform:uppercase; letter-spacing:.6px; margin-top:.2rem; }
</style>
<div class="container-fluid" style="max-width:960px;padding:2rem 1rem;">
  <div class="page-title">📊 Statistiche</div>

  <!-- Cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
    <div class="stat-card"><div class="stat-num"><?= $totUsers ?></div><div class="stat-lbl">Utenti totali</div></div>
    <div class="stat-card"><div class="stat-num"><?= $totMatches ?></div><div class="stat-lbl">Partite giocate</div></div>
    <div class="stat-card"><div class="stat-num"><?= $totAdmins ?></div><div class="stat-lbl">Amministratori</div></div>
  </div>

  <!-- Stats per gioco -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;margin-bottom:2rem;">
  <?php foreach($statsByGame as $s): ?>
    <div class="card-dark">
      <div style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--text);"><?= $s['gameId']===1?'🏃':'🎯' ?> <?= htmlspecialchars($s['gameName']) ?></div>
      <table style="width:100%;font-size:.88rem;">
        <tr><td style="color:var(--text2);padding:.3rem 0;">Partite</td><td style="text-align:right;color:var(--text);font-weight:600;"><?= $s['partite'] ?></td></tr>
        <tr><td style="color:var(--text2);padding:.3rem 0;">Players unici</td><td style="text-align:right;color:var(--text);font-weight:600;"><?= $s['giocatori'] ?></td></tr>
        <tr><td style="color:var(--text2);padding:.3rem 0;">Punteggio medio</td><td style="text-align:right;color:var(--gold);font-weight:700;"><?= number_format((float)$s['media'],1) ?><?= $s['gameId']===2?'s':'' ?></td></tr>
        <tr><td style="color:var(--text2);padding:.3rem 0;">Record assoluto</td><td style="text-align:right;color:var(--gold);font-weight:700;"><?= number_format((int)$s['massimo']) ?><?= $s['gameId']===2?'s':'' ?></td></tr>
      </table>
    </div>
  <?php endforeach; ?>
  </div>

  <!-- Players più attivi e ultime partite -->
  <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem;">
    <div class="card-dark">
      <div style="font-weight:600;margin-bottom:1rem;color:var(--text);">🔥 Più attivi</div>
      <table class="table-game" style="width:100%">
        <thead><tr><th>#</th><th>Player</th><th class="text-end">Partite</th></tr></thead>
        <tbody>
        <?php foreach($topPlayers as $i=>$p): ?>
          <tr>
            <td style="color:var(--text2);"><?= $i+1 ?></td>
            <td><?= htmlspecialchars($p['username']) ?></td>
            <td class="text-end" style="color:var(--gold);font-weight:700;"><?= $p['partite'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-dark">
      <div style="font-weight:600;margin-bottom:1rem;color:var(--text);">⏱ Ultime partite</div>
      <table class="table-game" style="width:100%">
        <thead><tr><th>Giocatore</th><th>Gioco</th><th class="text-end">Punteggio</th><th class="text-end">Quando</th></tr></thead>
        <tbody>
        <?php foreach($recent as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td style="color:var(--text2);font-size:.82rem;"><?= htmlspecialchars($r['gameName']) ?></td>
            <td class="text-end" style="color:var(--gold);font-weight:700;"><?= number_format((int) $r['score']) ?></td>
            <td class="text-end" style="color:var(--text2);font-size:.78rem;"><?= date('d/m H:i',strtotime($r['playedAt'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body></html>
