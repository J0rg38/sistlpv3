<?php
require_once 'config/db.php';
try {
    // 1. Añadir columnas a comprobantes
    $pdo->exec("ALTER TABLE comprobantes 
        ADD codigo_tipo_documento VARCHAR(2) NULL AFTER tipo_comprobante,
        ADD cliente_numero_documento VARCHAR(20) NULL AFTER cliente_id,
        ADD cliente_razon_social VARCHAR(255) NULL AFTER cliente_numero_documento,
        ADD cliente_direccion_completa TEXT NULL AFTER cliente_razon_social");
    
    // 2. Añadir IGV a comprobantes_items
    $pdo->exec("ALTER TABLE comprobantes_items 
        ADD igv DECIMAL(10,4) NOT NULL DEFAULT 0.0000 AFTER importe_total");

    // 3. Update existing records with dummy/legacy data just to keep consistency
    $pdo->exec("UPDATE comprobantes c 
                JOIN clientes cli ON c.cliente_id = cli.id 
                SET c.cliente_numero_documento = cli.numero_documento, 
                    c.cliente_razon_social = IF(cli.tipo_cliente='EMPRESA', cli.razon_social, CONCAT(cli.nombres, ' ', cli.apellidos)),
                    c.cliente_direccion_completa = cli.direccion,
                    c.codigo_tipo_documento = CASE 
                        WHEN c.tipo_comprobante = 'FACTURA' THEN '01'
                        WHEN c.tipo_comprobante = 'BOLETA' THEN '03'
                        WHEN c.tipo_comprobante = 'NOTA_CREDITO' THEN '07'
                        WHEN c.tipo_comprobante = 'NOTA_DEBITO' THEN '08'
                        ELSE '00' END");

    $pdo->exec("UPDATE comprobantes_items 
                SET igv = (importe_total / 1.18) * 0.18");

    echo "Refactorización arquitectónica exitosa.\n";
} catch (PDOException $e) {
    echo "Aviso: " . $e->getMessage() . "\n";
}
?>
