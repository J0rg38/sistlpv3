<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Check permission
    require_permission('facturacion_series', $id > 0 ? 'editar' : 'crear');

    $tipo_comprobante = trim($_POST['tipo_comprobante'] ?? '');
    $serie = trim(strtoupper($_POST['serie'] ?? ''));
    $descripcion = trim($_POST['descripcion'] ?? '');
    $correlativo_actual = isset($_POST['correlativo_actual']) ? (int)$_POST['correlativo_actual'] : 1;
    $estado = isset($_POST['estado']) ? 1 : 0;

    // Validation
    if (empty($tipo_comprobante) || empty($serie)) {
        $_SESSION['error'] = "El tipo de comprobante y la serie son obligatorios.";
        header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    if (strlen($serie) > 4) {
        $_SESSION['error'] = "El prefijo de serie no puede exceder 4 caracteres.";
        header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    try {
        // Check for duplicates
        $checkStmt = $pdo->prepare("SELECT id FROM series_facturacion WHERE tipo_comprobante = ? AND serie = ? AND id != ?");
        $checkStmt->execute([$tipo_comprobante, $serie, $id]);
        if ($checkStmt->fetch()) {
            $_SESSION['error'] = "Ya existe una serie registrada para este tipo de comprobante con el mismo prefijo.";
            header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
            exit;
        }

        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE series_facturacion SET tipo_comprobante = ?, serie = ?, descripcion = ?, correlativo_actual = ?, estado = ? WHERE id = ?");
            if ($stmt->execute([$tipo_comprobante, $serie, $descripcion, $correlativo_actual, $estado, $id])) {
                $_SESSION['success'] = "Serie actualizada correctamente.";
            } else {
                $_SESSION['error'] = "No se pudo actualizar la serie.";
            }
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO series_facturacion (tipo_comprobante, serie, descripcion, correlativo_actual, estado) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$tipo_comprobante, $serie, $descripcion, $correlativo_actual, $estado])) {
                $_SESSION['success'] = "Serie registrada exitosamente.";
            } else {
                $_SESSION['error'] = "Error al registrar la serie.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }
    
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
