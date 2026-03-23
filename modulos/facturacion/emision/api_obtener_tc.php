<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';

$fecha = $_GET['fecha'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT * FROM tipo_cambio WHERE fecha = ?");
    $stmt->execute([$fecha]);
    $tc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tc) {
        echo json_encode(['success' => true, 'tc' => $tc]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No hay Tipo de Cambio registrado para la fecha solicitada.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
