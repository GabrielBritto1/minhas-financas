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
   if ($value === null || $value === '') {
      return null;
   }
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
$expenseKind   = $_POST['expense_kind'] ?? null;
$paymentMethod = $_POST['payment_method'] ?? null;
$installments      = $_POST['installments'] ?? null;
$installmentValue  = normalizeMoney($_POST['installment_value'] ?? null);

if (empty($category) && !empty($_POST['category_select'])) {
   $category = $_POST['category_select'];
}

$amount = normalizeMoney($_POST['amount'] ?? null);

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
          category=:cat, amount=:amount, goal_id=:goal_id,
          expense_kind=:expense_kind, payment_method=:payment_method,
          installments=:installments, installment_value=:installment_value
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
      ':uid'     => $uid,
      ':expense_kind'   => $expenseKind,
      ':payment_method' => $paymentMethod,
      ':installments'      => $installments,
      ':installment_value' => $installmentValue
   ]);
   $transactionId = $id;
}

/* REGRA DO CRÉDITO (ANTES DO INSERT) */
if ($paymentMethod === 'credito') {
   $installments = (int)($installments ?? 0);

   if ($installments > 1) {
      // calcula SEMPRE no backend
      $installmentValue = round($amount / $installments, 2);
   } else {
      $installmentValue = null;
      $installments = null;
   }
} else {
   $installments = null;
   $installmentValue = null;
}

/* INSERT */
$stmt = $pdo->prepare("
   INSERT INTO transactions
   (user_id, date, description, type, category, amount, goal_id,
    expense_kind, payment_method, installments, installment_value)
   VALUES
   (:uid, :date, :desc, :type, :cat, :amount, :goal_id,
    :expense_kind, :payment_method, :installments, :installment_value)
");

$stmt->execute([
   ':uid'     => $uid,
   ':date'    => $date,
   ':desc'    => $description,
   ':type'    => $type,
   ':cat'     => $category,
   ':amount'  => $amount,
   ':goal_id' => $goalId ?: null,
   ':expense_kind'   => $expenseKind,
   ':payment_method' => $paymentMethod,
   ':installments'      => $installments,
   ':installment_value' => $installmentValue
]);

$transactionId = $pdo->lastInsertId();

/* PARCELAS CRÉDITO (SOMENTE SE VÁLIDO) */
if (
   $paymentMethod === 'credito'
   && $installments > 1
   && $installmentValue !== null
) {

   for ($i = 1; $i <= $installments; $i++) {
      $stmt = $pdo->prepare("
         INSERT INTO installments
         (user_id, transaction_id, description,
          installment_number, total_installments, amount)
         VALUES
         (:uid, :tid, :desc, :num, :total, :amount)
      ");

      $stmt->execute([
         ':uid'    => $uid,
         ':tid'    => $transactionId,
         ':desc'   => $description,
         ':num'    => $i,
         ':total'  => $installments,
         ':amount' => $installmentValue
      ]);
   }
}

header('Location: index.php?route=transactions');
exit;
