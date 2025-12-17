<?php
require_once __DIR__ . '/layouts/header-private.php';
requireLogin();
$id = $_GET['id'] ?? null;
$uid = currentUserId();
if (!$id) {
   header('Location: index.php?route=transactions');
   exit;
}
$stmt = $pdo->prepare('SELECT * FROM transactions WHERE id=:id AND user_id=:uid');
$stmt->execute([':id' => $id, ':uid' => $uid]);
$t = $stmt->fetch();
if (!$t) {
   header('Location: index.php?route=transactions');
   exit;
}
$cats = $pdo->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="row justify-content-center">
   <div class="col-md-6">
      <div class="card text-dark">
         <div class="card-body">
            <h5 class="card-title">Editar Transação</h5>
            <form action="index.php?route=process_transaction" method="post" class="needs-validation" novalidate>
               <input type="hidden" name="id" value="<?= $t['id'] ?>">
               <div class="mb-2"><label>Data</label><input type="date" name="date" class="form-control" required value="<?= $t['date'] ?>"></div>
               <div class="mb-2"><label>Descrição</label><input type="text" name="description" class="form-control" maxlength="255" value="<?= htmlspecialchars($t['description']) ?>"></div>
               <div class="mb-2"><label>Tipo</label>
                  <select name="type" class="form-select" required>
                     <option value="entrada" <?= $t['type'] == 'entrada' ? 'selected' : '' ?>>Entrada</option>
                     <option value="saida" <?= $t['type'] == 'saida' ? 'selected' : '' ?>>Saída</option>
                     <option value="extra" <?= $t['type'] == 'extra' ? 'selected' : '' ?>>Extra</option>
                  </select>
               </div>
               <label class="form-label">Categoria</label>
               <select name="category_select" id="catSelect" class="form-select mb-2">
                  <option value="">Selecione...</option>
                  <?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $t['category'] === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
                  <option value="Outros">Outros (digitar)</option>
               </select>
               <input type="text" name="category" id="catText" class="form-control mb-2" placeholder="Digite a categoria" style="display:none;" value="<?= htmlspecialchars($t['category']) ?>">
               <div class="mb-2"><label>Valor</label><input type="text" name="amount" class="form-control money" required value="<?= number_format($t['amount'], 2, ',', '.') ?>"></div>
               <div class="d-flex gap-2"><button class="btn btn-primary">Atualizar</button><a href="index.php?route=transactions" class="btn btn-secondary">Cancelar</a></div>
            </form>
         </div>
      </div>
   </div>
</div>
</div>

<script>
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
</script>

<?php require_once __DIR__ . '/footer.php'; ?>