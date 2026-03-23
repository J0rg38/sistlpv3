<?php
// includes/auth_helpers.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/**
 * Check if the currently logged-in user has a specific permission.
 * 
 * @param string $modulo The module name (e.g. 'usuarios')
 * @param string $accion The action name (e.g. 'crear')
 * @return bool True if permitted, false otherwise.
 */
function has_permission($modulo, $accion) {
    if (!isset($_SESSION['permisos'])) {
        return false;
    }
    
    foreach ($_SESSION['permisos'] as $p) {
        if ($p['modulo'] === $modulo && $p['accion'] === $accion) {
            return true;
        }
    }
    
    return false;
}

/**
 * Enforce permission. If not permitted, redirect.
 */
function require_permission($modulo, $accion, $redirect_to = '../../dashboard.php') {
    if (!has_permission($modulo, $accion)) {
        $_SESSION['error'] = "No tienes permisos para realizar esta acción ($modulo / $accion).";
        header("Location: " . $redirect_to);
        exit;
    }
}
?>
