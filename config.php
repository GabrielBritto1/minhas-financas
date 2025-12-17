<?php
// config.php - ajuste DB e sessão
session_start();

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'financas');
define('DB_USER', 'root');
define('DB_PASS', '');

$options = [
   PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
   $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
   die('Erro DB: ' . $e->getMessage());
}

function isLogged()
{
   return !empty($_SESSION['user_id']);
}
function requireLogin()
{
   if (!isLogged()) {
      header('Location: index.php?route=login');
      exit;
   }
}
function currentUserId()
{
   return $_SESSION['user_id'] ?? null;
}
