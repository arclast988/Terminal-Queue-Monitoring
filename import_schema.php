<?php
/**
 * Import PostgreSQL schema from SQL file using PDO.
 * Usage: php import_schema.php <sql_file>
 * 
 * Reads DATABASE_URL from environment or .env file.
 */

$sqlFile = $argv[1] ?? 'app/Database/postgres_schema.sql';

if (!file_exists($sqlFile)) {
    echo "[IMPORT] SQL file not found: $sqlFile\n";
    exit(1);
}

// Parse DATABASE_URL
$dbUrl = getenv('DATABASE_URL');
if (!$dbUrl) {
    echo "[IMPORT] No DATABASE_URL found\n";
    exit(1);
}

$parts = parse_url($dbUrl);
$host = $parts['host'] ?? '127.0.0.1';
$port = $parts['port'] ?? 5432;
$user = $parts['user'] ?? 'postgres';
$pass = $parts['pass'] ?? '';
$dbname = ltrim($parts['path'] ?? '/railway', '/');

echo "[IMPORT] Connecting to $host:$port/$dbname as $user...\n";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if tables already exist
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'users')");
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "[IMPORT] Tables already exist, skipping import.\n";
        exit(0);
    }

    // Read and execute SQL
    echo "[IMPORT] Importing schema from $sqlFile...\n";
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    echo "[IMPORT] Schema imported successfully!\n";

} catch (PDOException $e) {
    echo "[IMPORT] Database error: " . $e->getMessage() . "\n";
    exit(1);
}
