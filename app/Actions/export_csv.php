<?php
// Silencia deprecated apenas aqui (CSV não pode ter warnings)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

require_once __DIR__ . '/../../config.php';
requireLogin();

$uid = currentUserId();

$start    = $_GET['start'] ?? null;
$end      = $_GET['end'] ?? null;
$category = $_GET['category'] ?? null;
$type     = $_GET['type'] ?? null;

$where  = ['user_id = :uid'];
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
   'entrada'      => 0,
   'saida'        => 0,
   'extra'        => 0,
   'investimento' => 0
];

foreach ($rows as $r) {
   if (isset($totais[$r['type']])) {
      $totais[$r['type']] += $r['amount'];
   }
}

/* Headers CSV */
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=relatorio-transacoes.csv');

/* BOM UTF-8 (Excel / Outlook) */
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

/* Parâmetros explícitos do fputcsv */
$delimiter = ';';
$enclosure = '"';
$escape    = '\\';

/* Cabeçalho */
fputcsv($out, ['Data', 'Descrição', 'Tipo', 'Categoria', 'Valor (R$)'], $delimiter, $enclosure, $escape);

/* Dados */
foreach ($rows as $r) {
   fputcsv(
      $out,
      [
         date('d/m/Y', strtotime($r['date'])),
         $r['description'],
         ucfirst($r['type']),
         $r['category'],
         number_format($r['amount'], 2, ',', '.')
      ],
      $delimiter,
      $enclosure,
      $escape
   );
}

/* Linha em branco */
fputcsv($out, [], $delimiter, $enclosure, $escape);

/* Totais */
fputcsv($out, ['TOTAL ENTRADAS', '', '', '', number_format($totais['entrada'], 2, ',', '.')], $delimiter, $enclosure, $escape);
fputcsv($out, ['TOTAL SAÍDAS', '', '', '', number_format($totais['saida'], 2, ',', '.')], $delimiter, $enclosure, $escape);
fputcsv($out, ['TOTAL INVESTIDO', '', '', '', number_format($totais['investimento'], 2, ',', '.')], $delimiter, $enclosure, $escape);

$saldo = $totais['entrada'] - $totais['saida'];
fputcsv($out, ['SALDO GERAL', '', '', '', number_format($saldo, 2, ',', '.')], $delimiter, $enclosure, $escape);

fclose($out);
exit;
