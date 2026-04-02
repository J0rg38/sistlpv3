<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'siscisne@cisne.com.pe';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = '/test';

try {
    require 'api_solicitar_recuperacion.php';
} catch (Throwable $e) {
    echo "FATAL ERROR CAUGHT: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}
