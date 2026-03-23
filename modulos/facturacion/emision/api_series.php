<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';

$tipo = $_GET['tipo'] ?? '';
if (!$tipo) {
    echo json_encode(['error' => 'Tipo de comprobante requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, serie, correlativo_actual FROM series_facturacion WHERE tipo_comprobante = ? AND estado = 1 ORDER BY serie ASC");
    $stmt->execute([$tipo]);
    $series = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($series);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
