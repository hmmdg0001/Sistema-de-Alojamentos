<?php
// includes/config.php — Configuração da base de dados

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Alterar para o teu utilizador
define('DB_PASS', '');           // Alterar para a tua password
define('DB_NAME', 'tpfinal_db');

define('SITE_NAME', 'Alojamentos Online');
define('BASE_URL', 'http://localhost/WEB_FINAL/');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    return $pdo;
}
