<?php
require 'config/db.php';
try {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS tipo_cambio (
        fecha DATE PRIMARY KEY,
        compra DECIMAL(10,3) NOT NULL,
        venta DECIMAL(10,3) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "OK";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
