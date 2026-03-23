<?php
session_start();
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';
require_permission('facturacion_tipo_cambio', 'ver'); 

$base_url = '../../../';
require_once '../../../includes/header.php';
?>

<div class="max-w-7xl mx-auto" x-data="tipoCambioManager()">
    <!-- Header & Actions -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Registro de Tipos de Cambio</h2>
            <p class="text-sm font-medium text-gray-500 mt-1">Administra el histórico de TC (Dólares) para la emisión automática.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <!-- Buscador -->
            <div class="relative w-full sm:w-64">
                <input type="text" x-model.debounce.500ms="search" @input="page = 1; loadItems()" placeholder="Buscar por fecha..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>

            <?php if (has_permission('facturacion_tipo_cambio', 'crear')): ?>
            <button @click="openModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors w-full sm:w-auto justify-center">
                <i class="fas fa-plus mr-2"></i> Nuevo TC
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div id="tc-toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha Calendario</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Compra (S/)</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Venta (S/)</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="item in items" :key="item.fecha">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900" x-text="formatDate(item.fecha)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200" x-text="item.compra"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-50 text-green-700 border border-green-200" x-text="item.venta"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <?php if (has_permission('facturacion_tipo_cambio', 'editar') || has_permission('facturacion_tipo_cambio', 'crear')): ?>
                                    <button @click="openModal(item)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors" title="Editar este día">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (has_permission('facturacion_tipo_cambio', 'eliminar')): ?>
                                    <button @click="deleteItem(item.fecha)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors" title="Eliminar este día">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0 && !loading">
                        <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                            No hay tipos de cambio registrados en el histórico. Registre el primero para empezar a operar en Dólares.
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- loading skel -->
            <div x-show="loading" class="p-6 flex justify-center items-center text-gray-400">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>
        
        <!-- Paginación -->
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6" x-show="totalPages > 1 && !loading">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="prevPage()" :disabled="page === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Anterior</button>
                <button @click="nextPage()" :disabled="page === totalPages" class="relative inline-flex items-center ml-3 px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Siguiente</button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando página <span class="font-medium" x-text="page"></span> de <span class="font-medium" x-text="totalPages"></span>
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button @click="prevPage()" :disabled="page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            <span class="sr-only">Anterior</span>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        
                        <template x-for="p in Array.from({length: totalPages}, (_, i) => i + 1)" :key="p">
                            <!-- Show limited pages heuristic or all if < 10 -->
                            <button x-show="totalPages < 10 || Math.abs(p - page) < 3 || p === 1 || p === totalPages" 
                                    @click="gotoPage(p)" 
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium" 
                                    :class="p === page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" 
                                    x-text="p"></button>
                        </template>

                        <button @click="nextPage()" :disabled="page === totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            <span class="sr-only">Siguiente</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="modalOpen" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-6 pt-5 pb-6">
                    <div class="flex justify-between items-center mb-5 border-b pb-2">
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Tipo de Cambio</h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <form @submit.prevent="saveItem" class="space-y-4" novalidate>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Cotización <span class="text-red-500">*</span></label>
                                <input type="date" x-model="form.fecha" required :readonly="isEditing" class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" :class="isEditing ? 'bg-gray-100 text-gray-500' : ''">
                                <p class="text-xs text-gray-500 mt-1" x-show="isEditing">En caso edite un TC del pasado, afectará la impresión de futuras facturas asimiladas a esta fecha.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <label class="block text-sm font-bold text-blue-700 mb-1">Precio Compra <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.001" min="1" x-model="form.compra" required class="w-full rounded-lg border-blue-200 bg-blue-50 px-3 py-2 font-mono text-xl text-blue-900 focus:ring-blue-500 focus:border-blue-500 placeholder-blue-300" placeholder="0.000">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-green-700 mb-1">Precio Venta (SUNAT) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.001" min="1" x-model="form.venta" required class="w-full rounded-lg border-green-200 bg-green-50 px-3 py-2 font-mono text-xl text-green-900 focus:ring-green-500 focus:border-green-500 placeholder-green-300" placeholder="0.000">
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 sm:flex sm:flex-row-reverse border-t pt-4 border-gray-100">
                            <button type="submit" :disabled="isSaving" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <i class="fas mr-2" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                                <span x-text="isSaving ? 'Guardando...' : 'Guardar'"></span>
                            </button>
                            <button type="button" @click="modalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tipoCambioManager', () => ({
        items: [],
        loading: false,
        modalOpen: false,
        isSaving: false,
        isEditing: false,
        search: '',
        page: 1,
        totalPages: 1,
        form: {
            fecha: '<?= date('Y-m-d') ?>',
            compra: '',
            venta: ''
        },

        init() {
            this.loadItems();
        },

        formatDate(d) {
            // Reversa simple para visual YYYY-MM-DD -> DD/MM/YYYY
            const parts = d.split('-');
            if(parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
            return d;
        },

        async loadItems() {
            this.loading = true;
            try {
                const query = new URLSearchParams({ search: this.search, page: this.page }).toString();
                const res = await fetch(`api.php?action=list&${query}`);
                const data = await res.json();
                this.items = data.data || [];
                this.totalPages = data.total_pages || 1;
                this.page = data.current_page || 1;
            } catch (e) {
                this.showToast('Error cargando servidor', 'error');
            } finally {
                this.loading = false;
            }
        },

        prevPage() {
            if (this.page > 1) {
                this.page--;
                this.loadItems();
            }
        },

        nextPage() {
            if (this.page < this.totalPages) {
                this.page++;
                this.loadItems();
            }
        },

        gotoPage(p) {
            this.page = p;
            this.loadItems();
        },

        openModal(item = null) {
            if (item) {
                this.isEditing = true;
                this.form.fecha = item.fecha;
                this.form.compra = parseFloat(item.compra).toFixed(3);
                this.form.venta = parseFloat(item.venta).toFixed(3);
            } else {
                this.isEditing = false;
                this.form.fecha = '<?= date('Y-m-d') ?>';
                this.form.compra = '';
                this.form.venta = '';
            }
            this.modalOpen = true;
        },

        async saveItem() {
            if(!this.form.fecha || !this.form.compra || !this.form.venta) {
                this.showToast('Complete todos los campos', 'error');
                return;
            }
            this.isSaving = true;
            try {
                const res = await fetch('api.php?action=save', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if(data.success) {
                    this.showToast('Tipo de Cambio registrado!', 'success');
                    this.modalOpen = false;
                    this.loadItems();
                } else {
                    this.showToast(data.error || 'Error BD', 'error');
                }
            } catch (e) {
                this.showToast('Error en red', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        async deleteItem(fecha) {
            if(!confirm(`¿Estás seguro de eliminar el Tipo de Cambio registrado el ${fecha}?`)) return;
            try {
                const res = await fetch(`api.php?action=delete&fecha=${fecha}`);
                const data = await res.json();
                if(data.success) {
                    this.showToast('Eliminado', 'success');
                    this.loadItems();
                } else {
                    this.showToast(data.error, 'error');
                }
            } catch(e) {
                this.showToast('Error en red', 'error');
            }
        },

        showToast(message, type = 'success') {
            const container = document.getElementById('tc-toast-container');
            const toast = document.createElement('div');
            
            let bgClass = type === 'error' ? 'bg-red-600' : 'bg-green-600';
            
            toast.className = `${bgClass} text-white px-6 py-3 rounded-lg shadow-xl transition-all duration-300 opacity-0 transform translate-y-4 font-medium text-sm border flex items-center`;
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-times-circle'} mr-2 text-lg"></i> ${message}`;
            
            container.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('opacity-0', 'translate-y-4'), 10);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }));
});
</script>

<?php require_once '../../../includes/footer.php'; ?>
