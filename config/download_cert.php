<?php
$cert_url = 'https://raw.githubusercontent.com/thegreenter/xmldsig/master/tests/Resources/certificate.pem';
$dest = __DIR__ . '/../data/certs/certificate.pem';

if (!file_exists($dest)) {
    if (!is_dir(dirname($dest))) {
        mkdir(dirname($dest), 0777, true);
    }
    $cert = file_get_contents($cert_url);
    if ($cert) {
        file_put_contents($dest, $cert);
        echo "Certificado de prueba Greenter descargado correctamente.\n";
    } else {
        echo "Error al descargar el certificado.\n";
    }
} else {
    echo "El certificado ya existe.\n";
}
?>
