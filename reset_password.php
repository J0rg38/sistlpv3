<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = 'Enlace inválido o sin token visible.';
    header("Location: index.php");
    exit;
}

// Validar en la BD que el token existe y no ha expirado
$stmt = $pdo->prepare("SELECT id, nombre as nombres FROM usuarios WHERE token_recuperacion = ? AND instante_expiracion > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = 'El enlace de recuperación es inválido o ha caducado. Solicita uno nuevo.';
    header("Location: olvide_password.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - SistLP v3</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-800 to-indigo-900 min-h-screen flex items-center justify-center p-4">

    <!-- Decorative elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -right-[10%] w-[40%] h-[40%] bg-blue-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-40"></div>
        <div class="absolute top-[20%] -left-[10%] w-[30%] h-[30%] bg-purple-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-40"></div>
        <div class="absolute -bottom-[10%] right-[20%] w-[35%] h-[35%] bg-indigo-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-40"></div>
    </div>

    <div class="glass rounded-2xl shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] w-full max-w-md p-8 sm:p-10 relative z-10 transition-all duration-300 hover:shadow-[0_8px_40px_0_rgba(0,0,0,0.4)]">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                <i class="fas fa-key text-2xl text-green-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">Nueva Contraseña</h1>
            <p class="text-sm text-gray-600 font-medium">Hola <?= htmlspecialchars($user['nombres']) ?>, por favor elige tu nueva contraseña.</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3 text-red-500"></i>
                <p class="text-sm font-medium"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <form action="api_procesar_reset.php" method="POST" class="space-y-6" onsubmit="return validatePasswords()">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Nueva Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" required minlength="6" class="block w-full pl-10 pr-4 py-3 bg-white/60 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm" placeholder="Mínimo 6 caracteres">
                </div>
            </div>

            <div>
                <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-1">Confirmar Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-check text-gray-400"></i>
                    </div>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" class="block w-full pl-10 pr-4 py-3 bg-white/60 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm" placeholder="Vuelve a escribir la clave">
                </div>
                <p id="error-msg" class="text-red-500 text-xs mt-2 hidden font-medium">Las contraseñas no coinciden.</p>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all active:scale-[0.98]">
                    Actualizar y Acceder
                </button>
            </div>
        </form>

    </div>

    <script>
        function validatePasswords() {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            const errorMsg = document.getElementById('error-msg');
            
            if (pass !== confirm) {
                errorMsg.classList.remove('hidden');
                document.getElementById('password_confirm').classList.add('border-red-500');
                return false; // Prevent submit
            }
            
            errorMsg.classList.add('hidden');
            return true; // Allow submit
        }
    </script>
</body>
</html>
