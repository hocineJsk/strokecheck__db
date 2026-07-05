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

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'Normal_User'
    )",
    "CREATE TABLE IF NOT EXISTS predictions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        User_ID INT UNSIGNED NOT NULL,
        Risk_Level VARCHAR(50),
        Top_Factors TEXT,
        Risk_probability FLOAT,
        Prediction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_predictions_users FOREIGN KEY (User_ID) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS advice_log (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        Prediction_Id INT UNSIGNED NOT NULL,
        User_Id INT UNSIGNED NOT NULL,
        Ai_Advice TEXT,
        Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_advice_prediction FOREIGN KEY (Prediction_Id) REFERENCES predictions(id) ON DELETE CASCADE,
        CONSTRAINT fk_advice_user FOREIGN KEY (User_Id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS patient_profile (
        id INT UNSIGNED PRIMARY KEY,
        Age INT,
        Ever_Married VARCHAR(50),
        gender VARCHAR(50),
        work_type VARCHAR(100),
        residence_type VARCHAR(100),
        CONSTRAINT fk_profile_user FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS health_record (
        id INT UNSIGNED PRIMARY KEY,
        All_Other_Patient_Profile TEXT,
        CONSTRAINT fk_health_user FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

$success = true;
foreach ($queries as $query) {
    if (!$conn->query($query)) {
        echo "Erreur lors de la création de la table : " . $conn->error . "<br>";
        $success = false;
    }
}

if ($success) {
    echo "<h1>Succès ABSOLU !</h1><p>Toutes les tables (users, predictions, etc.) ont été créées proprement de zéro.</p>";
}
$conn->close();
?>
