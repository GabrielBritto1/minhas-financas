<?php
require_once __DIR__ . '/../../config.php';
if (!isLogged()) {
   header('Location: index.php?route=login');
   exit;
}
$id = $_GET['id'] ?? null;
$uid = currentUserId();
if ($id) {
   $stmt = $pdo->prepare('DELETE FROM transactions WHERE id=:id AND user_id=:uid');
   $stmt->execute([':id' => $id, ':uid' => $uid]);
}
header('Location: index.php?route=transactions');
exit;
