<?php
// On vérifie si on est sur Railway en cherchant une variable d'environnement spécifique
$isRailway = getenv('MYSQLHOST') !== false;

if ($isRailway) {
    // --- Configuration RAILWAY (En ligne) ---
    $host = getenv('mysql.railway.internal');
    $dbname = getenv('railway');
    $user = getenv('root');
    $pass = getenv('xwiipZsXlxEyaOgZCKtPvQWESaYnEjRQ');
    $port = getenv('3306');
} else {
    // --- Configuration LOCALHOST (Ton PC) ---
    $host = 'localhost';
    $dbname = 'club_bultaco';
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