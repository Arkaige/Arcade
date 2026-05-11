<?php
require '../include/admincheck.php';
$pdo = DBHandler::getPDO();
$msg = '';

// Gestione ruolo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggleRole'])) {
  $uid = (int) $_POST['userId'];
  if ($uid !== (int) $_SESSION['userId']) {
    $cur = $pdo->prepare("SELECT role FROM users WHERE userId=:u");
    $cur->bindParam(':u',$uid,PDO::PARAM_INT);$cur->execute();
    $row=$cur->fetch();
    $newRole = $row['role'] === 'admin' ? 'user' : 'admin';
    $upd=$pdo->prepare("UPDATE users SET role=:r WHERE userId=:u");
    $upd->bindParam(':r',$newRole,PDO::PARAM_STR);
    $upd->bindParam(':u',$uid,PDO::PARAM_INT);
    $upd->execute();
    $msg = "Ruolo di utente #$uid aggiornato a <strong>$newRole</strong>.";
  }
}

// Elimina utente 
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['deleteUser'])) {
  $uid = (int) $_POST['userId'];
  if ($uid !== (int)$_SESSION['userId']) {
    $pdo->prepare("DELETE FROM users WHERE userId=:u")->execute([':u'=>$uid]);
    $msg = "Utente eliminato.";
  }
}

$users = $pdo->query("
  SELECT u.userId, u.username, u.role, u.joinDate,
         COUNT(DISTINCT m.matchId) AS partite,
         MAX(CASE WHEN l.gameId=1 THEN l.bestScore END) AS bestRunner,
         MAX(CASE WHEN l.gameId=2 THEN l.bestScore END) AS bestSling
  FROM users u
  LEFT JOIN matches m ON m.userId=u.userId
  LEFT JOIN leaderboard l ON l.userId=u.userId
  GROUP BY u.userId ORDER BY u.joinDate DESC
")->fetchAll();

include '../include/adminMenu.php';
?>

<div class="container-fluid" style="max-width:960px;padding:2rem 1rem;">
  <div class="page-title">👥 Gestione Utente</div>

  <?php if($msg): ?>
    <div class="alert-game alert-info mb-3"><?= $msg ?></div>
  <?php endif; ?>

  <table class="table-game" style="width:100%;">
    <thead>
      <tr>
        <th>Utente</th>
        <th>Ruolo</th>
        <th class="text-center">Partite</th>
        <th class="text-center">🏃 Top</th>
        <th class="text-center">🎯 Top</th>
        <th>Iscritto</th>
        <th class="text-center">Azioni</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($users as $u): $isMe = (int)$u['userId']===(int)$_SESSION['userId']; ?>
      <tr class="<?= $isMe?'user-row':'' ?>">
        <td>
          <strong><?= htmlspecialchars($u['username']) ?></strong>
          <?php if($isMe): ?><span class="badge-gold ms-1">Tu</span><?php endif; ?>
        </td>
        <td>
          <?php if($u['role'] === 'admin'): ?>
            <span class="badge-admin">admin</span>
          <?php else: ?>
            <span style="color:var(--text2);font-size:.82rem;">user</span>
          <?php endif; ?>
        </td>
        <td class="text-center" style="color:var(--text2);"><?= $u['partite'] ?></td>
        <td class="text-center" style="color:var(--gold);"><?= isset($u['bestRunner']) ? $u['bestRunner'] : '—' ?></td>
        <td class="text-center" style="color:var(--gold);"><?= $u['bestSling']!==null?$u['bestSling'].'s':'—' ?></td>
        <td style="color:var(--text2);font-size:.82rem;"><?= date('d/m/Y',strtotime($u['joinDate'])) ?></td>
        <td class="text-center">
          <?php if(!$isMe): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="userId" value="<?= $u['userId'] ?>">
            <button name="toggleRole" class="btn btn-outline-muted btn-sm px-2 py-1"
              style="font-size:.75rem;margin-right:4px;"
              title="<?= $u['role'] === 'admin' ? 'Rimuovi admin' : 'Rendi admin' ?>">
              <?= $u['role'] === 'admin' ? '↓ user' : '↑ admin' ?>
            </button>
            <button name="deleteUser" class="btn btn-sm px-2 py-1"
              style="font-size:.75rem;background:rgba(224,80,80,.15);color:#ff7b7b;border:1px solid rgba(224,80,80,.3);border-radius:6px;"
              onclick="return confirm('Eliminare <?= htmlspecialchars($u['username']) ?>?')"
              title="Elimina utente">✕</button>
          </form>
          <?php else: ?><span style="color:var(--text2);font-size:.75rem;">—</span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
