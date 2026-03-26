<?php
// c:\xampp\htdocs\sistlpv3\config\sunat.php
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM empresa_config WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $row = null;
}

$endpoint = \Greenter\Ws\Services\SunatEndpoints::FE_BETA;

if (!$row) {
    // Retorno seguro inicial
    $row = [
        'ruc' => '20603850174',
        'razon_social' => 'TRANSPORTES TOMAS LINARES S.A.C.',
        'nombre_comercial' => 'TRANSPORTES TOMAS LINARES S.A.C.',
        'direccion' => 'CAL. JACINTO IBAÑEZ 490 LT. 01 MZ. E URB. PARQUE INDUSTRIAL',
        'ubigeo' => '040101',
        'departamento' => 'AREQUIPA',
        'provincia' => 'AREQUIPA',
        'distrito' => 'AREQUIPA',
        'sol_usuario' => 'FACTUR44',
        'sol_clave' => 'Transportes2',
        'certificado_path' => 'data/certs/sunat_cert.pfx',
        'certificado_clave' => '',
        'logo_path' => ''
    ];
} else {
    if($row['estado_facturacion'] == 1){
        $endpoint = \Greenter\Ws\Services\SunatEndpoints::FE_PRODUCCION;
    }else{}
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
            'cuenta_banco_nacion' => $row['cuenta_banco_nacion'] ?? ''
        ]
    ],
    'greenter' => [
        'endpoint' => $endpoint,
        'user' => $row['sol_usuario'],
        'password' => $row['sol_clave'],
        'cert_path' => __DIR__ . '/../' . $row['certificado_path'],
        'cert_clave' => $row['certificado_clave']
    ],
    'logo' => $row['logo_path']
];