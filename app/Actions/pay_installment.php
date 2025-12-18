<?php
require __DIR__ . '/../../bootstrap.php';
requireLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
   header('Location: index.php?route=installments');
   exit;
}

$stmt = $pdo->prepare("
   UPDATE installments
   SET paid = 1,
       paid_at = CURDATE()
   WHERE id = :id
     AND user_id = :uid
");
$stmt->execute([
   ':id'  => $id,
   ':uid' => currentUserId()
]);

header('Location: index.php?route=installments');
exit;
