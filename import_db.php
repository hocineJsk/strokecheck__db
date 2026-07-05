<?php
$host = getenv('MYSQLHOST') ?: '127.0.0.1';
$dbname = getenv('MYSQLDATABASE') ?: 'stroke_risk_db';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';  
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Lire le fichier SQL
$sql = file_get_contents('setup_db.sql');

// Exécuter les requêtes
if ($conn->multi_query($sql)) {
    echo "<h1>Succès !</h1><p>Les tables de la base de données ont été créées avec succès.</p>";
} else {
    echo "Erreur lors de la création : " . $conn->error;
}
$conn->close();
?>
