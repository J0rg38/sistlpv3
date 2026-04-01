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

// Nomenclatura PLE 14.1: LE + RUC + YYYYMM00 + 140100 + 00 + 1 + 1 + 1 + 1 .txt
$periodo = $anio . $mes . "00";
$nombrePLE = "LE{$ruc_emisor}{$periodo}140100001111.txt";

header('Content-Type: application/octet-stream; charset=ISO-8859-1');
header("Content-Disposition: attachment; filename={$nombrePLE}");
header('Pragma: no-cache');
header('Expires: 0');

$salida = '';
foreach ($comprobantes as $c) {

    // Codificación Tipo de Comprobante
    $tipo_doc_sunat = '01'; // Factura
    if ($c['tipo_comprobante'] === 'BOLETA') $tipo_doc_sunat = '03';
    if ($c['tipo_comprobante'] === 'NOTA_CREDITO') $tipo_doc_sunat = '07';
    if ($c['tipo_comprobante'] === 'NOTA_DEBITO') $tipo_doc_sunat = '08';

    // Codificación Tipo Documento Receptor
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

    // Si es Anulado se envia en cero
    if ($c['estado_sunat'] === 'ANULADO') {
        $base = $igv = $total = 0.00;
        $estado = '2'; // 2 = Anulado
    } else {
        $base = (float)$c['subtotal'];
        $igv = (float)$c['igv'];
        $total = (float)$c['total'];
        $estado = '1'; // 1 = Activo
        
        // Convert NC a negativos
        if ($c['tipo_comprobante'] === 'NOTA_CREDITO') {
            $base *= -1; $igv *= -1; $total *= -1;
        }
    }

    $fecha_emision = date('d/m/Y', strtotime($c['fecha_emision']));
    $fecha_vencimiento = $c['fecha_vencimiento'] ? date('d/m/Y', strtotime($c['fecha_vencimiento'])) : '';

    // Documento Referencia (Notas)
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

    $fila = [];
    $fila[] = $periodo; // 1
    $fila[] = $c['id']; // 2 CUO
    $fila[] = 'M' . str_pad($c['id'], 3, '0', STR_PAD_LEFT); // 3 Correlativo CUO
    $fila[] = $fecha_emision; // 4
    $fila[] = $fecha_vencimiento; // 5
    $fila[] = $tipo_doc_sunat; // 6
    $fila[] = $c['serie']; // 7
    $fila[] = str_pad($c['correlativo'], 8, '0', STR_PAD_LEFT); // 8
    $fila[] = ''; // 9
    $fila[] = $tipo_cli_sunat; // 10
    $fila[] = $c['cli_doc']; // 11
    $fila[] = $nom; // 12
    $fila[] = ''; // 13 Exportacion
    $fila[] = number_format($base, 2, '.', ''); // 14 Base Imponible
    $fila[] = '0.00'; // 15
    $fila[] = number_format($igv, 2, '.', ''); // 16 IGV
    $fila[] = '0.00'; // 17
    $fila[] = '0.00'; // 18 Exonerada
    $fila[] = '0.00'; // 19 Inafecta
    $fila[] = '0.00'; // 20 ISC
    $fila[] = '0.00'; // 21 Base IVAP
    $fila[] = '0.00'; // 22 IVAP
    $fila[] = '0.00'; // 23 ICBPER
    $fila[] = '0.00'; // 24 Otros tributos
    $fila[] = number_format($total, 2, '.', ''); // 25
    $fila[] = $c['moneda']; // 26 PEN/USD
    $fila[] = ($c['moneda'] === 'USD' ? number_format($c['tipo_cambio'], 3, '.', '') : ''); // 27 Tipo Cambio
    $fila[] = $padre_fecha; // 28 Fec Emi Modificado
    $fila[] = $padre_tipo;  // 29 Tipo Modificado
    $fila[] = $padre_serie; // 30 Serie Modificado
    $fila[] = ''; // 31 DUA
    $fila[] = $padre_correlativo; // 32 Correlativo Modificado
    $fila[] = ''; // 33 Id Contrato
    $fila[] = ''; // 34 Inconsistencia
    $fila[] = ''; // 35 Medio Pago
    $fila[] = $estado; // 36 Estado

    // Convert encoding just in case SUNAT whines
    $line = implode('|', $fila) . '|'; // PLE requiere el PIPE al final
    $salida .= mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8') . "\r\n";
}

echo $salida;
