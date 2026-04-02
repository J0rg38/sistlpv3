<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SistLP v3</title>
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
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-lock-open text-2xl text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">Recuperar Acceso</h1>
            <p class="text-sm text-gray-600 font-medium">Ingresa tu correo para recibir un enlace de reseteo.</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3 text-red-500"></i>
                <p class="text-sm font-medium"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-check-circle mt-0.5 mr-3 text-green-500"></i>
                <p class="text-sm font-medium"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            </div>
        <?php endif; ?>

        <form action="api_solicitar_recuperacion.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico Registrado</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" id="email" name="email" required class="block w-full pl-10 pr-4 py-3 bg-white/60 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm" placeholder="ejemplo@empresa.com">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-0.5">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Enlace
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <a href="index.php" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center justify-center">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Iniciar Sesión
            </a>
        </div>
    </div>
</body>
</html>
