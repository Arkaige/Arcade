<?php
require '../include/admincheck.php';

$pdo = DBHandler::getPDO();
$msg = '';

// Reset leaderboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resetBoard'])) {
  $gid = (int) $_POST['gameId'];
  $pdo -> prepare("DELETE FROM leaderboard WHERE gameId=:g") -> execute([':g' => $gid]);
  $msg = "Leaderboard azzerata.";
}

// Elimina un unico record 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteRecord'])) {
  $uid = (int) $_POST['userId']; 
  $gid = (int) $_POST['gameId'];
  $pdo->prepare("DELETE FROM leaderboard WHERE userId=:u AND gameId=:g")->execute([':u'=>$uid,':g'=>$gid]);
  $msg = "Record eliminato.";
}

// Leaderboard ranking
$games = $pdo->query("SELECT * FROM gametype ORDER BY gameId")->fetchAll();
$boards = [];
foreach($games as $g){
  $gid = $g['gameId'];
  $stmt = $pdo->prepare("SELECT u.username,u.userId,l.bestScore,l.updatedAt,DENSE_RANK() OVER(ORDER BY l.bestScore DESC) pos FROM leaderboard l JOIN users u ON l.userId=u.userId WHERE l.gameId=:g ORDER BY l.bestScore DESC");
  $stmt->execute([':g'=>$gid]);
  $boards[$gid] = $stmt->fetchAll();
}
$scoreLabel = [1 => 'Punti', 2 => 'Secondi'];


include '../include/adminMenu.php';
?>

<div class="container-fluid" style="max-width:760px;padding:2rem 1rem;">
  <div class="page-title">🗑 Gestione Punteggi</div>
  <?php if($msg): ?>
    <div class="alert-game alert-info mb-3">
      <?= $msg ?>
    </div>
  <?php endif; ?>

  <?php foreach($games as $g): 
    $gid=$g['gameId'];
  ?>


  <div class="card-dark mb-4">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
      <h5 style="color:var(--text);margin:0;"><?= $gid===1?'🏃':'🎯' ?> <?= htmlspecialchars($g['gameName']) ?></h5>
      <form method="POST">
        <input type="hidden" name="gameId" value="<?= $gid ?>">
        <button name="resetBoard" class="btn btn-sm" style="background:rgba(224,80,80,.15);color:#ff7b7b;border:1px solid rgba(224,80,80,.3);border-radius:6px;font-size:.8rem;padding:4px 12px;"
          onclick="return confirm('Cancellare la leaderboard di <?= htmlspecialchars($g['gameName']) ?>?')">
          Cancella
        </button>
      </form>
    </div>

    <?php if(empty($boards[$gid])): ?>
      <p style="color:var(--text2);font-size:.88rem;">Nessun record.</p>
    <?php else: ?>
      <table class="table-game" style="width:100%">
        <thead><tr><th>#</th><th>Player</th><th class="text-end"><?= $scoreLabel[$gid] ?></th><th class="text-end">Data</th><th></th></tr></thead>
        <tbody>
        <?php foreach($boards[$gid] as $r): ?>
        <tr>
          <td style="color:var(--text2);"><?= $r['pos'] ?></td>
          <td><?= htmlspecialchars($r['username']) ?></td>
          <td class="text-end" style="color:var(--gold);font-weight:700;"><?= number_format((int)$r['bestScore']) ?><?= $gid===2?'s':'' ?></td>
          <td class="text-end" style="color:var(--text2);font-size:.82rem;"><?= date('d/m/y',strtotime($r['updatedAt'])) ?></td>
          <td class="text-end">
            <form method="POST" style="display:inline;">
              <input type="hidden" name="userId" value="<?= $r['userId'] ?>">
              <input type="hidden" name="gameId" value="<?= $gid ?>">
              <button name="deleteRecord" style="background:none;border:none;color:#ff7b7b;cursor:pointer;font-size:.85rem;" title="Elimina record" onclick="return confirm('Eliminare il record di <?= htmlspecialchars($r['username']) ?>?')">✕</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
</body></html>
