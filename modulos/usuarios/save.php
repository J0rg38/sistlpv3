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
    require_permission('usuarios', $id > 0 ? 'editar' : 'crear');

    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $tipo_documento = $_POST['tipo_documento'] ?? 'DNI';
    $numero_documento = trim($_POST['numero_documento'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol_id = (int)($_POST['rol_id'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($apellidos) || empty($email) || empty($rol_id)) {
        $_SESSION['error'] = "Todos los campos obligatorios deben completarse.";
        header("Location: form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    try {
        if ($id > 0) {
            // Check if email belongs to someone else
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $check->execute([$email, $id]);
            if ($check->rowCount() > 0) {
                $_SESSION['error'] = "El correo electrónico ya está registrado en otro usuario.";
                header("Location: form.php?id=$id");
                exit;
            }

            // Update
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, tipo_documento = ?, numero_documento = ?, email = ?, rol_id = ?, activo = ?, password = ? WHERE id = ?");
                $stmt->execute([$nombre, $apellidos, $tipo_documento, $numero_documento, $email, $rol_id, $activo, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, tipo_documento = ?, numero_documento = ?, email = ?, rol_id = ?, activo = ? WHERE id = ?");
                $stmt->execute([$nombre, $apellidos, $tipo_documento, $numero_documento, $email, $rol_id, $activo, $id]);
            }
            $_SESSION['success'] = "Usuario actualizado correctamente.";
        } else {
            // Create
            if (empty($password)) {
                $_SESSION['error'] = "La contraseña es obligatoria para nuevos usuarios.";
                header("Location: form.php");
                exit;
            }
            // Check email uniqueness
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() > 0) {
                $_SESSION['error'] = "El correo electrónico ya está registrado.";
                header("Location: form.php");
                exit;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellidos, tipo_documento, numero_documento, email, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellidos, $tipo_documento, $numero_documento, $email, $hash, $rol_id, $activo]);
            $_SESSION['success'] = "Usuario creado correctamente.";
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
