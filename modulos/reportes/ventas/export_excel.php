<?php
session_start();
require_once '../../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$mes = $_GET['mes'] ?? date('m');
$anio = $_GET['anio'] ?? date('Y');

// Obtener comprobantes
// Filtramos solo aceptados y pendientes o anulados
$stmt = $pdo->prepare("
    SELECT c.*, cli.numero_documento as cli_doc, cli.tipo_documento as cli_tipo_doc, cli.razon_social as cli_rs, cli.nombres as cli_nom, cli.apellidos as cli_ape
    FROM comprobantes c
    LEFT JOIN clientes cli ON c.cliente_id = cli.id
    WHERE MONTH(c.fecha_emision) = ? AND YEAR(c.fecha_emision) = ?
    AND c.estado_sunat IN ('ACEPTADO', 'ANULADO')
    ORDER BY c.fecha_emision ASC, c.serie ASC, c.correlativo ASC
");
$stmt->execute([$mes, $anio]);
$comprobantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Archivo Excel HTML Disfrazado
$filename = "RegistroVentas_{$anio}{$mes}.xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename={$filename}");
header("Pragma: no-cache");
header("Expires: 0");

$html = '<html><head><meta charset="utf-8"></head><body>';
$html .= '<table border="1">';
$html .= '<thead>';
$html .= '<tr style="background-color:#1e3a8a; color:#ffffff;">';
$html .= '<th>FEC. EMISION</th>';
$html .= '<th>FEC. VENCIMIENTO</th>';
$html .= '<th>TIPO DOC</th>';
$html .= '<th>SERIE</th>';
$html .= '<th>NUMERO</th>';
$html .= '<th>TIPO DOC IDENTIDAD</th>';
$html .= '<th>NRO DOC IDENTIDAD</th>';
$html .= '<th>RAZON SOCIAL / NOMBRES</th>';
$html .= '<th>MONEDA</th>';
$html .= '<th>TIPO CAMBIO</th>';
$html .= '<th>BASE IMPONIBLE (V.G.)</th>';
$html .= '<th>IGV</th>';
$html .= '<th>EXONERADO</th>';
$html .= '<th>INAFECTO</th>';
$html .= '<th>ICBPER</th>';
$html .= '<th>IMPORTE TOTAL</th>';
$html .= '<th>ESTADO</th>';
$html .= '</tr>';
$html .= '</thead><tbody>';

foreach ($comprobantes as $c) {
    if ($c['estado_sunat'] === 'ANULADO') {
        $base = $igv = $total = $exo = $ina = $icbper = 0.00;
    } else {
        $base = (float)$c['subtotal'];
        $igv = (float)$c['igv'];
        $total = (float)$c['total'];
        $exo = 0; // Por ahora SistLPv3 soporta IGV gravado principal, ampliar lógicas Exoneradas si es necesario
        $ina = 0;
        $icbper = 0;
        
        // Convert NC/ND a negativos/positivos
        if ($c['tipo_comprobante'] === 'NOTA_CREDITO') {
            $base *= -1; $igv *= -1; $total *= -1;
        }
    }

    $c_tipo = '01'; // Factura
    if ($c['tipo_comprobante'] === 'BOLETA') $c_tipo = '03';
    if ($c['tipo_comprobante'] === 'NOTA_CREDITO') $c_tipo = '07';
    if ($c['tipo_comprobante'] === 'NOTA_DEBITO') $c_tipo = '08';

    $cli_tipo = '1'; // DNI
    if ($c['cli_tipo_doc'] === 'RUC') $cli_tipo = '6';
    if ($c['cli_tipo_doc'] === 'CE') $cli_tipo = '4';
    if ($c['cli_tipo_doc'] === 'PASAPORTE') $cli_tipo = '7';

    $nom = $c['cli_tipo_doc'] === 'RUC' ? $c['cli_rs'] : trim($c['cli_nom'] . ' ' . $c['cli_ape']);

    $html .= '<tr>';
    $html .= '<td>' . $c['fecha_emision'] . '</td>';
    $html .= '<td>' . ($c['fecha_vencimiento'] ?: '-') . '</td>';
    $html .= '<td>' . $c_tipo . '</td>';
    $html .= '<td>' . $c['serie'] . '</td>';
    $html .= '<td>' . $c['correlativo'] . '</td>';
    $html .= '<td>' . $cli_tipo . '</td>';
    $html .= '<td>' . $c['cli_doc'] . '</td>';
    $html .= '<td>' . htmlspecialchars($nom) . '</td>';
    $html .= '<td>' . $c['moneda'] . '</td>';
    $html .= '<td>' . ($c['moneda'] === 'USD' ? $c['tipo_cambio'] : '') . '</td>';
    $html .= '<td>' . number_format($base, 2, '.', '') . '</td>';
    $html .= '<td>' . number_format($igv, 2, '.', '') . '</td>';
    $html .= '<td>' . number_format($exo, 2, '.', '') . '</td>';
    $html .= '<td>' . number_format($ina, 2, '.', '') . '</td>';
    $html .= '<td>' . number_format($icbper, 2, '.', '') . '</td>';
    $html .= '<td>' . number_format($total, 2, '.', '') . '</td>';
    $html .= '<td>' . $c['estado_sunat'] . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table></body></html>';
echo $html;
