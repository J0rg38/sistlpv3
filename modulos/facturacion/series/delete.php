<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

// Check permission
require_permission('facturacion_series', 'eliminar');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM series_facturacion WHERE id = ?");
        $check->execute([$id]);
        
        if ($check->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM series_facturacion WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Serie eliminada correctamente.";
            } else {
                $_SESSION['error'] = "Error al eliminar la serie.";
            }
        } else {
            $_SESSION['error'] = "La serie especificada no existe.";
        }
    } catch (PDOException $e) {
        // Could be a foreign key constraint violation in the future
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "No se puede eliminar la serie porque está en uso en otros registros (ej. Comprobantes emitidos).";
        } else {
            $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "ID de serie no proporcionado.";
}

header("Location: index.php");
exit;
?>
