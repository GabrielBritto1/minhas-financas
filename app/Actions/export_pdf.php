<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

require __DIR__ . '/../../bootstrap.php';
requireLogin();

$uid = currentUserId();

$start    = $_GET['start'] ?? null;
$end      = $_GET['end'] ?? null;
$category = $_GET['category'] ?? null;
$type     = $_GET['type'] ?? null;

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
if ($type) {
   $where[] = 'type = :type';
   $params[':type'] = $type;
}

$sql = 'SELECT * FROM transactions WHERE ' . implode(' AND ', $where) . ' ORDER BY date ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* Totais */
$totais = [
   'entrada'       => 0,
   'saida'         => 0,
   'extra'         => 0,
   'investimento'  => 0,
];

foreach ($rows as $r) {
   if (isset($totais[$r['type']])) {
      $totais[$r['type']] += $r['amount'];
   }
}

/* HTML do PDF */
$html = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #1f2937;
    }
    h1 {
        font-size: 18px;
        margin-bottom: 4px;
        color: #065f46;
    }
    .subtitle {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    thead {
        background: #065f46;
        color: #ffffff;
    }
    th, td {
        padding: 8px 6px;
        font-size: 11px;
    }
    th {
        text-align: left;
    }
    td.valor {
        text-align: right;
    }
    tbody tr:nth-child(even) {
        background: #f0fdf4;
    }
    tbody tr:nth-child(odd) {
        background: #ffffff;
    }
    .totais {
        margin-top: 20px;
        width: 100%;
    }
    .totais td {
        padding: 6px;
        font-size: 12px;
    }
    .totais .label {
        font-weight: bold;
        color: #065f46;
    }
    .footer {
        position: fixed;
        bottom: -10px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #9ca3af;
    }
</style>
</head>
<body>

<h1>Relatório de Transações</h1>
<div class="subtitle">
    Gerado em ' . date('d/m/Y H:i') . '
</div>

<table>
<thead>
<tr>
    <th>Data</th>
    <th>Descrição</th>
    <th>Tipo</th>
    <th>Categoria</th>
    <th style="text-align:right;">Valor (R$)</th>
</tr>
</thead>
<tbody>';

foreach ($rows as $r) {
   $html .= '
    <tr>
        <td>' . date('d/m/Y', strtotime($r['date'])) . '</td>
        <td>' . htmlspecialchars($r['description']) . '</td>
        <td>' . ucfirst(htmlspecialchars($r['type'])) . '</td>
        <td>' . htmlspecialchars($r['category']) . '</td>
        <td class="valor">' . number_format($r['amount'], 2, ',', '.') . '</td>
    </tr>';
}

$html .= '
</tbody>
</table>

<table class="totais">
<tr>
    <td class="label">Total Entradas</td>
    <td class="valor">R$ ' . number_format($totais['entrada'], 2, ',', '.') . '</td>
</tr>
<tr>
    <td class="label">Total Saídas</td>
    <td class="valor">R$ ' . number_format($totais['saida'], 2, ',', '.') . '</td>
</tr>
<tr>
    <td class="label">Total Investido</td>
    <td class="valor">R$ ' . number_format($totais['investimento'], 2, ',', '.') . '</td>
</tr>

<tr>
    <td class="label">Total de Saldo</td>
    <td class="valor">R$ ' . number_format($totais['entrada'] - $totais['saida'], 2, ',', '.') . '</td>
</tr>
</table>

<div class="footer">
    Meu Finanças • Página {PAGE_NUM} de {PAGE_COUNT}
</div>

</body>
</html>
';

/* Geração PDF */
$dompdf = new \Dompdf\Dompdf([
   'defaultFont' => 'DejaVu Sans'
]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('relatorio-transacoes.pdf', ['Attachment' => false]);
exit;
