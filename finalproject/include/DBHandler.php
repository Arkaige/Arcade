<?php
class DBHandler {
    private static $pdo = null;

    // Singleton
    private function __construct() {}

    public static function getPDO() {
        if (self::$pdo === null) {
            self::connect_database();
        }
        return self::$pdo;
    }

    private static function connect_database() {
        define('DB_USER', 'root');
        define('DB_PASS', '');

        try {
            $connection_string = 'mysql:host=localhost;dbname=jumpgame;charset=utf8';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            self::$pdo = new PDO($connection_string, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            self::$pdo = null;
            die("Errore connessione DB: " . $e->getMessage());
        }
    }
}
?>
