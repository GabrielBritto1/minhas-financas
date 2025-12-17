<?php
require __DIR__ . '/../../bootstrap.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
   $_SESSION['user_id'] = $user['id'];
   $_SESSION['user_name'] = $user['name'] ?? 'Usuário';

   header('Location: index.php?route=home');
   exit;
}

header('Location: index.php?route=login&error=1');
exit;
