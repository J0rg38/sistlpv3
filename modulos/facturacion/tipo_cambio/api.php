<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $search = $_GET['search'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    if ($page < 1) $page = 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $whereClause = "";
    $params = [];
    if (!empty($search)) {
        $whereClause = "WHERE fecha LIKE ?";
        $params[] = "%$search%";
    }
    
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tipo_cambio $whereClause");
    $stmtCount->execute($params);
    $total = $stmtCount->fetchColumn();
    $total_pages = ceil($total / $limit);
    if ($total_pages == 0) $total_pages = 1;
    
    $stmt = $pdo->prepare("SELECT * FROM tipo_cambio $whereClause ORDER BY fecha DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    
    echo json_encode([
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
    exit;
}

if ($action === 'save') {
    require_permission('facturacion_tipo_cambio', 'crear');
    $data = json_decode(file_get_contents('php://input'), true);
    
    $fecha = $data['fecha'] ?? '';
    $compra = (float)($data['compra'] ?? 0);
    $venta = (float)($data['venta'] ?? 0);
    
    if (!$fecha || $compra <= 0 || $venta <= 0) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos o inválidos']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tipo_cambio (fecha, compra, venta) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE compra = ?, venta = ?");
        $stmt->execute([$fecha, $compra, $venta, $compra, $venta]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error BD: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete') {
    require_permission('facturacion_tipo_cambio', 'eliminar');
    $fecha = $_GET['fecha'] ?? '';
    if (!$fecha) {
        echo json_encode(['success' => false, 'error' => 'Fecha inválida']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tipo_cambio WHERE fecha = ?");
        $stmt->execute([$fecha]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error BD: ' . $e->getMessage()]);
    }
    exit;
}
?>
