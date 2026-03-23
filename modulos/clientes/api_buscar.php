<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$documento = $_GET['doc'] ?? '';
if (!$documento) {
    echo json_encode(['error' => 'Documento requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE numero_documento = ? LIMIT 1");
    $stmt->execute([$documento]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        $partes = array_filter([
            trim($cliente['direccion'] ?? ''),
            trim($cliente['departamento'] ?? ''),
            trim($cliente['provincia'] ?? ''),
            trim($cliente['distrito'] ?? '')
        ]);
        $cliente['direccion_completa'] = empty($partes) ? '' : mb_strtoupper(implode(' - ', $partes));

        // Return successful data
        echo json_encode($cliente);
    } else {
        echo json_encode(['error' => 'Cliente no encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
