<?php
require_once 'config/db.php';

try {
    // 1. Crear tabla 'clientes'
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_cliente ENUM('NATURAL', 'EMPRESA') NOT NULL DEFAULT 'NATURAL',
        tipo_documento VARCHAR(20) NOT NULL DEFAULT 'DNI',
        numero_documento VARCHAR(50) NOT NULL UNIQUE,
        razon_social VARCHAR(255) NULL,
        nombres VARCHAR(100) NULL,
        apellidos VARCHAR(100) NULL,
        direccion VARCHAR(255) NULL,
        departamento VARCHAR(100) NULL,
        provincia VARCHAR(100) NULL,
        distrito VARCHAR(100) NULL,
        telefono VARCHAR(50) NULL,
        email VARCHAR(150) NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Agregar permisos base
    $perms = [
        ['clientes', 'ver', 'Ver listado de clientes'],
        ['clientes', 'crear', 'Crear nuevos clientes'],
        ['clientes', 'editar', 'Editar clientes existentes'],
        ['clientes', 'eliminar', 'Eliminar clientes']
    ];

    foreach ($perms as $p) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO permisos (modulo, accion, descripcion) VALUES (?, ?, ?)");
        $stmt->execute($p);
    }

    // 3. Asignar los nuevos permisos al rol Administrador (suponiendo que su id es 1, lo buscaremos de todos modos)
    $adminRoleQuery = $pdo->query("SELECT id FROM roles WHERE nombre = 'Administrador'");
    $adminRoleId = $adminRoleQuery->fetchColumn();
    
    if ($adminRoleId) {
        $pdo->exec("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) 
                    SELECT $adminRoleId, id FROM permisos WHERE modulo = 'clientes'");
    }

    echo "Base de datos actualizada correctamente para el modulo Clientes.\n";
} catch (PDOException $e) {
    die("Error en migracion: " . $e->getMessage());
}
?>
