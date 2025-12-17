<?php
require __DIR__ . '/../bootstrap.php';

$route = $_GET['route'] ?? 'home';

/**
 * Rotas privadas
 */
$privateRoutes = [
   'home',
   'transactions',
   'edit_transaction_view',
   'goals'
];

/**
 * Valida login ANTES de carregar qualquer view
 */
if (in_array($route, $privateRoutes, true)) {
   requireLogin();
}

/**
 * Roteamento
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

   /**
    * Actions
    */
   case 'process_login':
      require __DIR__ . '/../app/Actions/process_login.php';
      break;

   case 'process_goal':
      require __DIR__ . '/../app/Actions/process_goal.php';
      break;

   case 'process_transaction':
      require __DIR__ . '/../app/Actions/process_transaction.php';
      break;

   case 'delete_transaction':
      require __DIR__ . '/../app/Actions/delete_transaction.php';
      break;

   case 'export_csv':
      require __DIR__ . '/../app/Actions/export_csv.php';
      break;

   case 'export_pdf':
      require __DIR__ . '/../app/Actions/export_pdf.php';
      break;

   default:
      http_response_code(404);
      echo 'Página não encontrada';
}
