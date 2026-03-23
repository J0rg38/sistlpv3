<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';
require_permission('clientes', 'ver');
require_once '../../includes/header.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $whereClause = "";
    $params = [];
    if ($search !== '') {
        $whereClause = "WHERE numero_documento LIKE ? OR razon_social LIKE ? OR nombres LIKE ? OR apellidos LIKE ? OR email LIKE ? OR telefono LIKE ?";
        $searchParam = "%$search%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    }

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM clientes $whereClause");
    $stmtTotal->execute($params);
    $total_records = $stmtTotal->fetchColumn();
    $total_pages = max(1, ceil($total_records / $limit));

    $stmt = $pdo->prepare("SELECT * FROM clientes $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al obtener clientes: " . $e->getMessage();
}
?>
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Clientes</h2>
        <p class="text-sm font-medium text-gray-500 mt-1">Administra la base de datos de clientes Naturales y Empresas.</p>
    </div>
    <div class="flex items-center space-x-3 w-full md:w-auto">
        <form method="GET" action="index.php" class="relative w-full md:w-64">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar cliente..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <?php if ($search !== ''): ?>
            <a href="index.php" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times-circle"></i>
            </a>
            <?php endif; ?>
        </form>
        <?php if (has_permission('clientes', 'crear')): ?>
        <a href="form.php" class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shrink-0">
            <i class="fas fa-plus mr-2"></i> Nuevo
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
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Documento</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th scope="col" class="px-6 py-4 flex justify-end text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $c): ?>
                        <?php 
                        $nombre_mostrar = $c['tipo_cliente'] == 'EMPRESA' 
                            ? $c['razon_social'] 
                            : $c['apellidos'] . ', ' . $c['nombres'];
                        $inicial = substr(trim($nombre_mostrar), 0, 1);
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 shrink-0 rounded-full bg-gradient-to-tr from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600 font-bold text-sm shadow-sm ring-1 ring-white">
                                        <?= strtoupper($inicial) ?>
                                    </div>
                                    <div class="ml-4 truncate">
                                        <div class="text-sm font-semibold text-gray-900 truncate max-w-xs"><?= htmlspecialchars($nombre_mostrar) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($c['departamento'] ? ($c['departamento'] . ' - ' . $c['provincia'] . ' - ' . $c['distrito']) : 'Ubicación no especificada') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-medium text-gray-500 uppercase"><?= htmlspecialchars($c['tipo_documento']) ?></span>
                                <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($c['numero_documento']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($c['tipo_cliente'] == 'EMPRESA'): ?>
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                                        Empresa
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-teal-50 text-teal-700 border border-teal-100">
                                        Natural
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 truncate max-w-[150px]"><i class="fas fa-phone text-gray-400 mr-1"></i> <?= htmlspecialchars($c['telefono'] ?: 'Sin teléfono') ?></div>
                                <div class="text-xs text-gray-500 truncate max-w-[150px]"><i class="fas fa-envelope text-gray-400 mr-1"></i> <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="hover:underline"><?= htmlspecialchars($c['email'] ?: 'Sin correo') ?></a></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <?php if (has_permission('clientes', 'editar')): ?>
                                    <a href="form.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (has_permission('clientes', 'eliminar')): ?>
                                    <a href="delete.php?id=<?= $c['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este cliente?');" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                            No hay clientes registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
