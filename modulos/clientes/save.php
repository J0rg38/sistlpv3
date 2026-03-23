<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
require_permission('clientes', $id > 0 ? 'editar' : 'crear');

// Datos obligatorios y de base (Forzamos upper case siempre por backend tambien)
$tipo_cliente = mb_strtoupper(trim($_POST['tipo_cliente']));
$tipo_documento = mb_strtoupper(trim($_POST['tipo_documento']));
$numero_documento = mb_strtoupper(trim($_POST['numero_documento']));
$direccion = mb_strtoupper(trim($_POST['direccion']));
$departamento = mb_strtoupper(trim($_POST['departamento_nombre'] ?? ''));
$provincia = mb_strtoupper(trim($_POST['provincia_nombre'] ?? ''));
$distrito = mb_strtoupper(trim($_POST['distrito_nombre'] ?? ''));
$telefono = mb_strtoupper(trim($_POST['telefono']));
$email = trim($_POST['email']);

$nombres = null;
$apellidos = null;
$razon_social = null;

if ($tipo_cliente === 'NATURAL') {
    $nombres = mb_strtoupper(trim($_POST['nombres']));
    $apellidos = mb_strtoupper(trim($_POST['apellidos']));
} else {
    $razon_social = mb_strtoupper(trim($_POST['razon_social']));
}

if ($tipo_cliente === 'EMPRESA' && strlen($numero_documento) !== 11) {
    $_SESSION['error'] = "El RUC debe tener exactamente 11 dígitos.";
    header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
    exit;
}

try {
    // Verificar si el numero de documento ya existe
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE numero_documento = ? AND id != ?");
        $stmt->execute([$numero_documento, $id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE numero_documento = ?");
        $stmt->execute([$numero_documento]);
    }
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "El número de documento ya está registrado.";
        header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    if ($id > 0) {
        // Actualizar
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET tipo_cliente = ?, tipo_documento = ?, numero_documento = ?, 
                razon_social = ?, nombres = ?, apellidos = ?, direccion = ?, 
                departamento = ?, provincia = ?, distrito = ?, telefono = ?, email = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $tipo_cliente, $tipo_documento, $numero_documento, 
            $razon_social, $nombres, $apellidos, $direccion,
            $departamento, $provincia, $distrito, $telefono, $email,
            $id
        ]);
        $_SESSION['success'] = "Cliente actualizado exitosamente.";
    } else {
        // Insertar
        $stmt = $pdo->prepare("
            INSERT INTO clientes 
            (tipo_cliente, tipo_documento, numero_documento, razon_social, nombres, apellidos, 
            direccion, departamento, provincia, distrito, telefono, email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tipo_cliente, $tipo_documento, $numero_documento, 
            $razon_social, $nombres, $apellidos, $direccion,
            $departamento, $provincia, $distrito, $telefono, $email
        ]);
        $_SESSION['success'] = "Cliente creado exitosamente.";
    }
    
    header("Location: index.php");
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al guardar el cliente: " . $e->getMessage();
    header("Location: " . ($id > 0 ? "form.php?id=$id" : "form.php"));
}
?>
