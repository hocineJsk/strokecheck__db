<?php

// Sur Railway, ces variables sont remplies automatiquement
$host = getenv('MYSQLHOST') ?: '127.0.0.1';
$dbname = getenv('MYSQLDATABASE') ?: 'stroke_risk_db';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';  
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("La connexion à la base de données a échoué: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>