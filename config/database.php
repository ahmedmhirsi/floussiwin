<?php
// config/database.php
// PDO connection - update with your DB credentials (MySQL)

$DB_HOST = '127.0.0.1';
$DB_NAME = 'flousiwin';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Log error and show generic message to the user
    error_log('Database connection failed: ' . $e->getMessage());
    if (php_sapi_name() === 'cli') {
        // helpful for CLI debugging
        echo "Database connection failed. Check logs." . PHP_EOL;
    } else {
        echo "Erreur de connexion à la base de données. Contactez l'administrateur.";
    }
    exit;
}
