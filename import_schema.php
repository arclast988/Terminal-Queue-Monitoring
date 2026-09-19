<?php
/**
 * Reset database by dropping all tables so migrations can run fresh.
 * Only runs if the migrations table doesn't exist (fresh/broken state).
 */

$dbUrl = getenv('DATABASE_URL');
if (!$dbUrl) {
    echo "[RESET] No DATABASE_URL, skipping\n";
    exit(0);
}

$parts = parse_url($dbUrl);
$host = $parts['host'] ?? '127.0.0.1';
$port = $parts['port'] ?? 5432;
$user = $parts['user'] ?? 'postgres';
$pass = $parts['pass'] ?? '';
$dbname = ltrim($parts['path'] ?? '/railway', '/');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if migrations table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'migrations')");
    $hasMigrations = $stmt->fetchColumn();

    if ($hasMigrations) {
        // Check if migrations have actually been recorded
        $count = $pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        if ($count > 0) {
            echo "[RESET] Migrations table exists with $count records. Schema is managed by migrations.\n";
            exit(0);
        }
    }

    // No migrations table or empty - drop everything so migrations can run clean
    echo "[RESET] No migration records found. Dropping all tables for clean migration...\n";
    
    // Get all table names
    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        $pdo->exec("DROP TABLE IF EXISTS " . implode(', ', $tables) . " CASCADE");
        echo "[RESET] Dropped " . count($tables) . " tables.\n";
    } else {
        echo "[RESET] No tables to drop.\n";
    }

} catch (PDOException $e) {
    echo "[RESET] Error: " . $e->getMessage() . "\n";
    exit(1);
}
