<?php
// Leaderboard and score management
// gameId (1 = Runner, 2 = Slingshot).
header('Content-Type: application/json');

$userId = $_SESSION['userId'];
$gameId = isset($_GET['gameId']) ? (int) $_GET['gameId'] : (isset($_POST['gameId']) ? (int) $_POST['gameId'] : 1);
if ($gameId < 1 || $gameId > 2) {
    $gameId = 1;
}


// GET - User record
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['getBest'])) {
    $stmt = DBHandler::getPDO()->prepare(
        "SELECT bestScore FROM leaderboard WHERE userId = :uid AND gameId = :gid"
    );
    $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':gid', $gameId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    echo json_encode(['bestScore' => $row ? (int)$row['bestScore'] : 0]);
    exit;
}

// POST - Leaderboard record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score'])) {
    $score = (int)$_POST['score'];
    $pdo   = DBHandler::getPDO();

    // 1. Registers the match
    $stmt = $pdo->prepare("INSERT INTO matches (userId, gameId, score) VALUES (:uid, :gid, :score)");
    $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':gid', $gameId, PDO::PARAM_INT);
    $stmt->bindParam(':score', $score,  PDO::PARAM_INT);
    $stmt->execute();

    // 2. Updates the leaderboard (checks if it's a new record)
    $check = $pdo->prepare("SELECT bestScore FROM leaderboard WHERE userId = :uid AND gameId = :gid");
    $check->bindParam(':uid', $userId, PDO::PARAM_INT);
    $check->bindParam(':gid', $gameId, PDO::PARAM_INT);
    $check->execute();
    $existing = $check->fetch();

    if (!$existing) {
        // First match of the user
        $stmt = $pdo->prepare("INSERT INTO leaderboard (userId, gameId, bestScore, updatedAt) VALUES (:uid, :gid, :score, NOW())");
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':gid', $gameId, PDO::PARAM_INT);
        $stmt->bindParam(':score', $score,  PDO::PARAM_INT);
        $stmt->execute();
    } else if ($score > (int) $existing['bestScore']) {
        // New record (saves highscore and the date)
        $stmt = $pdo->prepare("UPDATE leaderboard SET bestScore = :score, updatedAt = NOW() WHERE userId = :uid AND gameId = :gid");
        $stmt->bindParam(':score', $score,  PDO::PARAM_INT);
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':gid', $gameId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // 3. Returns the updated record
    $stmt = $pdo->prepare("SELECT bestScore FROM leaderboard WHERE userId = :uid AND gameId = :gid");
    $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':gid', $gameId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();

    echo json_encode(['bestScore' => (int) $row['bestScore']]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
exit;
?>
