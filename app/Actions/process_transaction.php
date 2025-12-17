<?php
require_once __DIR__ . '/../../config.php';

if (!isLogged()) {
   header('Location: index.php?route=login');
   exit;
}

$uid = currentUserId();

/* FUNÇÃO PARA NORMALIZAR MOEDA */
function normalizeMoney($value)
{
   $value = trim($value);
   $value = str_replace('.', '', $value);   // remove milhar
   $value = str_replace(',', '.', $value);  // vírgula → ponto
   return $value;
}

/* DADOS BÁSICOS */
$id          = $_POST['id'] ?? null;
$date        = $_POST['date'] ?? null;
$description = $_POST['description'] ?? null;
$type        = $_POST['type'] ?? null;
$category    = $_POST['category'] ?? null;

if (empty($category) && !empty($_POST['category_select'])) {
   $category = $_POST['category_select'];
}

$amount = normalizeMoney($_POST['amount'] ?? 0);

if (!$date || !$type || !$amount) {
   die('Dados inválidos');
}

/* META (SE INVESTIMENTO) */
$goalId = $_POST['goal_id'] ?? null;

if ($type === 'investimento' && $goalId === 'new') {
   $stmt = $pdo->prepare("
      INSERT INTO goals (user_id, category, amount, month)
      VALUES (:uid, :cat, :amount, :month)
   ");
   $stmt->execute([
      ':uid'    => $uid,
      ':cat'    => $_POST['new_goal_category'],
      ':amount' => normalizeMoney($_POST['new_goal_amount']),
      ':month'  => date('Y-m')
   ]);

   $goalId = $pdo->lastInsertId();
}

/* UPDATE */
if ($id) {
   $stmt = $pdo->prepare("
      UPDATE transactions
      SET date=:date, description=:desc, type=:type,
          category=:cat, amount=:amount, goal_id=:goal_id
      WHERE id=:id AND user_id=:uid
   ");

   $stmt->execute([
      ':date'    => $date,
      ':desc'    => $description,
      ':type'    => $type,
      ':cat'     => $category,
      ':amount'  => $amount,
      ':goal_id' => $goalId ?: null,
      ':id'      => $id,
      ':uid'     => $uid
   ]);
}
/* INSERT */ else {
   $stmt = $pdo->prepare("
      INSERT INTO transactions
      (user_id, date, description, type, category, amount, goal_id)
      VALUES
      (:uid, :date, :desc, :type, :cat, :amount, :goal_id)
   ");

   $stmt->execute([
      ':uid'     => $uid,
      ':date'    => $date,
      ':desc'    => $description,
      ':type'    => $type,
      ':cat'     => $category,
      ':amount'  => $amount,
      ':goal_id' => $goalId ?: null
   ]);
}

header('Location: index.php?route=transactions');
exit;
