<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
require_permission('usuarios', $id > 0 ? 'editar' : 'crear');

require_once '../../includes/header.php';

$user = [
    'nombre' => '', 'apellidos' => '', 'email' => '', 
    'tipo_documento' => 'DNI', 'numero_documento' => '', 
    'activo' => 1, 'rol_id' => ''
];
$title = "Nuevo Usuario";

if ($id > 0) {
    $title = "Editar Usuario";
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    if ($result) {
        $user = $result;
    } else {
        $_SESSION['error'] = "Usuario no encontrado.";
        echo '<script>window.location.href="index.php";</script>';
        exit;
    }
}

// Get roles 
$roles = $pdo->query("SELECT * FROM roles ORDER BY nombre")->fetchAll();
?>

<div class="mb-6">
    <a href="index.php" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center transition-colors w-fit">
        <i class="fas fa-arrow-left mr-2"></i> Regresar al listado
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden max-w-4xl">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-gray-900"><?= $title ?></h3>
            <p class="text-sm text-gray-500 mt-1">Ingresa la información personal y de acceso.</p>
        </div>
    </div>
    
    <form action="save.php" method="POST" class="p-6">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Información Personal</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Apellidos</label>
                <input type="text" name="apellidos" value="<?= htmlspecialchars($user['apellidos']) ?>" required class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Documento</label>
                <select name="tipo_documento" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="DNI" <?= $user['tipo_documento'] == 'DNI' ? 'selected' : '' ?>>DNI</option>
                    <option value="Pasaporte" <?= $user['tipo_documento'] == 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                    <option value="Cédula" <?= $user['tipo_documento'] == 'Cédula' ? 'selected' : '' ?>>Cédula</option>
                    <option value="RUC" <?= $user['tipo_documento'] == 'RUC' ? 'selected' : '' ?>>RUC</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Número de Documento</label>
                <input type="text" name="numero_documento" value="<?= htmlspecialchars($user['numero_documento']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2 mt-8">Configuración de Acceso</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Rol en el Sistema</label>
                <select name="rol_id" required class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">Selecciona un rol...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $user['rol_id'] == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Contraseña <?= $id > 0 ? '<span class="text-gray-400 font-normal">(Vacío para mantener actual)</span>' : '*' ?>
                </label>
                <input type="password" name="password" <?= $id == 0 ? 'required' : '' ?> class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all" placeholder="••••••••">
            </div>
            
            <div class="flex items-center mt-6">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="activo" value="1" class="sr-only peer" <?= $user['activo'] ? 'checked' : '' ?>>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-semibold text-gray-700">Usuario Activo</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-100 mt-6">
            <a href="index.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-6 rounded-lg mr-3 transition-colors">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                <i class="fas fa-save mr-2"></i> Guardar Usuario
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
