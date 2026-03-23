<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';
require_permission('roles', 'ver');
require_once '../../includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT r.*, COUNT(u.id) as total_usuarios 
        FROM roles r 
        LEFT JOIN usuarios u ON r.id = u.rol_id 
        GROUP BY r.id 
        ORDER BY r.id ASC
    ");
    $roles = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al obtener roles: " . $e->getMessage();
}
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Roles y Permisos</h2>
        <p class="text-sm font-medium text-gray-500 mt-1">Configura niveles de acceso para los usuarios.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <?php if (has_permission('roles', 'crear')): ?>
        <a href="form.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-plus mr-2 -ml-1"></i> Nuevo Rol
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-start" role="alert">
        <i class="fas fa-check-circle mt-0.5 mr-3"></i>
        <p class="text-sm font-medium"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-start" role="alert">
        <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
        <p class="text-sm font-medium"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if(!empty($roles)): ?>
        <?php foreach($roles as $r): ?>
        <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-600 flex items-center justify-center text-xl shadow-sm border border-indigo-100">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="flex space-x-2">
                    <?php if (has_permission('roles', 'editar')): ?>
                    <a href="form.php?id=<?= $r['id'] ?>" class="text-gray-400 hover:text-blue-600 p-2 transition-colors"><i class="fas fa-edit"></i></a>
                    <?php endif; ?>
                    
                    <?php if (has_permission('roles', 'eliminar')): ?>
                        <?php if ($r['total_usuarios'] == 0): ?>
                        <a href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este rol?');" class="text-gray-400 hover:text-red-600 p-2 transition-colors"><i class="fas fa-trash"></i></a>
                        <?php else: ?>
                        <span class="text-gray-300 p-2 cursor-not-allowed" title="No se puede eliminar porque tiene usuarios asignados"><i class="fas fa-trash"></i></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <h3 class="text-lg font-bold text-gray-900 mb-1"><?= htmlspecialchars($r['nombre']) ?></h3>
            <p class="text-sm text-gray-500 flex-1 line-clamp-2"><?= htmlspecialchars($r['descripcion']) ?></p>
            
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Usuarios Asignados</span>
                <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700">
                    <i class="fas fa-users mr-1.5"></i> <?= $r['total_usuarios'] ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full bg-white p-10 text-center rounded-2xl border border-gray-200 border-dashed">
            <p class="text-gray-500">No hay roles configurados.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
