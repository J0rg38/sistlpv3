<?php
session_start();
require_once '../../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$mes = $_GET['mes'] ?? date('m');
$anio = $_GET['anio'] ?? date('Y');

// Obtener RUC de la empresa emisora
$stmtEmpresa = $pdo->prepare("SELECT ruc FROM empresa_config LIMIT 1");
$stmtEmpresa->execute();
$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);
$ruc_emisor = $empresa['ruc'] ?? '00000000000';

$stmt = $pdo->prepare("
    SELECT c.*, cli.numero_documento as cli_doc, cli.tipo_documento as cli_tipo_doc, cli.razon_social as cli_rs, cli.nombres as cli_nom, cli.apellidos as cli_ape, 
           padre.fecha_emision as padre_fecha, padre.tipo_comprobante as padre_tipo, padre.serie as padre_serie, padre.correlativo as padre_correlativo
    FROM comprobantes c
    LEFT JOIN clientes cli ON c.cliente_id = cli.id
    LEFT JOIN comprobantes padre ON c.comprobante_relacionado_id = padre.id
    WHERE MONTH(c.fecha_emision) = ? AND YEAR(c.fecha_emision) = ?
    AND c.estado_sunat IN ('ACEPTADO', 'ANULADO')
    ORDER BY c.fecha_emision ASC, c.serie ASC, c.correlativo ASC
");
$stmt->execute([$mes, $anio]);
$comprobantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nomenclatura SIRE RVIE
$periodo = $anio . $mes . "00";
// Nombre base homologado para SIRE
$nombreSIRE = "LE{$ruc_emisor}{$periodo}140400001111.txt"; 
// Diferencia general: SIRE suele usar 140400 o un macro archivo TXT zipeado (RVIE)
// Aquí proveemos el formato estructurado plano adaptado, compatible con propuestas SIRE

header('Content-Type: application/octet-stream; charset=ISO-8859-1');
header("Content-Disposition: attachment; filename={$nombreSIRE}");
header('Pragma: no-cache');
header('Expires: 0');

$salida = '';
foreach ($comprobantes as $c) {

    $tipo_doc_sunat = '01'; // Factura
    if ($c['tipo_comprobante'] === 'BOLETA') $tipo_doc_sunat = '03';
    if ($c['tipo_comprobante'] === 'NOTA_CREDITO') $tipo_doc_sunat = '07';
    if ($c['tipo_comprobante'] === 'NOTA_DEBITO') $tipo_doc_sunat = '08';

    $tipo_cli_sunat = '1'; // DNI
    if ($c['cli_tipo_doc'] === 'RUC') $tipo_cli_sunat = '6';
    if ($c['cli_tipo_doc'] === 'CE') $tipo_cli_sunat = '4';
    if ($c['cli_tipo_doc'] === 'PASAPORTE') $tipo_cli_sunat = '7';
    
    if ($c['estado_sunat'] === 'ANULADO') {
        $tipo_cli_sunat = '0';
        $c['cli_doc'] = '0';
        $nom = 'ANULADO';
    } else {
        $nom = $c['cli_tipo_doc'] === 'RUC' ? $c['cli_rs'] : trim($c['cli_nom'] . ' ' . $c['cli_ape']);
    }

    if ($c['estado_sunat'] === 'ANULADO') {
        $base = $igv = $total = 0.00;
        $estado = '2'; // 2 = Anulado
    } else {
        $base = (float)$c['subtotal'];
        $igv = (float)$c['igv'];
        $total = (float)$c['total'];
        $estado = '1'; 
        
        if ($c['tipo_comprobante'] === 'NOTA_CREDITO') {
            $base *= -1; $igv *= -1; $total *= -1;
        }
    }

    $fecha_emision = date('d/m/Y', strtotime($c['fecha_emision']));
    $fecha_vencimiento = $c['fecha_vencimiento'] ? date('d/m/Y', strtotime($c['fecha_vencimiento'])) : '';

    $padre_fecha = '';
    $padre_tipo = '';
    $padre_serie = '';
    $padre_correlativo = '';
    if (in_array($c['tipo_comprobante'], ['NOTA_CREDITO', 'NOTA_DEBITO']) && !empty($c['padre_serie'])) {
        $padre_fecha = date('d/m/Y', strtotime($c['padre_fecha']));
        $pt = '01';
        if ($c['padre_tipo'] === 'BOLETA') $pt = '03';
        $padre_tipo = $pt;
        $padre_serie = $c['padre_serie'];
        $padre_correlativo = str_pad($c['padre_correlativo'], 8, '0', STR_PAD_LEFT);
    }

    // El SIRE suele requerir en su Anexo 2 los siguientes campos (simplificado):
    $fila = [];
    $fila[] = $ruc_emisor; // 1 RUC
    $fila[] = $c['cli_rs'] ? $c['cli_rs'] : 'SistLPv3'; // 2 Razon Social Emisor
    $fila[] = $periodo; // 3 Periodo
    $fila[] = str_pad($c['id'], 6, '0', STR_PAD_LEFT); // 4 CAR / CUO SIRE
    $fila[] = $fecha_emision; // 5
    $fila[] = $fecha_vencimiento; // 6
    $fila[] = $tipo_doc_sunat; // 7
    $fila[] = $c['serie']; // 8
    $fila[] = str_pad($c['correlativo'], 8, '0', STR_PAD_LEFT); // 9
    $fila[] = ''; // 10 Nro Final
    $fila[] = $tipo_cli_sunat; // 11
    $fila[] = $c['cli_doc']; // 12
    $fila[] = $nom; // 13
    $fila[] = ''; // 14 Valor Facturado Exportacion
    $fila[] = number_format($base, 2, '.', ''); // 15 Base Imponible
    $fila[] = '0.00'; // 16 Dscto BI
    $fila[] = number_format($igv, 2, '.', ''); // 17 IGV
    $fila[] = '0.00'; // 18 Dscto IGV
    $fila[] = '0.00'; // 19 Exonerado
    $fila[] = '0.00'; // 20 Inafecto
    $fila[] = '0.00'; // 21 ISC
    $fila[] = '0.00'; // 22 Base IVAP
    $fila[] = '0.00'; // 23 IVAP
    $fila[] = '0.00'; // 24 ICBPER
    $fila[] = '0.00'; // 25 Otros Tributos
    $fila[] = number_format($total, 2, '.', ''); // 26 Importe Total
    $fila[] = $c['moneda']; // 27
    $fila[] = ($c['moneda'] === 'USD' ? number_format($c['tipo_cambio'], 3, '.', '') : ''); // 28
    $fila[] = $padre_fecha; // 29
    $fila[] = $padre_tipo; // 30
    $fila[] = $padre_serie; // 31
    $fila[] = ''; // 32 Cod Aduana
    $fila[] = $padre_correlativo; // 33 Nro Doc Modificado
    $fila[] = ''; // 34
    $fila[] = ''; // 35
    $fila[] = $estado; // 36

    $line = implode('|', $fila) . '|';
    $salida .= mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8') . "\r\n";
}

echo $salida;
