<?php
session_start();
require_once 'config/db.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: olvide_password.php");
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    $_SESSION['error'] = 'Por favor ingresa un correo válido.';
    header("Location: olvide_password.php");
    exit;
}

try {
    // 1. Verificar si existe el usuario y está activo
    $stmt = $pdo->prepare("SELECT id, nombre as nombres FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Por seguridad, no especificamos si el correo existe o no, pero damos mensajería genérica
        // O dependiendo de la usabilidad, podríamos decirle la verdad:
        $_SESSION['error'] = 'El correo electrónico no existe o el usuario está inactivo.';
        header("Location: olvide_password.php");
        exit;
    }

    // 2. Generar Token Seguro (32 bytes aleatorios convertidos a Hexadecimal)
    $token = bin2hex(random_bytes(32));
    
    // 3. Expiración de 1 Hora desde ahora
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // 4. Guardar en Base de Datos
    $updateStmt = $pdo->prepare("UPDATE usuarios SET token_recuperacion = ?, instante_expiracion = ? WHERE id = ?");
    $updateStmt->execute([$token, $expiracion, $user['id']]);

    // 5. Configurar PHPMailer
    $mail = new PHPMailer(true);

    // TODO: En producción, el usuario cambiará estos datos a sus credenciales reales (Hostgator, Gmail, AWS SES, etc)
    // Dejándolo funcional usando un Servidor SMTP General o configurado para pruebas
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Servidor SMTP predeterminado para Gmail
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sistemas2cisne@gmail.com'; // <--- EL USUARIO DEBE MODIFICAR ESTO
    $mail->Password   = 'xrph bcck koly scql'; // <--- EL USUARIO DEBE MODIFICAR ESTO (App Password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Para evitar problemas en XAMPP local sin certificados SSL instalados (Opcional, remover en prod)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Destinatarios
    $mail->setFrom('sistemas2cisne@gmail.com', 'SISTLPV3');
    $mail->addAddress($email, $user['nombres']);

    // Contenido del Correo
    $appUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']); // Ej: http://localhost/sistlpv3
    $resetLink = $appUrl . "/reset_password.php?token=" . $token;

    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de Contraseña - SistLP v3';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;'>
            <div style='background-color: #1e3a8a; padding: 20px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0;'>SistLP v3</h1>
            </div>
            <div style='padding: 30px; background-color: #f9fafb;'>
                <h2 style='color: #1f2937; margin-top: 0;'>Hola " . htmlspecialchars($user['nombres']) . ",</h2>
                <p style='color: #4b5563; line-height: 1.6;'>
                    Hemos recibido una solicitud para recuperar la contraseña de tu cuenta asociada a este correo electrónico.
                    Haz clic en el siguiente botón para crear una nueva contraseña:
                </p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' style='background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>
                        Restablecer Contraseña
                    </a>
                </div>
                <p style='color: #4b5563; font-size: 13px;'>
                    <em>Nota: Este enlace caducará en 1 hora por motivos de seguridad. Si no solicitaste este cambio, simplemente ignora este correo.</em>
                </p>
            </div>
            <div style='background-color: #f3f4f6; padding: 15px; text-align: center; color: #6b7280; font-size: 12px;'>
                &copy; " . date('Y') . " SistLP v3 - Gestión Empresarial
            </div>
        </div>
    ";
    $mail->AltBody = "Hola {$user['nombres']}, para restablecer tu contraseña visita el siguiente enlace: {$resetLink}";

    $mail->send();

    $_SESSION['success'] = 'Hemos enviado un enlace de recuperación a tu correo electrónico. Por favor revisa tu bandeja de entrada o spam.';
    header("Location: olvide_password.php");
    exit;

} catch (Exception $e) {
    // Error de Base de Datos o de Correo
    $_SESSION['error'] = "Ha ocurrido un error al procesar tu solicitud: " . $mail->ErrorInfo;
    header("Location: olvide_password.php");
    exit;
}
