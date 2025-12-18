<?php
require_once __DIR__ . '/layouts/header-private.php';
requireLogin();

$stmt = $pdo->prepare("
   SELECT *
   FROM installments
   WHERE user_id = :uid
   ORDER BY paid ASC, transaction_id, installment_number
");
$stmt->execute([':uid' => currentUserId()]);
$rows = $stmt->fetchAll();
?>

<h4 class="mb-3">Parcelas</h4>

<table class="table table-hover align-middle">
   <thead>
      <tr>
         <th>Produto</th>
         <th>Parcela</th>
         <th>Valor</th>
         <th>Status</th>
         <th>Ação</th>
      </tr>
   </thead>
   <tbody>
      <?php foreach ($rows as $p): ?>
         <tr>
            <td><?= htmlspecialchars($p['description']) ?></td>
            <td><?= $p['installment_number'] ?>/<?= $p['total_installments'] ?></td>
            <td>R$ <?= number_format($p['amount'], 2, ',', '.') ?></td>
            <td>
               <?php if ($p['paid']): ?>
                  <span class="badge bg-success">Paga</span>
               <?php else: ?>
                  <span class="badge bg-warning text-dark">Pendente</span>
               <?php endif; ?>
            </td>
            <td>
               <?php if (!$p['paid']): ?>
                  <a href="index.php?route=pay_installment&id=<?= $p['id'] ?>"
                     class="btn btn-sm btn-success">
                     Marcar como paga
                  </a>
               <?php else: ?>
                  —
               <?php endif; ?>
            </td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>

<?php require_once __DIR__ . '/footer.php'; ?>