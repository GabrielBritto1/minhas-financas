<!doctype html>
<html lang="pt-br">

<head>
   <meta charset="utf-8">
   <meta property="og:title" content="Minhas Finanças" />
   <meta property="og:description" content="Controle suas finanças com facilidade e eficiência." />
   <meta property="og:image" content="https://labmakerifes.com/minhas-financas/public/assets/img/logo_financas.png" />
   <meta property="og:url" content="https://labmakerifes.com/minhas-financas/public/index.php?route=login" />
   <meta property="og:type" content="website" />
   <meta name="viewport" content="width=device-width,initial-scale=1">
   <title>Minhas Finanças</title>

   <link rel="shortcut icon" href="assets/img/logo_financas.png">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.10/dist/sweetalert2.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
   <link rel="stylesheet" href="assets/css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="bg-light">

   <!-- TOPBAR -->
   <!-- TOPBAR COM MENU -->
   <nav class="navbar navbar-dark bg-dark shadow-sm">
      <div class="container-fluid">

         <!-- Botão menu -->
         <button class="btn btn-dark" data-bs-toggle="offcanvas" data-bs-target="#menu">
            <i class="fa fa-bars"></i>
         </button>

         <!-- Logo -->
         <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/img/logo_financas.png" width="36" class="rounded-circle">
            <strong>Minhas Finanças</strong>
         </a>

         <!-- Usuário -->
         <div class="d-flex align-items-center gap-3">
            <span class="text-light small d-none d-md-inline">
               <i class="fa fa-user"></i> <?= $_SESSION['user_name'] ?? 'Usuário' ?>
            </span>
            <a href="index.php?route=logout" class="btn btn-outline-light btn-sm">
               Sair
            </a>
         </div>

      </div>
   </nav>

   <div class="offcanvas offcanvas-start" tabindex="-1" id="menu">
      <div class="offcanvas-header">
         <h5 class="offcanvas-title">
            <i class="fa fa-wallet me-2"></i> Menu
         </h5>
         <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>

      <div class="offcanvas-body p-0">
         <ul class="nav nav-pills flex-column gap-1 p-3">

            <li class="nav-item">
               <a class="nav-link text-dark" href="index.php">
                  <i class="fa fa-chart-line me-2"></i> Dashboard
               </a>
            </li>

            <li class="nav-item">
               <a class="nav-link text-dark" href="index.php?route=transactions">
                  <i class="fa fa-list me-2"></i> Transações
               </a>
            </li>

            <li class="nav-item">
               <a class="nav-link text-dark" href="index.php?route=goals">
                  <i class="fa fa-bullseye me-2"></i> Metas
               </a>
            </li>

            <li class="nav-item">
               <a class="nav-link text-dark" href="index.php?route=installments">
                  <i class="fa fa-credit-card me-2"></i> Parcelas
               </a>
            </li>
         </ul>
      </div>
   </div>

   <div class="container-fluid p-4">