<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, nombre, codigo, modulo_padre_id FROM modulos WHERE codigo LIKE 'facturacion%'");
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($modulos);

$stmt = $pdo->query("SELECT * FROM roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);
?>
