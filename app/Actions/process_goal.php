<?php
require_once __DIR__ . '/../../config.php';
requireLogin();

$stmt = $pdo->prepare("
  INSERT INTO goals (user_id, category, amount, month)
  VALUES (:uid, :cat, :amount, :month)
");

$stmt->execute([
   ':uid' => currentUserId(),
   ':cat' => $_POST['category'],
   ':amount' => $_POST['amount'],
   ':month' => date('Y-m')
]);

header('Location: index.php?route=goals');
exit;
