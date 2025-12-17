<?php
require __DIR__ . '/../bootstrap.php';

$route = trim($_GET['route'] ?? 'login');

/**
 * ACTIONS (EXECUTAM ANTES DE QUALQUER HTML)
 */
$actionRoutes = [
   'process_login',
   'process_goal',
   'process_transaction',
   'delete_transaction',
   'export_csv',
   'export_pdf'
];

if (in_array($route, $actionRoutes, true)) {
   require __DIR__ . '/../app/Actions/' . $route . '.php';
   exit; // <<< ESSENCIAL
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
   <meta charset="UTF-8">

   <!-- OPEN GRAPH -->
   <meta property="og:title" content="Minhas Finanças">
   <meta property="og:description" content="Controle suas finanças com facilidade e eficiência.">
   <meta property="og:image"
      content="https://labmakerifes.com/minhas-financas/public/assets/img/logo_financas.png">
   <meta property="og:type" content="website">

   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Minhas Finanças</title>
</head>

<body>
   <?php

   /**
    * ROTAS PRIVADAS
    */
   $privateRoutes = ['home', 'transactions', 'edit_transaction_view', 'goals'];

   if (in_array($route, $privateRoutes, true)) {
      requireLogin();
   }

   /**
    * VIEWS
    */
   switch ($route) {
      case 'home':
         require __DIR__ . '/../app/Views/index.php';
         break;

      case 'login':
         require __DIR__ . '/../app/Views/login.php';
         break;

      case 'register':
         require __DIR__ . '/../app/Views/register.php';
         break;

      case 'logout':
         require __DIR__ . '/../app/Views/logout.php';
         break;

      case 'transactions':
         require __DIR__ . '/../app/Views/transactions.php';
         break;

      case 'edit_transaction_view':
         require __DIR__ . '/../app/Views/edit_transaction.php';
         break;

      case 'goals':
         require __DIR__ . '/../app/Views/goals.php';
         break;

      default:
         http_response_code(404);
         echo 'Página não encontrada';
   }
   ?>
</body>

</html>