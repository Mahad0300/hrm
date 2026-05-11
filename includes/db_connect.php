<?php
// includes/db_connect.php
date_default_timezone_set('Asia/Karachi');
// Database connection using PDO

$host = 'localhost';
$db = 'hrm';
$user = 'root';
$pass = ''; // XAMPP default is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Sync MySQL timezone with PHP
     $now = new DateTime();
     $mins = $now->getOffset() / 60;
     $sgn = ($mins < 0 ? -1 : 1);
     $mins = abs($mins);
     $hrs = floor($mins / 60);
     $mins %= 60;
     $offset = sprintf('%+03d:%02d', $hrs*$sgn, $mins);
     $pdo->exec("SET time_zone='$offset';");
} catch (\PDOException $e) {
     die("Connection failed: " . $e->getMessage());
}
?>