<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_permission('facturacion_configuracion', 'editar');

try {
    $stmt = $pdo->prepare("SELECT * FROM empresa_config WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    $certificado_path = $config['certificado_path'];
    $logo_path = $config['logo_path'];

    // Procesar Certificado
    if (isset($_FILES['certificado']) && $_FILES['certificado']['error'] == UPLOAD_ERR_OK) {
        $tmpName = $_FILES['certificado']['tmp_name'];
        $name = basename($_FILES['certificado']['name']);
        // Sanitizar y obligar extension segura
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($ext, ['pem', 'crt', 'p12', 'pfx'])) {
            $destPath = '../../../data/certs/sunat_cert.' . $ext;
            if (move_uploaded_file($tmpName, $destPath)) {
                $certificado_path = 'data/certs/sunat_cert.' . $ext;
            } else {
                throw new Exception("Error al mover el certificado subido.");
            }
        } else {
            throw new Exception("Formato de certificado inválido.");
        }
    }

    // Procesar Logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        $tmpName = $_FILES['logo']['tmp_name'];
        $name = basename($_FILES['logo']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $destPath = '../../../data/img/logo_factura.' . $ext;
            if (!is_dir('../../../data/img')) mkdir('../../../data/img', 0777, true);
            
            if (move_uploaded_file($tmpName, $destPath)) {
                $logo_path = 'data/img/logo_factura.' . $ext;
            } else {
                throw new Exception("Error al guardar el logo.");
            }
        }
    }

    $stmtUpd = $pdo->prepare("UPDATE empresa_config SET 
        ruc = ?, razon_social = ?, nombre_comercial = ?, direccion = ?, 
        ubigeo = ?, departamento = ?, provincia = ?, distrito = ?, 
        sol_usuario = ?, sol_clave = ?, 
        certificado_path = ?, certificado_clave = ?, logo_path = ?, cuenta_banco_nacion = ?, estado_facturacion = ? 
        WHERE id = 1");
        
    $stmtUpd->execute([
        $_POST['ruc'] ?? $config['ruc'],
        $_POST['razon_social'] ?? $config['razon_social'],
        $_POST['nombre_comercial'] ?? $config['nombre_comercial'],
        $_POST['direccion'] ?? $config['direccion'],
        $_POST['ubigeo'] ?? $config['ubigeo'],
        $_POST['departamento'] ?? $config['departamento'],
        $_POST['provincia'] ?? $config['provincia'],
        $_POST['distrito'] ?? $config['distrito'],
        $_POST['sol_usuario'] ?? $config['sol_usuario'],
        $_POST['sol_clave'] ?? $config['sol_clave'],
        $certificado_path,
        $_POST['certificado_clave'] ?? $config['certificado_clave'],
        $logo_path,
        $_POST['cuenta_banco_nacion'] ?? $config['cuenta_banco_nacion'],
        isset($_POST['estado_facturacion']) ? 1 : 0
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
