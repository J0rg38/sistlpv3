<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Alterar tabla empresa_config
    $pdo->exec("ALTER TABLE empresa_config ADD cuenta_banco_nacion VARCHAR(50) NULL AFTER logo_path");

    // 2. Alterar tabla comprobantes
    $pdo->exec("ALTER TABLE comprobantes 
        ADD tiene_detraccion TINYINT(1) DEFAULT 0 AFTER hash_cpe,
        ADD codigo_detraccion VARCHAR(10) NULL AFTER tiene_detraccion,
        ADD porcentaje_detraccion DECIMAL(5,2) NULL AFTER codigo_detraccion,
        ADD monto_detraccion DECIMAL(10,2) NULL AFTER porcentaje_detraccion,
        ADD tiene_retencion TINYINT(1) DEFAULT 0 AFTER monto_detraccion,
        ADD porcentaje_retencion DECIMAL(5,2) NULL AFTER tiene_retencion,
        ADD monto_retencion DECIMAL(10,2) NULL AFTER porcentaje_retencion");

    echo "Migración de Detracciones y Retenciones exitosa.\n";
} catch (PDOException $e) {
    echo "Aviso: " . $e->getMessage() . "\n";
}
?>
