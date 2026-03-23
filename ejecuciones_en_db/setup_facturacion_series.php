<?php
require_once 'config/db.php';

try {
    $pdo->beginTransaction();

    // 1. Create the series_facturacion table
    $pdo->exec("CREATE TABLE IF NOT EXISTS series_facturacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_comprobante VARCHAR(50) NOT NULL COMMENT 'BOLETA, FACTURA, NOTA_CREDITO, NOTA_DEBITO',
        serie VARCHAR(4) NOT NULL COMMENT 'B001, F001',
        descripcion VARCHAR(255) NULL COMMENT 'Para qué se destina esta serie',
        correlativo_actual INT NOT NULL DEFAULT 1,
        estado BOOLEAN NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_tipo_serie (tipo_comprobante, serie)
    )");

    // 2. Insert permissions for facturacion_series
    $perms = [
        ['facturacion_series', 'ver', 'Ver listado de series de facturación'],
        ['facturacion_series', 'crear', 'Crear nuevas series de facturación'],
        ['facturacion_series', 'editar', 'Editar series de facturación'],
        ['facturacion_series', 'eliminar', 'Eliminar series de facturación']
    ];

    foreach ($perms as $p) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO permisos (modulo, accion, descripcion) VALUES (?, ?, ?)");
        $stmt->execute($p);
    }

    // 3. Assign permissions to Admin role (assuming ID 1 is Administrador)
    $pdo->exec("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) 
                SELECT 1, id FROM permisos WHERE modulo = 'facturacion_series'");

    $pdo->commit();
    echo "Migración de series de facturación completada exitosamente.\n";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error en la migración: " . $e->getMessage());
}
?>
