<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';
require_permission('clientes', 'eliminar');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Cliente eliminado exitosamente.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "No se puede eliminar el cliente porque tiene registros asociados (ej. facturas).";
        } else {
            $_SESSION['error'] = "Error al eliminar el cliente: " . $e->getMessage();
        }
    }
}

header("Location: index.php");
exit;
?>
