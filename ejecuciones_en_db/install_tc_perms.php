<?php
require 'config/db.php';
$permisos = [
    ['facturacion_tipo_cambio', 'ver', 'Ver Tipos de Cambio'],
    ['facturacion_tipo_cambio', 'crear', 'Crear Tipos de Cambio'],
    ['facturacion_tipo_cambio', 'editar', 'Editar Tipos de Cambio'],
    ['facturacion_tipo_cambio', 'eliminar', 'Borrar Tipos de Cambio']
];

foreach ($permisos as $p) {
    $stmt = $pdo->prepare("SELECT id FROM permisos WHERE modulo = ? AND accion = ?");
    $stmt->execute([$p[0], $p[1]]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare("INSERT INTO permisos (modulo, accion, descripcion) VALUES (?, ?, ?)");
        $ins->execute([$p[0], $p[1], $p[2]]);
        $new_id = $pdo->lastInsertId();
        // Give permissions to Admin (Rol ID 1) by default
        $pdo->prepare("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (1, ?)")->execute([$new_id]);
    }
}
echo "OK P";
?>
