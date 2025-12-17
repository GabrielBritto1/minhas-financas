<?php
require_once __DIR__ . '/layouts/header-private.php';
requireLogin();
$uid = currentUserId();
$cats = $pdo->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);

$goals = $pdo->prepare("
   SELECT id, category, amount
   FROM goals
   WHERE user_id = :uid
   ORDER BY category
");
$goals->execute([':uid' => $uid]);
$goals = $goals->fetchAll();


$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$category = $_GET['category'] ?? '';
$typeFilter = $_GET['filter_type'] ?? '';

$where = ['user_id = :uid'];
$params = [':uid' => $uid];
if ($start) {
   $where[] = 'date >= :start';
   $params[':start'] = $start;
}
if ($end) {
   $where[] = 'date <= :end';
   $params[':end'] = $end;
}
if ($category) {
   $where[] = 'category = :cat';
   $params[':cat'] = $category;
}
if ($typeFilter) {
   $where[] = 'type = :type';
   $params[':type'] = $typeFilter;
}
$sql = 'SELECT * FROM transactions WHERE ' . implode(' AND ', $where) . ' ORDER BY date DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();
?>
<div class="row">
   <div class="col-md-5">
      <div class="card text-dark">
         <div class="card-body">
            <h5 class="card-title mb-3 d-flex align-items-center gap-2">
               <i class="fa fa-plus-circle text-success"></i>
               Nova Transação
            </h5>
            <div class="btn-group mb-3 w-100">
               <button type="button" onclick="setType('entrada')" class="btn btn-outline-success btn-sm">
                  <i class="fa fa-arrow-up"></i> Entrada
               </button>
               <button type="button" onclick="setType('saida')" class="btn btn-outline-danger btn-sm">
                  <i class="fa fa-arrow-down"></i> Saída
               </button>
               <button type="button" onclick="setType('extra')" class="btn btn-outline-info btn-sm">
                  <i class="fa fa-plus"></i> Extra
               </button>
               <button type="button" onclick="setType('investimento')" class="btn btn-outline-warning btn-sm">
                  <i class="fa fa-chart-line"></i> Invest.
               </button>
            </div>
            <form action="index.php?route=process_transaction" method="post" class="needs-validation" novalidate>
               <input type="hidden" name="id" id="id">
               <div class="row">
                  <div class="col-md-6 mb-2">
                     <label>Data</label>
                     <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="col-md-6 mb-2">
                     <label>Valor</label>
                     <input type="text" name="amount" class="form-control money" required>
                  </div>
               </div>
               <div class="mb-2"><label>Descrição</label><input type="text" name="description" class="form-control" maxlength="255"></div>
               <div class="mb-2"><label>Tipo</label>
                  <select name="type" class="form-select" required>
                     <option value="entrada">Entrada</option>
                     <option value="saida">Saída</option>
                     <option value="extra">Extra</option>
                     <option value="investimento">Investimento</option>
                  </select>
               </div>
               <label class="form-label">Categoria</label>
               <select name="category_select" id="catSelect" class="form-select mb-2">
                  <option value="">Selecione...</option>
                  <?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
                  <option value="Outros">Outros (digitar)</option>
               </select>

               <!-- BLOCO META (INVESTIMENTO) -->
               <div id="goalBox" class="card border-warning mb-3" style="display:none;">
                  <div class="card-body">
                     <h6 class="card-title text-warning mb-2">
                        <i class="fa fa-bullseye"></i> Vincular a uma Meta
                     </h6>

                     <label class="form-label">Meta</label>
                     <select name="goal_id" id="goalSelect" class="form-select mb-2">
                        <option value="">Selecione uma meta</option>
                        <?php foreach ($goals as $g): ?>
                           <option value="<?= $g['id'] ?>">
                              <?= htmlspecialchars($g['category']) ?> —
                              R$ <?= number_format($g['amount'], 2, ',', '.') ?>
                           </option>
                        <?php endforeach; ?>
                        <option value="new">+ Criar nova meta</option>
                     </select>

                     <div id="newGoalFields" style="display:none;">
                        <input type="text" name="new_goal_category" class="form-control mb-2"
                           placeholder="Nome da nova meta">
                        <input type="text" name="new_goal_amount" class="form-control money"
                           placeholder="Valor da meta (R$)">
                     </div>
                  </div>
               </div>

               <input type="text" name="category" id="catText" class="form-control mb-2" placeholder="Digite a categoria" style="display:none;">
               <div class="d-flex gap-2"><button class="btn btn-success">Salvar</button><button type="reset" class="btn btn-secondary">Limpar</button></div>
            </form>
         </div>
      </div>
   </div>

   <div class="col-md-7">
      <div class="card text-dark">
         <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
               <h5 class="mb-0">
                  <i class="fa fa-list"></i> Transações
               </h5>
            </div>
            <form class="row g-2 mb-3" method="get" action="index.php">
               <input type="hidden" name="route" value="transactions">
               <div class="col-3"><input type="date" name="start" class="form-control" value="<?= $start ?>"></div>
               <div class="col-3"><input type="date" name="end" class="form-control" value="<?= $end ?>"></div>
               <div class="col-3">
                  <select name="category" class="form-select">
                     <option value="">Todas categorias</option>
                     <?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
                  </select>
               </div>
               <div class="col-3">
                  <select name="filter_type" class="form-select">
                     <option value="">Todos tipos</option>
                     <option value="entrada" <?= $typeFilter === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                     <option value="saida" <?= $typeFilter === 'saida' ? 'selected' : '' ?>>Saída</option>
                     <option value="extra" <?= $typeFilter === 'extra' ? 'selected' : '' ?>>Extra</option>
                     <option value="investimento" <?= $typeFilter === 'investimento' ? 'selected' : '' ?>>Investimento</option>
                  </select>
               </div>
               <div class="col-12 text-end mt-2">
                  <button class="btn btn-primary btn-sm">
                     <i class="fa fa-filter"></i> Filtrar
                  </button>
                  <a href="index.php?route=transactions" class="btn btn-outline-secondary btn-sm">
                     Limpar
                  </a>
                  <a class="btn btn-success btn-sm"
                     href="index.php?route=export_csv&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($typeFilter) ?>">
                     <i class="fa fa-file-csv"></i>
                  </a>
                  <a class="btn btn-danger btn-sm"
                     href="index.php?route=export_pdf&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($typeFilter) ?>">
                     <i class="fa fa-file-pdf"></i>
                  </a>
               </div>
            </form>

            <div class="table-responsive">
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>Data</th>
                        <th>Desc</th>
                        <th>Tipo</th>
                        <th>Categoria</th>
                        <th class="text-end">Valor</th>
                        <th>Ações</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (empty($transactions)): ?>
                        <tr>
                           <td colspan="6" class="text-center text-muted py-4">
                              Nenhuma transação encontrada com os filtros aplicados.
                           </td>
                        </tr>
                     <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                           <tr>
                              <td><?= date('d/m/Y', strtotime($t['date'])) ?></td>
                              <td><?= htmlspecialchars($t['description']) ?></td>
                              <td>
                                 <span class="badge bg-<?=
                                                         $t['type'] === 'entrada' ? 'success' : ($t['type'] === 'saida' ? 'danger' : ($t['type'] === 'extra' ? 'info' : 'warning'))
                                                         ?>">
                                    <?= strtoupper($t['type']) ?>
                                 </span>
                              </td>
                              <td><?= htmlspecialchars($t['category']) ?></td>
                              <td class="text-end fw-semibold <?= $t['type'] === 'saida' ? 'text-danger' : 'text-success' ?>">
                                 R$ <?= number_format($t['amount'], 2, ',', '.') ?>
                              </td>
                              <td class="text-end">
                                 <a href="index.php?route=edit_transaction_view&id=<?= $t['id'] ?>"
                                    class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fa fa-pen"></i>
                                 </a>

                                 <a href="index.php?route=delete_transaction&id=<?= $t['id'] ?>"
                                    class="btn btn-sm btn-outline-danger btn-delete"
                                    title="Excluir">
                                    <i class="fa fa-trash"></i>
                                 </a>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
</div>

<script>
   document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', function(event) {
         event.preventDefault();
         const url = this.href;

         Swal.fire({
            title: 'Confirma a exclusão?',
            text: "Esta ação não pode ser desfeita.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
         }).then((result) => {
            if (result.isConfirmed) {
               window.location.href = url;
            }
         });
      });
   });

   function setType(type) {
      const select = document.querySelector('[name=type]');
      select.value = type;

      document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
      event.target.closest('button').classList.add('active');
   }

   document.getElementById('catSelect').addEventListener('change', function() {
      const txt = document.getElementById('catText');
      if (this.value === 'Outros') {
         txt.style.display = 'block';
         txt.required = true;
         txt.value = '';
      } else {
         txt.style.display = 'none';
         txt.required = false;
         txt.value = this.value;
      }
   });

   const typeSelect = document.querySelector('[name=type]');
   const goalBox = document.getElementById('goalBox');
   const goalSelect = document.getElementById('goalSelect');
   const newGoalFields = document.getElementById('newGoalFields');

   function toggleGoalBox() {
      if (typeSelect.value === 'investimento') {
         goalBox.style.display = 'block';
      } else {
         goalBox.style.display = 'none';
         goalSelect.value = '';
         newGoalFields.style.display = 'none';
      }
   }

   typeSelect.addEventListener('change', toggleGoalBox);

   goalSelect.addEventListener('change', function() {
      newGoalFields.style.display = this.value === 'new' ? 'block' : 'none';
   });

   // quando clicar nos botões rápidos
   function setType(type) {
      const select = document.querySelector('[name=type]');
      select.value = type;

      document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
      event.target.closest('button').classList.add('active');

      toggleGoalBox();
   }
</script>

<?php require_once __DIR__ . '/footer.php'; ?>