<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE comprobantes ADD comprobante_relacionado_id INT NULL AFTER cliente_id");
    $pdo->exec("ALTER TABLE comprobantes ADD FOREIGN KEY (comprobante_relacionado_id) REFERENCES comprobantes(id)");
    echo "Columna comprobante_relacionado_id agregada.\n";
} catch (PDOException $e) {
    echo "Aviso: " . $e->getMessage() . "\n";
}
?>
