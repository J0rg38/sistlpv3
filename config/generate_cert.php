<?php
$configArgs = array(
    'config' => 'C:/xampp/apache/conf/openssl.cnf' // O buscar 'C:/xampp/php/extras/ssl/openssl.cnf'
);
if (!file_exists($configArgs['config'])) {
    $configArgs['config'] = 'C:/xampp/php/extras/ssl/openssl.cnf';
}
if (!file_exists($configArgs['config'])) {
    die("No se encontro openssl.cnf");
}

$privkey = openssl_pkey_new($configArgs);
if (!$privkey) {
    die("Error pkey_new: " . openssl_error_string());
}

openssl_pkey_export($privkey, $pkeyout, null, $configArgs);

$dn = array(
    "countryName" => "PE",
    "stateOrProvinceName" => "Lima",
    "localityName" => "Lima",
    "organizationName" => "SISTLPV3",
    "organizationalUnitName" => "IT",
    "commonName" => "SISTLPV3 Test",
    "emailAddress" => "test@test.com"
);

$csr = openssl_csr_new($dn, $privkey, $configArgs);
$x509 = openssl_csr_sign($csr, null, $privkey, 365, $configArgs);
openssl_x509_export($x509, $certout);

file_put_contents(__DIR__ . '/../data/certs/certificate.pem', $certout . "\n" . $pkeyout);
echo "OK\n";
