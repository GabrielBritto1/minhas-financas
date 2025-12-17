<?php
require __DIR__ . '/../../bootstrap.php';

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
   header('Location: index.php?route=register&error=1');
   exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
$stmt->execute([':e' => $email]);

if ($stmt->fetch()) {
   header('Location: index.php?route=register&exists=1');
   exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('
   INSERT INTO users (name, email, password)
   VALUES (:n, :e, :p)
');

$stmt->execute([
   ':n' => $name,
   ':e' => $email,
   ':p' => $hash
]);

$_SESSION['user_id']   = $pdo->lastInsertId();
$_SESSION['user_name'] = $name;

header('Location: index.php?route=home');
exit;
