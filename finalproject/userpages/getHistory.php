<?php
// Card delle ultime partite giocate di un utente
header('Content-Type: application/json');

$gameId = isset($_GET['gameId']) ? (int) $_GET['gameId'] : 1;
$userId = $_SESSION['userId'];

$stmt = DBHandler::getPDO()->prepare("
    SELECT score, playedAt
    FROM matches
    WHERE userId = :uid AND gameId = :gid
    ORDER BY playedAt DESC
    LIMIT 5
");
$stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
$stmt->bindParam(':gid', $gameId, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$result = [];
foreach ($rows as $r) {
    $entry = [];
    $entry['score'] = (int) $r['score'];
    $entry['playedAt'] = $r['playedAt'];
    $result[] = $entry;
}

echo json_encode($result);
exit;
