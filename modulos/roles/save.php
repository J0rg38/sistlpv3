<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Check permission
    require_permission('roles', $id > 0 ? 'editar' : 'crear');

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $permisos_seleccionados = $_POST['permisos'] ?? [];

    if (empty($nombre)) {
        $_SESSION['error'] = "El nombre del rol es obligatorio.";
        header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($id > 0) {
            // Update Role
            $stmt = $pdo->prepare("UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $id]);
            $rol_id = $id;

            // Delete old permissions
            $pdo->prepare("DELETE FROM rol_permisos WHERE rol_id = ?")->execute([$rol_id]);
            $_SESSION['success'] = "Rol y permisos actualizados correctamente.";
        } else {
            // Create Role
            $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            $rol_id = $pdo->lastInsertId();
            $_SESSION['success'] = "Nuevo rol creado exitosamente.";
        }

        // Insert new permissions
        if (!empty($permisos_seleccionados)) {
            $insertPerm = $pdo->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
            foreach ($permisos_seleccionados as $perm_id) {
                $insertPerm->execute([$rol_id, (int)$perm_id]);
            }
        }

        $pdo->commit();
        
        // Refresh session permissions if user edited their own role
        if ($_SESSION['rol_id'] == $rol_id) {
            $permStmt = $pdo->prepare("
                SELECT p.modulo, p.accion 
                FROM rol_permisos rp 
                JOIN permisos p ON rp.permiso_id = p.id 
                WHERE rp.rol_id = ?
            ");
            $permStmt->execute([$rol_id]);
            $_SESSION['permisos'] = $permStmt->fetchAll();
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }
    
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
