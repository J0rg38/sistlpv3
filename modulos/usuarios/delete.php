<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_permission('usuarios', 'eliminar');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = "No puedes eliminar tu propio usuario activo.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Usuario eliminado correctamente.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "ID de usuario inválido.";
}

header("Location: index.php");
exit;
?>
