<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';

$serie = $_GET['serie'] ?? '';
$correlativo = $_GET['correlativo'] ?? '';

if (!$serie || !$correlativo) {
    echo json_encode(['error' => 'Serie y correlativo requeridos']);
    exit;
}

try {
    // Buscar la cabecera
    $stmt = $pdo->prepare("
        SELECT c.*, 
               c.cliente_numero_documento as numero_documento, 
               c.cliente_razon_social as nombre_cliente,
               c.cliente_direccion_completa as direccion_cliente
        FROM comprobantes c
        WHERE c.serie = ? AND c.correlativo = ? AND c.estado = 'EMITIDO'
    ");
    $stmt->execute([$serie, $correlativo]);
    $comprobante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comprobante) {
        echo json_encode(['error' => 'Comprobante no encontrado o inactivo']);
        exit;
    }

    // Solo se permite NC o ND sobre BOLETAS o FACTURAS
    if (!in_array($comprobante['tipo_comprobante'], ['BOLETA', 'FACTURA'])) {
        echo json_encode(['error' => 'Solo se puede emitir NC/ND para Facturas o Boletas. Este es un ' . $comprobante['tipo_comprobante']]);
        exit;
    }

    // Buscar los ítems
    $stmtItems = $pdo->prepare("
        SELECT * FROM comprobantes_items 
        WHERE comprobante_id = ?
    ");
    $stmtItems->execute([$comprobante['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'comprobante' => $comprobante,
        'items' => $items
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
