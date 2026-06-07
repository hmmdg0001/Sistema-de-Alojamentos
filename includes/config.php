<?php
# Ficheiro responsável para se ligar á base de dados e a URL do website

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tpfinal_db');

define('SITE_NAME', 'Alojamentos Online'); # Nome do website
define('BASE_URL', 'http://localhost/WEB_FINAL/'); # URL do website
define('CSS_URL', BASE_URL . 'css/style.css'); # CSS do website


function conectar(): PDO { # Função que conecta á base de dados
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
