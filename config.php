<?php
// Configuration de la base de données
// Use environment variables for better security
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'qcm_culture';
$db_user = $_ENV['DB_USER'] ?? 'root'; // Modifier selon votre configuration
$db_pass = $_ENV['DB_PASS'] ?? ''; // Modifier selon votre configuration

define('DB_HOST', $db_host);
define('DB_NAME', $db_name);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);

// Paramètres de l'application
define('QUESTIONS_PAR_TEST', 20);
define('TEMPS_MAX_MINUTES', 30);

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session
session_start();
?>