<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';
require_permission('usuarios', 'ver');
require_once '../../includes/header.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $whereClause = "";
    $params = [];
    if ($search !== '') {
        $whereClause = "WHERE u.nombre LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ? OR u.numero_documento LIKE ? OR r.nombre LIKE ?";
        $searchParam = "%$search%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    }

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id $whereClause");
    $stmtTotal->execute($params);
    $total_records = $stmtTotal->fetchColumn();
    $total_pages = max(1, ceil($total_records / $limit));

    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.apellidos, u.email, u.tipo_documento, u.numero_documento, u.activo, u.creado_en, r.nombre AS rol_nombre 
        FROM usuarios u 
        LEFT JOIN roles r ON u.rol_id = r.id 
        $whereClause 
        ORDER BY u.id DESC 
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al obtener usuarios: " . $e->getMessage();
}
?>
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Usuarios</h2>
        <p class="text-sm font-medium text-gray-500 mt-1">Administra los accesos al sistema empresarial.</p>
    </div>
    <div class="flex items-center space-x-3 w-full md:w-auto">
        <form method="GET" action="index.php" class="relative w-full md:w-64">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar usuario..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <?php if ($search !== ''): ?>
            <a href="index.php" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times-circle"></i>
            </a>
            <?php endif; ?>
        </form>
        <?php if (has_permission('usuarios', 'crear')): ?>
        <a href="form.php" class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shrink-0">
            <i class="fas fa-plus mr-2"></i> Nuevo Usuario
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

<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Documento</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rol</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-4 flex justify-end text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 shrink-0 rounded-full bg-gradient-to-tr from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600 font-bold text-sm shadow-sm ring-1 ring-white">
                                        <?= strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellidos'], 0, 1)) ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($u['tipo_documento'] . ': ' . $u['numero_documento']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <?= htmlspecialchars($u['rol_nombre'] ?? 'Sin rol') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($u['activo'] == 1): ?>
                                    <span class="px-2.5 py-1 inline-flex items-center text-xs font-semibold rounded-full bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 inline-flex items-center text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <?php if (has_permission('usuarios', 'editar')): ?>
                                    <a href="form.php?id=<?= $u['id'] ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (has_permission('usuarios', 'eliminar')): ?>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete.php?id=<?= $u['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </a>
                                        <?php else: ?>
                                        <span class="text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg cursor-not-allowed" title="No puedes eliminarte a ti mismo">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
    
    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6 flex items-center justify-between rounded-b-2xl">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando de <span class="font-bold text-gray-900"><?= $total_records > 0 ? $offset + 1 : 0 ?></span> a <span class="font-bold text-gray-900"><?= min($offset + $limit, $total_records) ?></span> de <span class="font-bold text-gray-900"><?= $total_records ?></span> resultados
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                    <?php 
                    $qs = '';
                    if ($search !== '') {
                        $qs = '&search=' . urlencode($search);
                    }
                    ?>
                    
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $qs ?>" class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                        <span class="sr-only">Anterior</span>
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                    <?php else: ?>
                    <span class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-gray-200 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                        <span class="sr-only">Anterior</span>
                        <i class="fas fa-chevron-left text-xs"></i>
                    </span>
                    <?php endif; ?>
                    
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<span class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500">...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <a href="?page=<?= $i ?><?= $qs ?>" aria-current="page" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium transition-colors <?= $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-700 font-bold' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php 
                    if ($end_page < $total_pages) {
                        echo '<span class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500">...</span>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $qs ?>" class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                        <span class="sr-only">Siguiente</span>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <?php else: ?>
                    <span class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-gray-200 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                        <span class="sr-only">Siguiente</span>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </span>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
        
        <!-- Paginación Mobile -->
        <div class="flex items-center justify-between w-full sm:hidden">
            <a href="<?= $page > 1 ? '?page='.($page-1).$qs : '#' ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors mt-2 <?= $page <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                Anterior
            </a>
            <span class="text-sm text-gray-700 mt-2 font-medium">Pág <?= $page ?> de <?= $total_pages ?></span>
            <a href="<?= $page < $total_pages ? '?page='.($page+1).$qs : '#' ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors mt-2 <?= $page >= $total_pages ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                Siguiente
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
