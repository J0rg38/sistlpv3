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
    <title>Login - SisTLP v3</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <div class="text-center mb-10">
            <img src="data/img/logo_factura.png" alt="Logo" class="h-15 mr-3">
            <p class="text-gray-600 font-medium">Gestión Empresarial</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow-sm flex items-start" role="alert">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <p class="text-sm font-medium"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                    </div>
                    <input type="email" id="email" name="email" required class="block w-full pl-10 pr-4 py-3 bg-white/60 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm" placeholder="admin@admin.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <input type="password" id="password" name="password" required class="block w-full pl-10 pr-4 py-3 bg-white/60 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm" placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between mt-2">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-600 cursor-pointer">Recordarme</label>
                </div>
                <div class="text-sm">
                    <a href="olvide_password.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">¿Olvidaste tu contraseña?</a>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all active:scale-[0.98]">
                Iniciar Sesión
            </button>
        </form>
    </div>

</body>
</html>
