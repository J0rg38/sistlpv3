<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_permission('roles', 'eliminar');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    if ($id == $_SESSION['rol_id']) {
        $_SESSION['error'] = "No puedes eliminar tu propio rol de administrador.";
    } else {
        try {
            // Check if it has users
            $stmt = $pdo->prepare("SELECT COUNT(id) FROM usuarios WHERE rol_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $_SESSION['error'] = "No puedes eliminar este rol porque tiene usuarios asignados.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success'] = "Rol eliminado correctamente.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar rol: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "ID de rol inválido.";
}

header("Location: index.php");
exit;
?>
