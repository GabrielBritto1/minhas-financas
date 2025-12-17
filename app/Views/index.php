<?php
require_once __DIR__ . '/layouts/header-private.php';
requireLogin();
$uid = currentUserId();
$stmt = $pdo->prepare("SELECT 
  SUM(CASE WHEN type='entrada' THEN amount ELSE 0 END) AS total_entrada,
  SUM(CASE WHEN type='saida' THEN amount ELSE 0 END) AS total_saida,
  SUM(CASE WHEN type='extra' THEN amount ELSE 0 END) AS total_extra,
  SUM(CASE WHEN type='investimento' THEN amount ELSE 0 END) AS total_investimento
FROM transactions WHERE user_id = :uid");
$stmt->execute([':uid' => $uid]);
$totals = $stmt->fetch();

$saldo = ($totals['total_entrada'] ?? 0) - ($totals['total_saida'] ?? 0);

$mesAtual = date('Y-m');
$mesAnterior = date('Y-m', strtotime('-1 month'));

function totaisPorMes(PDO $pdo, int $uid, string $mes)
{
   $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN type='entrada' THEN amount ELSE 0 END) AS entradas,
            SUM(CASE WHEN type='saida' THEN amount ELSE 0 END) AS saidas
        FROM transactions
        WHERE user_id = :uid
          AND DATE_FORMAT(date, '%Y-%m') = :mes
    ");
   $stmt->execute([
      ':uid' => $uid,
      ':mes' => $mes
   ]);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("
  SELECT category,
         SUM(CASE WHEN type='saida' THEN amount ELSE 0 END) AS total
  FROM transactions
  WHERE user_id = :uid
    AND DATE_FORMAT(date, '%Y-%m') = :mes
  GROUP BY category
  ORDER BY total DESC
");
$stmt->execute([
   ':uid' => $uid,
   ':mes' => $mesAtual
]);
$gastosPorCategoria = $stmt->fetchAll();

$stmt = $pdo->prepare("
  SELECT 
     g.id,
     g.category,
     g.amount AS meta,
     COALESCE(SUM(t.amount), 0) AS investido
  FROM goals g
  LEFT JOIN transactions t
    ON t.goal_id = g.id
   AND t.type = 'investimento'
  WHERE g.user_id = :uid
    AND g.month = :mes
  GROUP BY g.id
");
$stmt->execute([
   ':uid' => $uid,
   ':mes' => $mesAtual
]);
$metas = $stmt->fetchAll();

$atual = totaisPorMes($pdo, $uid, $mesAtual);
$anterior = totaisPorMes($pdo, $uid, $mesAnterior);

$saldoAtual = ($atual['entradas'] ?? 0) - ($atual['saidas'] ?? 0);
$saldoAnterior = ($anterior['entradas'] ?? 0) - ($anterior['saidas'] ?? 0);

$graficoComparativo = [
   'Entradas' => [
      $anterior['entradas'] ?? 0,
      $atual['entradas'] ?? 0
   ],
   'Saídas' => [
      $anterior['saidas'] ?? 0,
      $atual['saidas'] ?? 0
   ],
   'Saldo' => [
      ($anterior['entradas'] ?? 0) - ($anterior['saidas'] ?? 0),
      ($atual['entradas'] ?? 0) - ($atual['saidas'] ?? 0)
   ]
];

function variacaoPercentual($atual, $anterior)
{
   if ($anterior == 0 && $atual == 0) return 0;
   if ($anterior == 0) return 100;
   return (($atual - $anterior) / abs($anterior)) * 100;
}

$varEntradas = variacaoPercentual($atual['entradas'], $anterior['entradas']);
$varSaidas   = variacaoPercentual($atual['saidas'], $anterior['saidas']);
$varSaldo    = variacaoPercentual($saldoAtual, $saldoAnterior);

$months = [];
$entrada = [];
$saida = [];
$extra = [];
$investimento = [];
$alertas = [];

for ($i = 11; $i >= 0; $i--) {
   $m = (new DateTime("first day of -$i months"))->format('Y-m');
   $months[] = (new DateTime("$m-01"))->format('M Y');
   $stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN type='entrada' THEN amount ELSE 0 END) AS entrada,
    SUM(CASE WHEN type='saida' THEN amount ELSE 0 END) AS saida,
    SUM(CASE WHEN type='extra' THEN amount ELSE 0 END) AS extra,
    SUM(CASE WHEN type='investimento' THEN amount ELSE 0 END) AS investimento
  FROM transactions WHERE user_id = :uid AND DATE_FORMAT(date,'%Y-%m') = :ym");
   $stmt->execute([':uid' => $uid, ':ym' => $m]);
   $r = $stmt->fetch();
   $entrada[] = (float)($r['entrada'] ?? 0);
   $saida[] = (float)($r['saida'] ?? 0);
   $extra[] = (float)($r['extra'] ?? 0);
   $investimento[] = (float)($r['investimento'] ?? 0);
}

$stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id=:uid ORDER BY date DESC, id DESC LIMIT 10');
$stmt->execute([':uid' => $uid]);
$last = $stmt->fetchAll();
?>
<?php foreach ($alertas as $a): ?>
   <div class="alert alert-<?= $a['tipo'] ?> d-flex align-items-center mb-3">
      <?= $a['mensagem'] ?>
   </div>
<?php endforeach; ?>

<div class="card shadow-sm border-0 mb-4">
   <div class="card-body">
      <h6 class="card-title">Metas mensais</h6>
      <?php foreach ($metas as $m):
         $percent = $m['meta'] > 0
            ? min(100, ($m['investido'] / $m['meta']) * 100)
            : 0;
      ?>
         <div class="mb-3">
            <div class="d-flex justify-content-between">
               <small><?= htmlspecialchars($m['category']) ?></small>
               <small>
                  R$ <?= number_format($m['investido'], 2, ',', '.') ?>
                  /
                  R$ <?= number_format($m['meta'], 2, ',', '.') ?>
               </small>
            </div>
            <div class="progress">
               <div class="progress-bar bg-success"
                  style="width: <?= $percent ?>%">
               </div>
            </div>
         </div>
      <?php endforeach; ?>
   </div>
</div>

<div class="row mb-4">
   <div class="col-md-6">
      <div class="card shadow border-0">
         <div class="card-body">
            <small class="text-muted">Saldo Atual</small>
            <h2 class="fw-bold <?= $saldo >= 0 ? 'text-success' : 'text-danger' ?>">
               R$ <?= number_format($saldo, 2, ',', '.') ?>
            </h2>
            <small class="text-muted">Entradas − Saídas</small>
         </div>
      </div>
   </div>
</div>

<div class="row g-3 mb-4">
   <?php
   $cards = [
      ['Entradas', $totals['total_entrada'], 'success', 'fa-arrow-up'],
      ['Saídas', $totals['total_saida'], 'danger', 'fa-arrow-down'],
      ['Extras', $totals['total_extra'], 'info', 'fa-plus'],
      ['Investimentos', $totals['total_investimento'], 'warning', 'fa-chart-line'],
   ];
   ?>

   <?php foreach ($cards as [$label, $value, $color, $icon]): ?>
      <div class="col-md-3">
         <div class="card shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
               <div>
                  <small class="text-muted"><?= $label ?></small>
                  <h5 class="fw-bold mb-0 text-<?= $color ?>">
                     R$ <?= number_format($value ?? 0, 2, ',', '.') ?>
                  </h5>
               </div>
               <i class="fa-solid <?= $icon ?> fa-lg text-<?= $color ?>"></i>
            </div>
         </div>
      </div>
   <?php endforeach; ?>
</div>

<div class="row mb-4">
   <div class="col-md-12">
      <div class="card shadow-sm border-0">
         <div class="card-body">
            <h6 class="card-title mb-3">
               Comparativo com o mês anterior
            </h6>

            <div class="row text-center">

               <?php
               $comparativos = [
                  ['Entradas', $atual['entradas'], $varEntradas, 'success'],
                  ['Saídas',   $atual['saidas'],   $varSaidas,   'danger'],
                  ['Saldo',    $saldoAtual,        $varSaldo,    $saldoAtual >= 0 ? 'success' : 'danger'],
               ];
               ?>

               <?php foreach ($comparativos as [$label, $valor, $var, $color]): ?>
                  <div class="col-md-4">
                     <small class="text-muted"><?= $label ?></small>
                     <h5 class="fw-bold text-<?= $color ?>">
                        R$ <?= number_format($valor ?? 0, 2, ',', '.') ?>
                     </h5>

                     <span class="badge bg-<?=
                                             $var > 0 ? 'success' : ($var < 0 ? 'danger' : 'secondary')
                                             ?>">
                        <?= $var > 0 ? '↑' : ($var < 0 ? '↓' : '=') ?>
                        <?= number_format(abs($var), 1, ',', '.') ?>%
                     </span>
                  </div>
               <?php endforeach; ?>

            </div>
         </div>
      </div>
   </div>
</div>

<div class="row">
   <div class="col">
      <div class="card text-dark shadow p-3 rounded">
         <div class="card-body">
            <h5 class="card-title text-center text-muted text-uppercase">Gráfico mensal</h5>
            <canvas id="chart" height="100"></canvas>
         </div>
      </div>
   </div>

   <div class="col">
      <div class="card text-dark shadow p-3 rounded">
         <div class="card-body">
            <h6 class="card-title">Comparativo mensal</h6>
            <canvas id="chartComparativo" height="120"></canvas>
         </div>
      </div>
   </div>
</div>

<div class="row mt-2">
   <div class="col">
      <div class="card text-dark shadow p-3 rounded">
         <div class="card-body">
            <h6 class="card-title">Gastos por categoria (mês atual)</h6>
            <table class="table table-sm">
               <thead>
                  <tr>
                     <th>Categoria</th>
                     <th class="text-end">Total</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($gastosPorCategoria as $c): ?>
                     <tr>
                        <td><?= htmlspecialchars($c['category']) ?></td>
                        <td class="text-end fw-semibold">
                           R$ <?= number_format($c['total'], 2, ',', '.') ?>
                        </td>
                     </tr>
                  <?php endforeach; ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <div class="col">
      <div class="card text-dark shadow p-3 rounded">
         <div class="card-body">
            <h5 class="card-title">Últimas transações</h5>
            <div class="table-responsive">
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>Data</th>
                        <th>Desc</th>
                        <th>Tipo</th>
                        <th>Categoria</th>
                        <th class="text-end">Valor</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (empty($last)): ?>
                        <tr>
                           <td colspan="5" class="text-center text-muted py-4">
                              Nenhuma transação encontrada
                           </td>
                        </tr>
                     <?php else: ?>
                        <?php foreach ($last as $t): ?>
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
                              <td class="text-end fw-semibold">
                                 R$ <?= number_format($t['amount'], 2, ',', '.') ?>
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
   const labels = <?= json_encode($months) ?>;
   const entrada = <?= json_encode($entrada) ?>;
   const saida = <?= json_encode($saida) ?>;
   const extra = <?= json_encode($extra) ?>;
   const investimento = <?= json_encode($investimento) ?>;
   const ctx = document.getElementById('chart').getContext('2d');
   new Chart(ctx, {
      type: 'bar',
      data: {
         labels,
         datasets: [{
               label: 'Entradas',
               data: entrada,
               backgroundColor: '#198754'
            },
            {
               label: 'Saídas',
               data: saida,
               backgroundColor: '#dc3545'
            },
            {
               label: 'Extras',
               data: extra,
               backgroundColor: '#0dcaf0'
            },
            {
               label: 'Investimentos',
               data: investimento,
               backgroundColor: '#ffc107'
            }
         ]
      },
      options: {
         responsive: true,
         plugins: {
            legend: {
               position: 'bottom',
               labels: {
                  boxWidth: 12,
                  padding: 15
               }
            }
         },
         scales: {
            x: {
               grid: {
                  display: false
               }
            },
            y: {
               beginAtZero: true,
               grid: {
                  color: '#e9ecef'
               }
            }
         }
      }
   });

   const ctxComp = document.getElementById('chartComparativo');

   new Chart(ctxComp, {
      type: 'bar',
      data: {
         labels: ['Entradas', 'Saídas', 'Saldo'],
         datasets: [{
               label: 'Mês anterior',
               data: <?= json_encode(array_column($graficoComparativo, 0)) ?>,
               backgroundColor: '#adb5bd'
            },
            {
               label: 'Mês atual',
               data: <?= json_encode(array_column($graficoComparativo, 1)) ?>,
               backgroundColor: '#198754'
            }
         ]
      },
      options: {
         responsive: true,
         plugins: {
            legend: {
               position: 'bottom'
            }
         },
         scales: {
            y: {
               beginAtZero: true
            }
         }
      }
   });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>