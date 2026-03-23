<?php
require_once 'config/db.php';
try {
    // Modify comprobantes table to support SUNAT transmission status
    $pdo->exec("ALTER TABLE comprobantes 
        ADD estado_sunat ENUM('PENDIENTE', 'ACEPTADO', 'RECHAZADO', 'EXCEPCION') NOT NULL DEFAULT 'PENDIENTE' AFTER estado,
        ADD archivo_xml VARCHAR(255) NULL AFTER estado_sunat,
        ADD archivo_cdr VARCHAR(255) NULL AFTER archivo_xml,
        ADD hash_cpe VARCHAR(255) NULL AFTER archivo_cdr");

    echo "Tabla comprobantes optimizada para CDRs SUNAT.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Las columnas de SUNAT ya existen.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
