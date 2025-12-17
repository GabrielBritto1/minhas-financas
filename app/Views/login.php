<?php require_once __DIR__ . '/layouts/header-public.php'; ?>

<div class="row justify-content-center align-items-center" style="min-height:70vh;">
   <div class="col-md-5 col-lg-4">
      <div class="card shadow border-0">
         <div class="card-body p-4 text-dark">

            <div class="text-center mb-3">
               <img src="assets/img/logo_financas.png" width="70" class="rounded-circle mb-2" alt="">
               <h4 class="fw-bold mb-1">Minhas Finanças</h4>
               <small class="text-muted">Acesse sua conta</small>
            </div>

            <?php if (!empty($_GET['error'])): ?>
               <div class="alert alert-danger text-center">
                  E-mail ou senha inválidos.
               </div>
            <?php endif; ?>

            <form method="post" action="index.php?route=process_login" novalidate>

               <div class="mb-3">
                  <label class="form-label">E-mail</label>
                  <div class="input-group">
                     <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                     <input type="email" name="email" class="form-control" required autofocus>
                  </div>
               </div>

               <div class="mb-3">
                  <label class="form-label">Senha</label>
                  <div class="input-group">
                     <span class="input-group-text"><i class="fa fa-lock"></i></span>
                     <input type="password" name="password" class="form-control" required>
                  </div>
               </div>

               <button class="btn btn-primary w-100 mb-3">
                  <i class="fa fa-right-to-bracket me-1"></i>
                  Entrar
               </button>
            </form>

            <div class="text-center">
               <small class="text-muted">
                  Não tem conta?
                  <a href="index.php?route=register" class="fw-semibold">Criar agora</a>
               </small>
            </div>
         </div>
      </div>
   </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>