<?php
require_once __DIR__ . '/layouts/header-public.php';
if (isLogged()) {
   header('Location: index.php');
   exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $name = trim($_POST['name'] ?? '');
   $email = trim($_POST['email'] ?? '');
   $password = $_POST['password'] ?? '';
   if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
      $errors[] = 'Preencha corretamente.';
   } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (:n,:e,:p)');
      $stmt->execute([':n' => $name, ':e' => $email, ':p' => $hash]);
      header('Location: index.php?route=login');
      exit;
   }
}
?>
<div class="row justify-content-center">
   <div class="col-md-6">
      <div class="card text-dark">
         <div class="card-body">
            <h5 class="card-title">Registrar</h5>
            <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', $errors) ?></div><?php endif; ?>
            <form method="post" class="needs-validation" novalidate>
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