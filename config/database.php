<?php
/**
 * Configuration locale non versionnée (copier database.local.example.php).
 * Ne publiez jamais config/database.local.php sur GitHub.
 */
$configFile = __DIR__ . '/database.local.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Configuration absente : recopiez config/database.local.example.php en config/database.local.php.');
}

$config = require $configFile;
foreach (['host', 'port', 'dbname', 'username', 'password'] as $requiredKey) {
    if (!is_array($config) || !array_key_exists($requiredKey, $config)) {
        http_response_code(503);
        exit('La configuration de la base de données est incomplète.');
    }
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'], $config['port'], $config['dbname']),
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    error_log('[LexiBook] Échec de connexion MySQL : ' . $e->getMessage());
    http_response_code(503);
    exit('Connexion à la base de données indisponible. Vérifiez la configuration locale.');
}
