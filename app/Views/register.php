<?php require_once __DIR__ . '/layouts/header-public.php'; ?>

<div class="row justify-content-center">
   <div class="col-md-6">
      <div class="card text-dark">
         <div class="card-body">
            <h5 class="card-title">Registrar</h5>
            <?php if (!empty($_GET['error'])): ?><div class="alert alert-danger"><?= implode('<br>', $errors) ?></div><?php endif; ?>
            <form method="post" action="index.php?route=process_register" class="needs-validation" novalidate>
               <div class="mb-2"><label>Nome</label><input name="name" class="form-control" required></div>
               <div class="mb-2"><label>Email</label><input name="email" type="email" class="form-control" required></div>
               <div class="mb-2"><label>Senha (min 6)</label><input name="password" type="password" class="form-control" required minlength="6"></div>
               <button class="btn btn-primary">Criar conta</button>
            </form>
         </div>
      </div>
   </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>