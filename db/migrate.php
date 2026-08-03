<?php
// Simple migration script to apply the new schema
require_once __DIR__ . '/../config/database.php';

$sql = file_get_contents(__DIR__ . '/schema.sql');

try {
    $pdo->exec($sql);
    echo "Schema applied successfully!\n";
} catch (PDOException $e) {
    echo "Error applying schema: " . $e->getMessage() . "\n";
}
