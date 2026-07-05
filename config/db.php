<?php
// Ganti BASE_URL dengan '' untuk Vercel, atau '/habit-tracker' jika di subfolder XAMPP
define('BASE_URL', '/habit-tracker');

$host = getenv('MYSQLHOST')     ?: 'localhost';
$port = getenv('MYSQLPORT')     ?: '3306';
$db   = getenv('MYSQLDATABASE') ?: 'dbhabittracker';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('<p style="color:red;padding:20px;font-family:sans-serif;">Koneksi database gagal: ' . $e->getMessage() . '</p>');
}
