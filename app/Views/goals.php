<?php
require_once __DIR__ . '/layouts/header-private.php';
requireLogin();

$mesAtual = date('Y-m');

$stmt = $pdo->prepare("
  SELECT * FROM goals
  WHERE user_id = :uid AND month = :mes
  ORDER BY category
");
$stmt->execute([
   ':uid' => currentUserId(),
   ':mes' => $mesAtual
]);
$goals = $stmt->fetchAll();
?>

<h4 class="mb-4">🎯 Metas Mensais (<?= date('m/Y') ?>)</h4>

<div class="card mb-4">
   <div class="card-body">
      <form method="post" action="index.php?route=process_goal" class="row g-3">
         <div class="col-md-5">
            <label class="form-label">Categoria</label>
            <input type="text" name="category" class="form-control" required>
         </div>

         <div class="col-md-4">
            <label class="form-label">Valor da meta (R$)</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
         </div>

         <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-success w-100">
               <i class="fa fa-plus"></i> Salvar Meta
            </button>
         </div>
      </form>
   </div>
</div>

<div class="card">
   <div class="card-body">
      <h6 class="card-title">Metas cadastradas</h6>

      <?php if (empty($goals)): ?>
         <p class="text-muted">Nenhuma meta cadastrada.</p>
      <?php else: ?>
         <table class="table table-sm align-middle">
            <thead>
               <tr>
                  <th>Categoria</th>
                  <th class="text-end">Valor</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($goals as $g): ?>
                  <tr>
                     <td><?= htmlspecialchars($g['category']) ?></td>
                     <td class="text-end fw-semibold">
                        R$ <?= number_format($g['amount'], 2, ',', '.') ?>
                     </td>
                  </tr>
               <?php endforeach; ?>
            </tbody>
         </table>
      <?php endif; ?>
   </div>
</div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>