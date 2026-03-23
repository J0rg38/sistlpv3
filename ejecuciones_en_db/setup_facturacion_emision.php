<?php
require_once 'config/db.php';

try {
    $pdo->beginTransaction();

    // 1. Create the comprobantes table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comprobantes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_comprobante VARCHAR(50) NOT NULL COMMENT 'BOLETA, FACTURA, NOTA_CREDITO, NOTA_DEBITO',
        serie VARCHAR(4) NOT NULL,
        correlativo INT NOT NULL,
        cliente_id INT NOT NULL,
        comprobante_relacionado_id INT NULL,
        codigo_motivo VARCHAR(2) NULL,
        descripcion_motivo VARCHAR(255) NULL,
        fecha_emision DATE NOT NULL,
        fecha_vencimiento DATE NULL,
        moneda VARCHAR(3) NOT NULL DEFAULT 'PEN',
        tipo_cambio DECIMAL(10,4) NULL,
        condicion_pago VARCHAR(50) NOT NULL DEFAULT 'CONTADO',
        dias_credito INT NULL DEFAULT 0,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        igv DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        estado VARCHAR(20) NOT NULL DEFAULT 'EMITIDO' COMMENT 'EMITIDO, ANULADO',
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_comprobante (tipo_comprobante, serie, correlativo),
        FOREIGN KEY (comprobante_relacionado_id) REFERENCES comprobantes(id)
    )");

    // 2. Create the comprobantes_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comprobantes_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        comprobante_id INT NOT NULL,
        codigo VARCHAR(50) NULL,
        descripcion TEXT NOT NULL,
        unidad_medida VARCHAR(20) NOT NULL DEFAULT 'NIU',
        cantidad DECIMAL(10,2) NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        importe_total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE
    )");

    // 3. Insert permissions for facturacion_emision
    $perms = [
        ['facturacion_emision', 'ver', 'Ver listado de comprobantes emitidos'],
        ['facturacion_emision', 'crear', 'Emitir nuevos comprobantes'],
        ['facturacion_emision', 'anular', 'Anular comprobantes']
    ];

    foreach ($perms as $p) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO permisos (modulo, accion, descripcion) VALUES (?, ?, ?)");
        $stmt->execute($p);
    }

    // 4. Assign permissions to Admin role (id = 1)
    $pdo->exec("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) 
                SELECT 1, id FROM permisos WHERE modulo = 'facturacion_emision'");

    $pdo->commit();
    echo "Migración de comprobantes y detalle de ítems completada exitosamente.\n";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error en la migración: " . $e->getMessage());
}
?>
