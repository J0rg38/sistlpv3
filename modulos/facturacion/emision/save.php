<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

// Auth checks
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_permission('facturacion_emision', 'crear');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$cabecera = $data['cabecera'] ?? [];
$totales = $data['totales'] ?? [];
$cliente_id = (int)($data['cliente_id'] ?? 0);
$items = $data['items'] ?? [];

if ($cliente_id <= 0 || empty($items) || empty($cabecera['serie_id'])) {
    echo json_encode(['error' => 'Datos incompletos requeridos para facturar']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Lock and Get the series correlativo to avoid race conditions
    $stmtSerie = $pdo->prepare("SELECT serie, correlativo_actual, tipo_comprobante FROM series_facturacion WHERE id = ? AND estado = 1 FOR UPDATE");
    $stmtSerie->execute([$cabecera['serie_id']]);
    $serieRow = $stmtSerie->fetch();

    if (!$serieRow) {
        throw new Exception("Serie no encontrada o inactiva.");
    }
    
    // Ensure the payload matches the DB to prevent frontend falsification
    if ($serieRow['tipo_comprobante'] !== $cabecera['tipo']) {
        throw new Exception("Inconsistencia en tipo de comprobante.");
    }

    $correlativoFinal = $serieRow['correlativo_actual'];

    // Recuperar info estática de cliente
    $stmtCli = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmtCli->execute([$cliente_id]);
    $cliSnap = $stmtCli->fetch(PDO::FETCH_ASSOC);

    $razon_social = $cliSnap['tipo_cliente'] === 'EMPRESA' 
        ? trim($cliSnap['razon_social']) 
        : trim($cliSnap['nombres'] . ' ' . $cliSnap['apellidos']);

    $partesDir = array_filter([
        trim($cliSnap['direccion'] ?? ''),
        trim($cliSnap['departamento'] ?? ''),
        trim($cliSnap['provincia'] ?? ''),
        trim($cliSnap['distrito'] ?? '')
    ]);
    $direccion_completa = empty($partesDir) ? '' : mb_strtoupper(implode(' - ', $partesDir));

    $codigo_doc = '00';
    if ($cabecera['tipo'] === 'FACTURA') $codigo_doc = '01';
    elseif ($cabecera['tipo'] === 'BOLETA') $codigo_doc = '03';
    elseif ($cabecera['tipo'] === 'NOTA_CREDITO') $codigo_doc = '07';
    elseif ($cabecera['tipo'] === 'NOTA_DEBITO') $codigo_doc = '08';

    // Anti-fraud: Final validation limit for Nota Credito
    if ($cabecera['tipo'] === 'NOTA_CREDITO' && !empty($cabecera['comprobante_relacionado_id'])) {
        $stmtS = $pdo->prepare("SELECT total FROM comprobantes WHERE id = ?");
        $stmtS->execute([$cabecera['comprobante_relacionado_id']]);
        $parentTotal = (float)$stmtS->fetchColumn();

        $stmtNc = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM comprobantes WHERE comprobante_relacionado_id = ? AND tipo_comprobante = 'NOTA_CREDITO' AND estado_sunat IN ('ACEPTADO', 'PENDIENTE')");
        $stmtNc->execute([$cabecera['comprobante_relacionado_id']]);
        $totNc = (float)$stmtNc->fetchColumn();

        $stmtNd = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM comprobantes WHERE comprobante_relacionado_id = ? AND tipo_comprobante = 'NOTA_DEBITO' AND estado_sunat IN ('ACEPTADO', 'PENDIENTE')");
        $stmtNd->execute([$cabecera['comprobante_relacionado_id']]);
        $totNd = (float)$stmtNd->fetchColumn();

        $saldoReal = $parentTotal - $totNc + $totNd;

        if ((float)$totales['total'] > $saldoReal) {
            throw new Exception("El monto de la Nota de Crédito excéde matemáticamente el saldo restante del comprobante (Queda: S/ " . number_format($saldoReal, 2) . ").");
        }
    }

    // 2. Insert into comprobantes
    $stmtComp = $pdo->prepare("
        INSERT INTO comprobantes (
            tipo_comprobante, codigo_tipo_documento, serie, correlativo, 
            cliente_id, cliente_numero_documento, cliente_razon_social, cliente_direccion_completa,
            comprobante_relacionado_id, 
            codigo_motivo, descripcion_motivo,
            fecha_emision, fecha_vencimiento, observaciones,
            moneda, tipo_cambio, condicion_pago, dias_credito, subtotal, igv, total,
            tiene_detraccion, codigo_detraccion, porcentaje_detraccion, monto_detraccion,
            tiene_retencion, porcentaje_retencion, monto_retencion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtComp->execute([
        $cabecera['tipo'],
        $codigo_doc,
        $serieRow['serie'],
        $correlativoFinal,
        $cliente_id,
        $cliSnap['numero_documento'],
        $razon_social,
        $direccion_completa,
        $cabecera['comprobante_relacionado_id'] ?? null,
        $cabecera['codigo_motivo'] ?? null,
        $cabecera['descripcion_motivo'] ?? null,
        $cabecera['fecha_emision'],
        empty($cabecera['fecha_vencimiento']) ? null : $cabecera['fecha_vencimiento'],
        $cabecera['observaciones'] ?? null,
        $cabecera['moneda'],
        $cabecera['moneda'] === 'USD' ? ($cabecera['tipo_cambio'] ?: null) : null,
        in_array($cabecera['tipo'], ['NOTA_CREDITO', 'NOTA_DEBITO']) ? 'CONTADO' : $cabecera['condicion_pago'],
        ($cabecera['condicion_pago'] === 'CREDITO' && !in_array($cabecera['tipo'], ['NOTA_CREDITO', 'NOTA_DEBITO'])) ? $cabecera['dias_credito'] : 0,
        $totales['subtotal'],
        $totales['igv'],
        $totales['total'],
        !empty($cabecera['tiene_detraccion']) ? 1 : 0,
        $cabecera['codigo_detraccion'] ?? null,
        $cabecera['porcentaje_detraccion'] ?? null,
        $cabecera['monto_detraccion'] ?? 0,
        !empty($cabecera['tiene_retencion']) ? 1 : 0,
        $cabecera['porcentaje_retencion'] ?? null,
        $cabecera['monto_retencion'] ?? 0
    ]);

    $comprobante_id = $pdo->lastInsertId();

    // 3. Insert into comprobantes_items
    $stmtItem = $pdo->prepare("
        INSERT INTO comprobantes_items (
            comprobante_id, codigo, descripcion, unidad_medida, cantidad, precio_unitario, descuento, importe_total, igv
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $igv_item = ($item['total'] / 1.18) * 0.18;
        $stmtItem->execute([
            $comprobante_id,
            $item['codigo'] ?? null,
            $item['descripcion'],
            $item['um'] ?? 'NIU',
            $item['cantidad'],
            $item['precio'],
            $item['descuento'] ?? 0,
            $item['total'],
            $igv_item
        ]);
    }

    // 4. Update the serie correlativo
    $stmtUpdateSerie = $pdo->prepare("UPDATE series_facturacion SET correlativo_actual = correlativo_actual + 1 WHERE id = ?");
    $stmtUpdateSerie->execute([$cabecera['serie_id']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'comprobante_id' => $comprobante_id]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Duplicate entry handling if race condition somehow beat FOR UPDATE (rare)
    if (isset($e->errorInfo) && $e->errorInfo[1] == 1062) {
        echo json_encode(['error' => 'Error de concurrencia: El correlativo ya fue emitido. Por favor intente nuevamente.']);
    } else {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
