<?php
// On vérifie si on est sur Railway en cherchant une variable d'environnement spécifique
$isRailway = getenv('MYSQLHOST') !== false;

if ($isRailway) {
    // --- Configuration RAILWAY (En ligne) ---
    $host = getenv('MYSQLHOST');
    $dbname = getenv('MYSQLDATABASE');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $port = getenv('MYSQLPORT');
} else {
    // --- Configuration LOCALHOST (Ton PC) ---
    $host = 'localhost';
    $dbname = 'ton_nom_de_bdd_local';
    $user = 'root';
    $pass = ''; // Souvent vide sur XAMPP/WAMP
    $port = '3306';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}