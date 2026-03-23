<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE series_facturacion ADD descripcion VARCHAR(255) NULL AFTER serie");
    echo "Columna descripcion agregada correctamente.\n";
} catch (PDOException $e) {
    if ($e->getCode() === '42S21') {
        echo "La columna descripcion ya existe.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
