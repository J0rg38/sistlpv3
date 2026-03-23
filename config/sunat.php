<?php
// c:\xampp\htdocs\sistlpv3\config\sunat.php
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM empresa_config WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $row = null;
}

if (!$row) {
    // Retorno seguro inicial
    $row = [
        'ruc' => '20000000001',
        'razon_social' => 'EMPRESA POR DEFECTO S.A.C.',
        'nombre_comercial' => 'EMPRESA POR DEFECTO',
        'direccion' => 'AV. DESCONOCIDA',
        'ubigeo' => '150101',
        'departamento' => 'LIMA',
        'provincia' => 'LIMA',
        'distrito' => 'LIMA',
        'sol_usuario' => 'MODDATOS',
        'sol_clave' => 'moddatos',
        'certificado_path' => 'data/certs/certificate.pem',
        'logo_path' => ''
    ];
}

return [
    'empresa' => [
        'ruc' => $row['ruc'],
        'razon_social' => $row['razon_social'],
        'nombre_comercial' => $row['nombre_comercial'],
        'direccion' => [
            'ubigeo' => $row['ubigeo'],
            'departamento' => $row['departamento'],
            'provincia' => $row['provincia'],
            'distrito' => $row['distrito'],
            'direccion' => $row['direccion'],
        ]
    ],
    'greenter' => [
        'endpoint' => \Greenter\Ws\Services\SunatEndpoints::FE_BETA,
        'user' => $row['sol_usuario'],
        'password' => $row['sol_clave'],
        'cert_path' => __DIR__ . '/../' . $row['certificado_path']
    ],
    'logo' => $row['logo_path']
];
