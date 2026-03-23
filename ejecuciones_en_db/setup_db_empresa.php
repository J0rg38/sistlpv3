<?php
require_once 'config/db.php';
$pdo->exec("CREATE TABLE IF NOT EXISTS empresa_config (id INT PRIMARY KEY AUTO_INCREMENT, ruc VARCHAR(20), razon_social VARCHAR(255), nombre_comercial VARCHAR(255), direccion TEXT, ubigeo VARCHAR(6), departamento VARCHAR(50), provincia VARCHAR(50), distrito VARCHAR(50), sol_usuario VARCHAR(50), sol_clave VARCHAR(50), certificado_path VARCHAR(255), logo_path VARCHAR(255))");
$pdo->exec("INSERT IGNORE INTO empresa_config (id, ruc, razon_social, nombre_comercial, direccion, ubigeo, departamento, provincia, distrito, sol_usuario, sol_clave, certificado_path, logo_path) VALUES (1, '20000000001', 'EMPRESA BASE S.A.C.', 'SISTLPV3 ERP', 'AV. CUALQUIERA 123', '150101', 'LIMA', 'LIMA', 'LIMA', 'MODDATOS', 'moddatos', 'data/certs/certificate.pem', '')");
echo "OK DB";
?>
