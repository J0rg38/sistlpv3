<?php
session_start();
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

require_permission('facturacion_emision', 'ver'); 

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo_filtro = isset($_GET['tipo_filtro']) ? trim($_GET['tipo_filtro']) : '';

$where = "1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (c.cliente_numero_documento LIKE ? OR c.cliente_razon_social LIKE ? OR CONCAT(c.serie, '-', c.correlativo) LIKE ?)";
    $searchParam = "%$search%";
    array_push($params, $searchParam, $searchParam, $searchParam);
}

if ($tipo_filtro !== '') {
    $where .= " AND c.tipo_comprobante = ?";
    $params[] = $tipo_filtro;
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
        <form method="GET" action="index.php" class="relative w-full md:w-auto flex items-center space-x-2">
            <div class="relative w-full md:w-56">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar comprobante..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <?php if ($search !== '' || $tipo_filtro !== ''): ?>
                <a href="index.php" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times-circle"></i>
                </a>
                <?php endif; ?>
            </div>
            <select name="tipo_filtro" onchange="this.form.submit()" class="block w-full md:w-48 pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm shadow-sm">
                <option value="">Todos los Tipos</option>
                <option value="FACTURA" <?= $tipo_filtro==='FACTURA'?'selected':'' ?>>Facturas</option>
                <option value="BOLETA" <?= $tipo_filtro==='BOLETA'?'selected':'' ?>>Boletas</option>
                <option value="NOTA_CREDITO" <?= $tipo_filtro==='NOTA_CREDITO'?'selected':'' ?>>Notas de Crédito</option>
                <option value="NOTA_DEBITO" <?= $tipo_filtro==='NOTA_DEBITO'?'selected':'' ?>>Notas de Débito</option>
            </select>
            <noscript><button type="submit" class="hidden">Ir</button></noscript>
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
                                    
                                    <button @click="abrirDetalles(<?= $c['id'] ?>)" class="text-teal-600 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition-colors" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
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
                    if ($search !== '') $qs .= '&search=' . urlencode($search);
                    if ($tipo_filtro !== '') $qs .= '&tipo_filtro=' . urlencode($tipo_filtro);
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

<!-- Modal Detalles -->
<div x-data="{ 
        modalOpen: false, 
        loading: false, 
        data: null,
        formatMoney(amount, cur) {
            if (!amount && amount !== 0) return 'S/ 0.00';
            let sy = cur === 'PEN' ? 'S/' : '$';
            return sy + ' ' + parseFloat(amount).toFixed(2);
        },
        cargarDetalles(id) {
            this.loading = true;
            this.data = null;
            fetch(`api_detalles.php?id=${id}`)
                .then(res => res.json())
                .then(d => {
                    if(d.success) {
                        this.data = d;
                    } else {
                        alert('Error cargando detalles: ' + d.error);
                    }
                })
                .catch(e => {
                    console.error(e);
                    alert('Error de red al cargar detalles.');
                })
                .finally(() => {
                    this.loading = false;
                });
        }
     }" 
     @abrir-detalle.window="modalOpen = true; cargarDetalles($event.detail);"
     x-show="modalOpen" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-details" 
     role="dialog" 
     aria-modal="true" 
     style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="modalOpen = false" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="modalOpen" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-5 pb-6">
                <!-- Header Modal -->
                <div class="flex justify-between items-start mb-5 pb-3 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-blue-50 p-3 rounded-lg mr-4">
                            <i class="fas fa-file-invoice-dollar text-xl text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 leading-tight">
                                <span x-text="data ? data.cabecera.tipo_comprobante : 'Cargando...'"></span>
                                <span x-show="data" class="text-blue-600 font-black ml-1" x-text="data ? `${data.cabecera.serie}-${data.cabecera.correlativo.toString().padStart(8,'0')}` : ''"></span>
                            </h3>
                            <p class="text-sm text-gray-500 font-medium" x-show="data" x-text="data ? data.cabecera.fecha_emision : ''"></p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 p-2 rounded-lg transition-colors focus:outline-none">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Skeleton Loader -->
                <div x-show="loading" class="animate-pulse space-y-4 py-4">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-32 bg-gray-100 rounded-xl mt-4"></div>
                </div>

                <!-- Content -->
                <div x-show="!loading && data" class="space-y-6">
                    
                    <!-- Top metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Cliente</span>
                            <span class="block text-sm font-semibold text-gray-900 truncate" x-text="data?.cabecera.cliente_razon_social"></span>
                            <span class="block text-xs text-gray-500 mt-0.5" x-text="data?.cabecera.cliente_numero_documento"></span>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Condición</span>
                            <span class="block text-sm font-semibold text-gray-900" x-text="data?.cabecera.condicion_pago"></span>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Inicial</span>
                            <span class="block text-lg font-black text-gray-900" x-text="data ? formatMoney(data.cabecera.total, data.cabecera.moneda) : ''"></span>
                        </div>
                        <!-- Saldo Actual (solo si es Factura/Boleta) -->
                        <div x-show="data && ['FACTURA', 'BOLETA'].includes(data.cabecera.tipo_comprobante)" class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                            <span class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Saldo Actualizado</span>
                            <span class="block text-2xl font-black text-blue-700 leading-none" x-text="data?.saldo !== undefined ? formatMoney(data.saldo, data.cabecera.moneda) : ''"></span>
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Detalle de Ítems</h4>
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left font-semibold text-gray-700">Cant.</th>
                                        <th class="px-4 py-2.5 text-left font-semibold text-gray-700">Descripción</th>
                                        <th class="px-4 py-2.5 text-right font-semibold text-gray-700">P. Unit.</th>
                                        <th class="px-4 py-2.5 text-right font-semibold text-gray-700">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <template x-for="it in data?.items" :key="it.id">
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 text-gray-900 font-medium" x-text="parseFloat(it.cantidad).toFixed(2)"></td>
                                            <td class="px-4 py-3 text-gray-600" x-text="it.descripcion"></td>
                                            <td class="px-4 py-3 text-gray-600 text-right" x-text="it.precio_unitario"></td>
                                            <td class="px-4 py-3 text-gray-900 font-bold text-right py-2 bg-yellow-50/30" x-text="it.importe_total"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notas Aplicadas / Relacionadas -->
                    <div x-show="data && data.notas && data.notas.length > 0" class="mt-8">
                        <h4 class="text-xs font-bold text-purple-600 uppercase tracking-wider mb-3 flex items-center">
                            <i class="fas fa-link mr-2"></i> Notas Relacionadas Emitidas
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="n in data?.notas" :key="n.id">
                                <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 bg-white hover:border-purple-300 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-2 h-full rounded-full mr-3" :class="n.tipo_comprobante === 'NOTA_CREDITO' ? 'bg-red-400' : 'bg-green-400'">&nbsp;</div>
                                        <div>
                                            <span class="block text-xs font-bold text-gray-500" x-text="n.tipo_comprobante"></span>
                                            <span class="block text-sm font-black text-gray-900" x-text="`${n.serie}-${n.correlativo.toString().padStart(8,'0')}`"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-xs text-gray-500 font-medium" x-text="n.estado_sunat"></span>
                                        <span class="block text-sm font-bold" :class="n.tipo_comprobante === 'NOTA_CREDITO' ? 'text-red-600' : 'text-green-600'" x-text="(n.tipo_comprobante === 'NOTA_CREDITO' ? '- ' : '+ ') + formatMoney(n.total, n.moneda)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Documento Padre (Si esto es una nota) -->
                    <div x-show="data && data.padre" class="mt-8 bg-purple-50 rounded-xl p-4 border border-purple-200 flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold text-purple-600 uppercase tracking-wider mb-1"><i class="fas fa-level-up-alt mr-1"></i> Documento Afectado (Padre)</span>
                            <span class="block text-lg font-black text-gray-900" x-text="data?.padre ? `${data.padre.tipo_comprobante} ${data.padre.serie}-${data.padre.correlativo.toString().padStart(8,'0')}` : ''"></span>
                        </div>
                        <div class="text-right">
                            <span class="block text-2xl font-black text-purple-700" x-text="data?.padre ? formatMoney(data.padre.total, data.padre.moneda) : ''"></span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end rounded-b-2xl border-t border-gray-100">
                <button type="button" @click="modalOpen = false" class="px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none">
                    Cerrar Detalle
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('comprobantesList', () => ({
        abrirDetalles(id) {
            this.$dispatch('abrir-detalle', id);
        },
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
