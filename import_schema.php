<?php
/**
 * Import PostgreSQL schema from SQL file using PDO.
 * Reads the complete database connection from DATABASE_URL.
 */

$dbUrl = getenv('DATABASE_URL');
if (!$dbUrl) {
    echo "[IMPORT] No DATABASE_URL found, skipping import check.\n";
    exit(0);
}

$parts = parse_url($dbUrl);
if ($parts === false) {
    echo "[IMPORT] DATABASE_URL is invalid.\n";
    exit(1);
}

$requiredParts = ['host', 'port', 'user', 'pass', 'path'];
foreach ($requiredParts as $requiredPart) {
    if (!isset($parts[$requiredPart]) || $parts[$requiredPart] === '') {
        echo "[IMPORT] DATABASE_URL is incomplete.\n";
        exit(1);
    }
}

$host = $parts['host'];
$port = (int) $parts['port'];
$user = rawurldecode($parts['user']);
$pass = rawurldecode($parts['pass']);
$dbname = ltrim($parts['path'], '/');

if ($port < 1 || $port > 65535 || $dbname === '') {
    echo "[IMPORT] DATABASE_URL is invalid.\n";
    exit(1);
}

echo "[IMPORT] Connecting to configured database...\n";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if the database is already initialized (check if users and queue tables exist)
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('users', 'queue')");
    $coreTables = (int) $stmt->fetchColumn();

    if ($coreTables >= 2) {
        echo "[IMPORT] Core tables ('users', 'queue') already exist. Database is initialized.\n";
        exit(0);
    }

    $sqlFile = $argv[1] ?? __DIR__ . '/app/Database/postgres_schema.sql';
    if (!file_exists($sqlFile)) {
        echo "[IMPORT] Error: Schema file not found at $sqlFile\n";
        exit(1);
    }

    echo "[IMPORT] Core tables missing. Importing schema from $sqlFile...\n";
    $sql = file_get_contents($sqlFile);
    
    // Strip UTF-8 BOM if present
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

    // Execute schema import
    $pdo->exec($sql);
    
    // Ensure search_path is set back to public
    $pdo->exec("SET search_path = public;");

    // Verify import
    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    echo "[IMPORT] SUCCESS! Imported " . count($tables) . " tables into public schema.\n";

} catch (PDOException) {
    echo "[IMPORT] Database initialization failed.\n";
    exit(1);
}
