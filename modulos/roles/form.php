<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
require_permission('roles', $id > 0 ? 'editar' : 'crear');

require_once '../../includes/header.php';

$rol = ['nombre' => '', 'descripcion' => ''];
$rol_permisos = [];
$title = "Nuevo Rol";

if ($id > 0) {
    $title = "Editar Rol";
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    if ($result) {
        $rol = $result;
        $permStmt = $pdo->prepare("SELECT permiso_id FROM rol_permisos WHERE rol_id = ?");
        $permStmt->execute([$id]);
        $rol_permisos = $permStmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $_SESSION['error'] = "Rol no encontrado.";
        echo '<script>window.location.href="index.php";</script>';
        exit;
    }
}

// Get all permissions grouped by module
$all_permisos = [];
try {
    $stmt = $pdo->query("SELECT * FROM permisos ORDER BY modulo, accion");
    foreach ($stmt->fetchAll() as $p) {
        $all_permisos[$p['modulo']][] = $p;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error cargando permisos: " . $e->getMessage();
}
?>

<div class="mb-6">
    <a href="index.php" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center transition-colors w-fit">
        <i class="fas fa-arrow-left mr-2"></i> Regresar a Roles
    </a>
</div>

<form action="save.php" method="POST">
    <input type="hidden" name="id" value="<?= $id ?>">
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Col: Role Details -->
        <div class="lg:col-span-1 border border-gray-200/60 bg-white rounded-2xl shadow-sm p-6 self-start">
            <h3 class="text-lg font-bold text-gray-900 mb-5 border-b border-gray-100 pb-3"><?= $title ?></h3>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre del Rol *</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($rol['nombre']) ?>" required placeholder="Ej: Vendedor" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
                <textarea name="descripcion" rows="3" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Describe brevemente este nivel de acceso..."><?= htmlspecialchars($rol['descripcion']) ?></textarea>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center">
                <i class="fas fa-save mr-2"></i> Guardar Rol y Permisos
            </button>
        </div>
        
        <!-- Right Col: Permissions Matrix -->
        <div class="lg:col-span-2 border border-gray-200/60 bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Matriz de Permisos</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Selecciona a qué partes del sistema tendrá acceso este rol.</p>
                </div>
            </div>
            
            <div class="p-6">
                <?php foreach ($all_permisos as $modulo => $permisos): ?>
                <div class="mb-8 last:mb-0">
                    <h4 class="text-base font-bold text-gray-800 mb-3 flex items-center capitalize">
                        <i class="fas fa-folder-open text-blue-500 mr-2"></i> Módulo: <?= htmlspecialchars($modulo) ?>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <?php foreach ($permisos as $p): ?>
                            <?php $isChecked = in_array($p['id'], $rol_permisos); ?>
                            <label class="flex items-start p-2 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="permisos[]" value="<?= $p['id'] ?>" <?= $isChecked ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                </div>
                                <div class="ml-3 text-sm flex flex-col">
                                    <span class="font-semibold text-gray-900 capitalize"><?= htmlspecialchars($p['accion']) ?></span>
                                    <span class="text-gray-500 text-xs mt-0.5"><?= htmlspecialchars($p['descripcion']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>
</form>

<?php require_once '../../includes/footer.php'; ?>
