<?php
$base = isset($base_url) ? $base_url : '';
?>
<!-- Topbar -->
<header class="h-16 shrink-0 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-40 relative w-full">
    <!-- Mobile menu button -->
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none md:hidden p-2 -ml-2 rounded-md hover:bg-gray-100 transition-colors">
        <i class="fas fa-bars text-xl"></i>
    </button>
    
    <!-- Global Search -->
    <div class="flex-1 hidden md:flex" x-data="globalSearch()">
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" x-model="searchQuery" placeholder="Buscar módulos rápidamente..." class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors sm:text-sm">
            
            <!-- Search Results Dropdown -->
            <div x-show="searchQuery.length > 0" x-cloak class="absolute left-0 mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden" @click.away="searchQuery = ''">
                <template x-if="filteredModules().length > 0">
                    <ul class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                        <template x-for="mod in filteredModules()" :key="mod.url">
                            <li>
                                <a :href="mod.url" class="group flex items-center px-4 py-3 hover:bg-blue-50 transition-colors">
                                    <div class="mr-3 flex-shrink-0 w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                        <i :class="'fas ' + mod.icon"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-gray-900" x-text="mod.name"></div>
                                        <div class="text-xs text-gray-500" x-text="mod.caption"></div>
                                    </div>
                                    <div class="ml-3 flex-shrink-0 w-5 h-5 flex items-center justify-center text-gray-400 group-hover:text-blue-600">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </div>
                                </a>
                            </li>
                        </template>
                    </ul>
                </template>
                <template x-if="filteredModules().length === 0">
                    <div class="px-4 py-6 text-center text-sm font-medium text-gray-500">
                        No se encontraron resultados.
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="flex items-center space-x-3 sm:space-x-5">
        <!-- Notifications -->
        <button class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none rounded-full hover:bg-gray-100 transition-colors">
            <span class="sr-only">Ver notificaciones</span>
            <i class="far fa-bell text-xl"></i>
            <span class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>

        <!-- Profile Dropdown -->
        <div x-data="{ dropdownOpen: false }" class="relative shrink-0">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center focus:outline-none border-2 border-transparent rounded-full focus:border-gray-200 transition-colors">
                <img class="h-9 w-9 rounded-full object-cover border border-gray-200 shadow-sm" src="https://ui-avatars.com/api/?name=<?= urlencode($nombreCompleto) ?>&background=4F46E5&color=fff&rounded=true&bold=true" alt="Avatar">
                <i class="fas fa-chevron-down ml-2 text-[10px] text-gray-400 hidden sm:block"></i>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900 truncate"><?= $nombreCompleto ?></p>
                    <p class="text-xs font-medium text-gray-500 truncate mt-0.5"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                </div>
                
                <div class="py-1 border-t border-gray-100">
                    <a href="<?= $base ?>logout.php" class="group flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                        <i class="fas fa-sign-out-alt mr-3 text-red-400 group-hover:text-red-500"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Alert Toasts for Access Violations / Errors -->
<?php if (isset($_SESSION['error'])): ?>
<div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.500ms class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white shadow-xl rounded-xl border-l-4 border-red-500 flex items-start p-4 cursor-pointer" @click="show = false">
    <div class="flex-shrink-0 mt-0.5">
        <i class="fas fa-times-circle text-red-500 text-lg"></i>
    </div>
    <div class="ml-3 flex-1">
        <h3 class="text-sm font-bold text-gray-900">Acceso Denegado</h3>
        <p class="mt-1 text-sm font-medium text-gray-500"><?= htmlspecialchars($_SESSION['error']) ?></p>
    </div>
    <div class="ml-4 flex-shrink-0 flex">
        <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<?php unset($_SESSION['error']); endif; ?>

<script>
function globalSearch() {
    const baseUrl = '<?= $base ?>';
    // Generar array de módulos basado en permisos para evitar mostrar cosas prohibidas
    const rawModules = [
        { name: 'Inicio (Dashboard)', url: baseUrl + 'dashboard.php', icon: 'fa-home', caption: 'Panel principal' },
        <?php if (has_permission('usuarios', 'ver')): ?>
        { name: 'Usuarios', url: baseUrl + 'modulos/usuarios/index.php', icon: 'fa-users', caption: 'Gestión de personal' },
        <?php endif; ?>
        <?php if (has_permission('clientes', 'ver')): ?>
        { name: 'Clientes', url: baseUrl + 'modulos/clientes/index.php', icon: 'fa-user-tie', caption: 'Directorio comercial' },
        <?php endif; ?>
        <?php if (has_permission('roles', 'ver')): ?>
        { name: 'Roles y Permisos', url: baseUrl + 'modulos/roles/index.php', icon: 'fa-shield-alt', caption: 'Seguridad' },
        <?php endif; ?>
        <?php if (has_permission('facturacion_emision', 'ver')): ?>
        { name: 'Emitir Comprobante', url: baseUrl + 'modulos/facturacion/emision/form.php', icon: 'fa-plus-circle', caption: 'Nueva factura/boleta/nota' },
        { name: 'Comprobantes Emitidos', url: baseUrl + 'modulos/facturacion/emision/index.php', icon: 'fa-file-invoice-dollar', caption: 'Historial SUNAT' },
        <?php endif; ?>
        <?php if (has_permission('facturacion_configuracion', 'ver')): ?>
        { name: 'Configuración de Empresa', url: baseUrl + 'modulos/facturacion/configuracion/index.php', icon: 'fa-cogs', caption: 'Credenciales y Logo' },
        <?php endif; ?>
        <?php if (has_permission('facturacion_tipo_cambio', 'ver')): ?>
        { name: 'Tipos de Cambio', url: baseUrl + 'modulos/facturacion/tipo_cambio/index.php', icon: 'fa-exchange-alt', caption: 'Historial cotizaciones' },
        <?php endif; ?>
        <?php if (has_permission('facturacion_series', 'ver')): ?>
        { name: 'Series Documentales', url: baseUrl + 'modulos/facturacion/series/index.php', icon: 'fa-list-ol', caption: 'F-001, B-001 correlativos' },
        <?php endif; ?>
        <?php if (has_permission('reportes_contabilidad', 'ver')): ?>
        { name: 'Registro de Ventas', url: baseUrl + 'modulos/reportes/ventas/index.php', icon: 'fa-chart-bar', caption: 'Exportación PLE 14.1 / SIRE / Excel' },
        <?php endif; ?>
    ];

    return {
        searchQuery: '',
        modules: rawModules,
        filteredModules() {
            if (this.searchQuery.trim() === '') return [];
            const query = this.searchQuery.toLowerCase();
            return this.modules.filter(mod => 
                mod.name.toLowerCase().includes(query) || 
                mod.caption.toLowerCase().includes(query)
            );
        }
    }
}
</script>
