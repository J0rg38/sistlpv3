<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (empty($token) || empty($password) || empty($password_confirm)) {
    $_SESSION['error'] = 'Todos los campos son obligatorios.';
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

if ($password !== $password_confirm) {
    $_SESSION['error'] = 'Las contraseñas no coinciden.';
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

try {
    // Validar de nuevo el Token
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_recuperacion = ? AND instante_expiracion > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'El enlace de recuperación es inválido o expiró.';
        header("Location: olvide_password.php");
        exit;
    }

    // Hashear nueva contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Actualizar clave e invalidar el token
    $updateStmt = $pdo->prepare("UPDATE usuarios SET password = ?, token_recuperacion = NULL, instante_expiracion = NULL WHERE id = ?");
    $updateStmt->execute([$hash, $user['id']]);

    // Opcionalmente, autologin (aquí forzamos que vaya al login para confirmar que sabe la nueva)
    $_SESSION['success'] = '¡Tu contraseña ha sido restaurada con éxito! Ya puedes iniciar sesión de forma segura.';
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error en base de datos: ' . $e->getMessage();
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}
