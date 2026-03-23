<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    // 1. Cabecera
    $stmt = $pdo->prepare("SELECT * FROM comprobantes WHERE id = ?");
    $stmt->execute([$id]);
    $comp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comp) {
        echo json_encode(['error' => 'Comprobante no existe']);
        exit;
    }

    // 2. Items
    $stmtIt = $pdo->prepare("SELECT * FROM comprobantes_items WHERE comprobante_id = ?");
    $stmtIt->execute([$id]);
    $items = $stmtIt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Relaciones y Saldo
    $saldo = (float)$comp['total'];
    $notasAsociadas = [];
    $padre = null;

    if (in_array($comp['tipo_comprobante'], ['FACTURA', 'BOLETA'])) {
        $stmtRel = $pdo->prepare("
            SELECT id, tipo_comprobante, serie, correlativo, total, fecha_emision, estado_sunat, moneda
            FROM comprobantes 
            WHERE comprobante_relacionado_id = ? 
            ORDER BY fecha_emision DESC
        ");
        $stmtRel->execute([$comp['id']]);
        $notasAsociadas = $stmtRel->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notasAsociadas as $n) {
            if (in_array($n['estado_sunat'], ['ACEPTADO', 'PENDIENTE'])) {
                if ($n['tipo_comprobante'] === 'NOTA_CREDITO') {
                    $saldo -= (float)$n['total'];
                } elseif ($n['tipo_comprobante'] === 'NOTA_DEBITO') {
                    $saldo += (float)$n['total'];
                }
            }
        }
    } else {
        if ($comp['comprobante_relacionado_id']) {
            $stmtPadre = $pdo->prepare("
                SELECT id, tipo_comprobante, serie, correlativo, total, fecha_emision, moneda 
                FROM comprobantes WHERE id = ?
            ");
            $stmtPadre->execute([$comp['comprobante_relacionado_id']]);
            $padre = $stmtPadre->fetch(PDO::FETCH_ASSOC);
        }
    }

    $saldo = max(0, $saldo); // No mostramos matemáticamente negativos visuales si hay notas erradas

    echo json_encode([
        'success' => true,
        'cabecera' => $comp,
        'items' => $items,
        'saldo' => $saldo,
        'notas' => $notasAsociadas,
        'padre' => $padre
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de BD: ' . $e->getMessage()]);
}
?>
