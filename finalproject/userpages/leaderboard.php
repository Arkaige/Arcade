<?php
$pdo = DBHandler::getPDO();
$currentUserId = (int)$_SESSION['userId'];
$currentUsername = htmlspecialchars($_SESSION['username']);

$games = $pdo->query("SELECT gameId, gameName FROM gametype ORDER BY gameId")->fetchAll();

$boards   = [];
$myStats  = [];
$gameIcons = [1 => '🏃', 2 => '🎯'];
$gameLinks = [1 => 'runner.php', 2 => 'slingshot.php'];
$scoreLabel = [1 => 'Punteggio', 2 => 'Secondi'];

foreach ($games as $game) {
    $gid = $game['gameId'];

    $stmt = $pdo->prepare("
        SELECT u.username, u.userId, l.bestScore, l.updatedAt,
               DENSE_RANK() OVER (ORDER BY l.bestScore DESC) AS pos
        FROM leaderboard l
        JOIN users u ON l.userId = u.userId
        WHERE l.gameId = :gid
        ORDER BY l.bestScore DESC
        LIMIT 50
    ");
    $stmt->bindParam(':gid', $gid, PDO::PARAM_INT);
    $stmt->execute();
    $boards[$gid] = $stmt->fetchAll();

    // Posizione e score dell'utente corrente per leaderboard
    $myStmt = $pdo->prepare("
        SELECT l.bestScore,
               (SELECT COUNT(*) + 1 FROM leaderboard l2 WHERE l2.gameId = :gid2 AND l2.bestScore > l.bestScore) AS pos
        FROM leaderboard l
        WHERE l.userId = :uid AND l.gameId = :gid3
    ");
    $myStmt->bindParam(':uid',  $currentUserId, PDO::PARAM_INT);
    $myStmt->bindParam(':gid2', $gid,           PDO::PARAM_INT);
    $myStmt->bindParam(':gid3', $gid,           PDO::PARAM_INT);
    $myStmt->execute();
    $myStats[$gid] = $myStmt->fetch();
}

function medal(int $p): string {
    if ($p === 1) { return '🥇'; }
    if ($p === 2) { return '🥈'; }
    if ($p === 3) { return '🥉'; }
    return (string) $p;
}
?>
<style>
body { background: var(--bg); }
.lb-layout {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 1.5rem;
    max-width: 960px;
    margin: 0 auto;
    padding: 2rem 1rem;
    align-items: start;
}
@media (max-width: 640px) {
    .lb-layout { grid-template-columns: 1fr; }
}

/* Pannello posizione */
.my-stats { position: sticky; top: 1rem; }
.my-stats-title {
    font-size: .72rem; text-transform: uppercase;
    letter-spacing: .7px; color: var(--text2);
    margin-bottom: .75rem; font-weight: 600;
}
.my-stat-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1rem;
    margin-bottom: .75rem;
}
.my-stat-card .game-label {
    font-size: .8rem; color: var(--text2);
    margin-bottom: .6rem;
}
.my-stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .88rem;
    padding: .25rem 0;
    border-bottom: 1px solid rgba(48,54,61,.4);
}
.my-stat-row:last-child { border-bottom: none; }
.my-stat-row span { color: var(--text2); }
.my-stat-row strong { color: var(--gold); }
.no-record { color: var(--text2); font-size: .82rem; font-style: italic; }

/* Tabs e tabella */
.lb-tabs { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
.lb-tab {
    padding: .5rem 1.2rem; border-radius: 8px;
    border: 1px solid var(--border);
    background: transparent; color: var(--text2);
    font-size: .88rem; cursor: pointer;
    font-weight: 500; transition: all .15s;
    font-family: inherit;
}
.lb-tab.active { background: rgba(240,192,64,.1); border-color: var(--gold); color: var(--gold); }
.lb-tab:hover { border-color: var(--text2); color: var(--text); }
.lb-panel { display: none; }
.lb-panel.active { display: block; }
.empty-msg { text-align: center; padding: 3rem; color: var(--text2); }
.play-wrap { text-align: center; margin-top: 1.5rem; }
.score-val { color: var(--gold); font-weight: 700; }
.user-row td { background: rgba(240,192,64,.06); border-left: 2px solid var(--gold); }
</style>



<div class="lb-layout">

    <!-- Classifica utente -->
    <div class="my-stats">
        <div class="my-stats-title">👤 La tua posizione</div>

        <?php foreach ($games as $game):
            $gid  = $game['gameId'];
            $stat = $myStats[$gid];
        ?>
            <div class="my-stat-card">
                <div class="game-label"><?= $gameIcons[$gid] ?> <?= htmlspecialchars($game['gameName']) ?></div>
                <?php if ($stat): ?>
                    <div class="my-stat-row">
                        <span>Posizione</span>
                        <strong>#<?= (int)$stat['pos'] ?></strong>
                    </div>
                    <div class="my-stat-row">
                        <span><?= $scoreLabel[$gid] ?></span>
                        <strong><?= number_format((int) $stat['bestScore']) ?><?php if ($gid === 2) { echo 's'; } ?></strong>
                    </div>
                <?php else: ?>
                    <div class="no-record">Nessuna partita ancora</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Classifica -->
    <div>
        <div class="page-title">🏆 Leaderboard</div>

        <div class="lb-tabs">
            <?php foreach ($games as $i => $game): ?>
                <button class="lb-tab <?php if ($i === 0) { echo 'active'; } ?>"
                    onclick="showTab(<?= $game['gameId'] ?>, this)">
                    <?= $gameIcons[$game['gameId']] ?> <?= htmlspecialchars($game['gameName']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($games as $i => $game):
            $gid  = $game['gameId'];
            $rows = $boards[$gid];
        ?>
        <div class="lb-panel <?php if ($i === 0) { echo 'active'; } ?>" id="panel<?= $gid ?>">
            <?php if (empty($rows)): ?>
                <div class="empty-msg">Nessun punteggio trovato.</div>
            <?php else: ?>
            <table class="table-game">
                <thead>
                    <tr>
                        <th style="width:3rem">#</th>
                        <th>Giocatore</th>
                        <th class="text-end"><?= $scoreLabel[$gid] ?></th>
                        <th class="text-end" style="font-size:.72rem;">Data</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row):
                    $pos = (int)$row['pos'];
                    $isMe = (int)$row['userId'] === $currentUserId;
                ?>
                    <tr class="<?php if ($isMe) { echo 'user-row'; } ?>">
                        <td>
                            <?php if ($pos <= 3): ?>
                                <?= medal($pos) ?>
                            <?php else: ?>
                                <span style="color:var(--text2);"><?= $pos ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['username']) ?>
                            <?php if ($isMe): ?>
                                <span class="badge badge-gold" style="margin-left:6px;">Tu</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end score-val">
                            <?= number_format((int)$row['bestScore']) ?><?php if ($gid === 2) { echo 's'; } ?>
                        </td>
                        <td class="text-end" style="color:var(--text2);font-size:.8rem;">
                            <?= date('d/m/y', strtotime($row['updatedAt'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            <div class="play-wrap">
                <a href="<?= $gameLinks[$gid] ?>" class="btn btn-gold" style="text-decoration:none;padding:.6rem 2rem;">
                    ▶ Gioca <?= htmlspecialchars($game['gameName']) ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
function showTab(id, btn) {
    var panels = document.querySelectorAll('.lb-panel');
    var tabs = document.querySelectorAll('.lb-tab');

    for (var i = 0; i < panels.length; i++) {
        panels[i].classList.remove('active'); 
    }
    for (var i = 0; i < tabs.length; i++) { 
        tabs[i].classList.remove('active'); 
    }

    document.getElementById('panel' + id).classList.add('active');
    btn.classList.add('active');
}
</script>
</body></html>
