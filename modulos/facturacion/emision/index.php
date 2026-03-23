<?php
session_start();
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

require_permission('facturacion_emision', 'ver'); 

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = "1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (c.cliente_numero_documento LIKE ? OR c.cliente_razon_social LIKE ? OR CONCAT(c.serie, '-', c.correlativo) LIKE ?)";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam];
}

// Contar total
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM comprobantes c WHERE $where");
$stmtTotal->execute($params);
$total_records = $stmtTotal->fetchColumn();
$total_pages = max(1, ceil($total_records / $limit));

// Obtener datos
$sql = "
    SELECT c.* 
    FROM comprobantes c
    WHERE $where
    ORDER BY c.fecha_emision DESC, c.id DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comprobantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$base_url = '../../../';
require_once '../../../includes/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4" x-data="comprobantesList()">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Comprobantes de Pago</h2>
        <p class="text-sm font-medium text-gray-500 mt-1">Histórico de facturación electrónica y comunicación con SUNAT.</p>
    </div>
    
    <div class="flex items-center space-x-3 w-full md:w-auto">
        <form method="GET" action="index.php" class="relative w-full md:w-64">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar comprobante..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <?php if ($search !== ''): ?>
            <a href="index.php" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times-circle"></i>
            </a>
            <?php endif; ?>
        </form>
        
        <?php if (has_permission('facturacion_emision', 'crear')): ?>
        <a href="form.php" class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shrink-0">
            <i class="fas fa-plus mr-2"></i> Emitir Nuevo
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden" x-data="comprobantesList()">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Comprobante</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Emisión</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado SUNAT</th>
                    <th scope="col" class="px-6 py-4 flex justify-end text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($comprobantes)): ?>
                    <?php foreach ($comprobantes as $c): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-medium text-gray-500 uppercase"><?= htmlspecialchars($c['tipo_comprobante']) ?></span>
                                <div class="text-sm font-bold text-blue-600"><?= $c['serie'] ?>-<?= str_pad($c['correlativo'], 8, '0', STR_PAD_LEFT) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900 truncate max-w-xs"><?= htmlspecialchars($c['cliente_numero_documento']) ?></div>
                                <div class="text-xs text-gray-500 truncate max-w-xs"><?= htmlspecialchars($c['cliente_razon_social']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= date('d/m/Y', strtotime($c['fecha_emision'])) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?= $c['moneda'] === 'PEN' ? 'S/' : '$' ?> <?= number_format($c['total'], 2) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $badge = 'bg-gray-100 text-gray-800 border-gray-200';
                                    if($c['estado_sunat'] === 'ACEPTADO') $badge = 'bg-green-50 text-green-700 border-green-200';
                                    if($c['estado_sunat'] === 'RECHAZADO') $badge = 'bg-red-50 text-red-700 border-red-200';
                                    if($c['estado_sunat'] === 'EXCEPCION') $badge = 'bg-orange-50 text-orange-700 border-orange-200';
                                    if($c['estado_sunat'] === 'PENDIENTE') $badge = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border <?= $badge ?>">
                                    <?= htmlspecialchars($c['estado_sunat']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <?php if ($c['estado_sunat'] !== 'ACEPTADO'): ?>
                                        <button @click="enviarSunat(<?= $c['id'] ?>)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors" title="Enviar a SUNAT">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button @click="descargarPDF(<?= $c['id'] ?>)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    
                                    <?php if($c['archivo_xml']): ?>
                                        <a href="../../../data/xml/<?= $c['archivo_xml'] ?>" download class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors" title="Descargar XML">
                                            <i class="fas fa-file-code"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if($c['archivo_cdr']): ?>
                                        <a href="../../../data/cdr/<?= $c['archivo_cdr'] ?>" download class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg transition-colors" title="Descargar CDR">
                                            <i class="fas fa-file-archive"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            No hay comprobantes emitidos.
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
                    if ($search !== '') $qs = '&search=' . urlencode($search);
                    ?>
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $qs ?>" class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                        <span class="sr-only">Anterior</span>
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                    <?php endif; ?>
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <a href="?page=<?= $i ?><?= $qs ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium transition-colors <?= $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-700 font-bold' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $qs ?>" class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                        <span class="sr-only">Siguiente</span>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('comprobantesList', () => ({
        enviarSunat(id) {
            this.showToast('Enviando comprobante a SUNAT...', 'info');
            fetch('api_sunat_enviar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.showToast('Comprobante aceptado por SUNAT!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showToast('Error SUNAT: ' + (data.error || 'Desconocido'), 'error');
                }
            })
            .catch(err => {
                this.showToast('Error de conexión', 'error');
            });
        },
        descargarPDF(id) {
            window.open(`generar_pdf.php?id=${id}`, '_blank');
        },
        showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let bgClass = 'bg-green-600';
            if(type === 'error') bgClass = 'bg-red-600';
            if(type === 'info') bgClass = 'bg-blue-600';
            
            toast.className = `${bgClass} text-white px-6 py-3 rounded-lg shadow-xl transition-all duration-300 opacity-0 transform translate-y-4 font-medium text-sm border flex items-center`;
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : (type==='error'?'fa-times-circle':'fa-info-circle')} mr-2 text-lg"></i> ${message}`;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-4');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }));
});
</script>

<?php require_once '../../../includes/footer.php'; ?>
