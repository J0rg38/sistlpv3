<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'No se proporcionaron datos']);
    exit;
}

$tipo_cliente = mb_strtoupper(trim($data['tipo_cliente'] ?? ''));
$tipo_documento = mb_strtoupper(trim($data['tipo_documento'] ?? ''));
$numero_documento = mb_strtoupper(trim($data['numero_documento'] ?? ''));
$direccion = mb_strtoupper(trim($data['direccion'] ?? ''));
$telefono = mb_strtoupper(trim($data['telefono'] ?? ''));
$email = trim($data['email'] ?? '');

$nombres = null;
$apellidos = null;
$razon_social = null;

if ($tipo_cliente === 'NATURAL') {
    $nombres = mb_strtoupper(trim($data['nombres'] ?? ''));
    $apellidos = mb_strtoupper(trim($data['apellidos'] ?? ''));
} else {
    $razon_social = mb_strtoupper(trim($data['razon_social'] ?? ''));
}

if ($tipo_cliente === 'EMPRESA' && strlen($numero_documento) !== 11) {
    echo json_encode(['error' => 'El RUC debe tener exactamente 11 dígitos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE numero_documento = ?");
    $stmt->execute([$numero_documento]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'El número de documento ya está registrado.']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO clientes 
        (tipo_cliente, tipo_documento, numero_documento, razon_social, nombres, apellidos, direccion, telefono, email) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $tipo_cliente, $tipo_documento, $numero_documento, 
        $razon_social, $nombres, $apellidos, $direccion, $telefono, $email
    ]);
    
    $id = $pdo->lastInsertId();
    
    // Return the newly created client
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $partes = array_filter([
        trim($cliente['direccion'] ?? ''),
        trim($cliente['departamento'] ?? ''),
        trim($cliente['provincia'] ?? ''),
        trim($cliente['distrito'] ?? '')
    ]);
    $cliente['direccion_completa'] = empty($partes) ? '' : mb_strtoupper(implode(' - ', $partes));
    
    echo json_encode(['success' => true, 'cliente' => $cliente]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al guardar el cliente: ' . $e->getMessage()]);
}
?>
