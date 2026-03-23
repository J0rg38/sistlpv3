<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Por favor, ingrese sus credenciales.';
        header("Location: index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            
            // Verificamos si esta activo
            if ($user['activo'] != 1) {
                $_SESSION['error'] = 'Tu cuenta está desactivada. Contacta al administrador.';
                header("Location: index.php");
                exit;
            }

            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_apellidos'] = $user['apellidos'];
            $_SESSION['rol_id'] = $user['rol_id'];
            $_SESSION['user_rol'] = $user['rol_nombre'] ?? 'Sin Rol';

            // Cargar permisos en sesión
            $permStmt = $pdo->prepare("
                SELECT p.modulo, p.accion 
                FROM rol_permisos rp 
                JOIN permisos p ON rp.permiso_id = p.id 
                WHERE rp.rol_id = ?
            ");
            $permStmt->execute([$user['rol_id']]);
            $_SESSION['permisos'] = $permStmt->fetchAll();
            
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = 'Correo o contraseña incorrectos.';
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error de sistema. Contacte a soporte.';
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
