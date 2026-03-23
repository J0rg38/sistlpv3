<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE comprobantes ADD codigo_motivo VARCHAR(2) NULL AFTER comprobante_relacionado_id");
    $pdo->exec("ALTER TABLE comprobantes ADD descripcion_motivo VARCHAR(255) NULL AFTER codigo_motivo");
    echo "Columnas de motivo SUNAT agregadas correctamente a comprobantes.\n";
} catch (PDOException $e) {
    if ($e->getCode() === '42S21') {
        echo "Las columnas de motivo ya existen.\n";
    } else {
        echo "Aviso: " . $e->getMessage() . "\n";
    }
}
?>
