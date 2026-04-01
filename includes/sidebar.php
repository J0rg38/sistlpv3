<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page, $dir, $target_page, $target_dir = '')
{
    if ($target_dir)
        return ($dir === $target_dir);
    return ($page === $target_page && $dir !== 'usuarios' && $dir !== 'roles' && $dir !== 'modulos');
}
$base = isset($base_url) ? $base_url : '';
?>
<!-- Sidebar Desktop -->
<aside class="hidden md:flex flex-col w-64 bg-white shadow-xl border-r border-gray-100 z-20">
    <div class="h-16 flex items-center justify-center border-b border-gray-100 px-6 shrink-0">
        <img src="<?= $base?>data/img/logo_factura.png" alt="Logo" class="h-15 mr-3">
        <!-- cambiar el logo en caso sea necesario -->
    </div>

    <div class="flex-1 overflow-y-auto py-6">
        <div class="px-4 mb-3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menú Principal</p>
        </div>
        <nav class="px-3 space-y-1">
            <a href="<?= $base?>dashboard.php"
                class="<?= isActive($current_page, $current_dir, 'dashboard.php') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-home w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'dashboard.php') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Inicio
            </a>

            <?php if (has_permission('usuarios', 'ver')): ?>
            <a href="<?= $base?>modulos/usuarios/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'usuarios') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-users w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'usuarios') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Usuarios
            </a>
            <?php
endif; ?>

            <?php if (has_permission('clientes', 'ver')): ?>
            <a href="<?= $base?>modulos/clientes/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'clientes') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-user-tie w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'clientes') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Clientes
            </a>
            <?php
endif; ?>

            <?php if (has_permission('roles', 'ver')): ?>
            <a href="<?= $base?>modulos/roles/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'roles') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-shield-alt w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'roles') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Roles y Permisos
            </a>
            <?php
endif; ?>

            <div class="px-4 mt-6 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Facturación</p>
            </div>
            <?php if (has_permission('facturacion_emision', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/emision/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'emision') || isActive($current_page, $current_dir, 'form.php', 'emision') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-file-invoice-dollar w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'emision') || isActive($current_page, $current_dir, 'form.php', 'emision') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Comprobantes
            </a>
            <?php
endif; ?>
            <?php if (has_permission('facturacion_configuracion', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/configuracion/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'configuracion') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-cogs w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'configuracion') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Configuración
            </a>
            <?php
endif; ?>
            <?php if (has_permission('facturacion_tipo_cambio', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/tipo_cambio/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'tipo_cambio') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-exchange-alt w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'tipo_cambio') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Tipos de Cambio
            </a>
            <?php
endif; ?>
            <?php if (has_permission('facturacion_series', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/series/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'series') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-list-ol w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'series') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Series
            </a>
            <?php
endif; ?>

            <div class="px-4 mt-6 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Reportes Contables</p>
            </div>
            <!-- Idealmente crear sub-permisos como reportes_ventas -->
            <?php if (has_permission('reportes_contabilidad', 'ver')): ?>
            <a href="<?= $base?>modulos/reportes/ventas/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'ventas') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium'?> flex items-center px-4 py-3 rounded-xl transition-all shadow-sm group">
                <i
                    class="fas fa-chart-bar w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'ventas') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500'?> flex items-center justify-center transition-colors"></i>
                Registro de Ventas
            </a>
            <?php
endif; ?>

        </nav>
    </div>

    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
        <div class="flex items-center">
            <div
                class="h-10 w-10 shrink-0 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                <?= $iniciales?>
            </div>
            <div class="ml-3 truncate">
                <p class="text-sm font-bold text-gray-900 truncate">
                    <?= $nombreCompleto?>
                </p>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= $rol?>
                </p>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar Mobile -->
<aside x-show="sidebarOpen"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-2xl transform transition-transform duration-300 md:hidden flex flex-col"
    x-transition:enter="duration-300 ease-out" x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0" x-transition:leave="duration-200 ease-in"
    x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
    <div class="h-16 flex items-center justify-between border-b border-gray-100 px-6 shrink-0 bg-white">
        <span class="text-xl font-black text-gray-900">SistLP <span class="text-blue-600">v3</span></span>
        <button @click="sidebarOpen = false"
            class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md bg-gray-50">
            <i class="fas fa-times text-lg w-6 h-6 flex items-center justify-center"></i>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="px-3 py-2 space-y-1">
            <a href="<?= $base?>dashboard.php"
                class="<?= isActive($current_page, $current_dir, 'dashboard.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-bold">
                <i
                    class="fas fa-home w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'dashboard.php') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Inicio
            </a>

            <?php if (has_permission('usuarios', 'ver')): ?>
            <a href="<?= $base?>modulos/usuarios/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'usuarios') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-users w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'usuarios') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Usuarios
            </a>
            <?php
endif; ?>

            <?php if (has_permission('clientes', 'ver')): ?>
            <a href="<?= $base?>modulos/clientes/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'clientes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-user-tie w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'clientes') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Clientes
            </a>
            <?php
endif; ?>

            <?php if (has_permission('roles', 'ver')): ?>
            <a href="<?= $base?>modulos/roles/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'roles') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-shield-alt w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'roles') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Roles
            </a>
            <?php
endif; ?>

            <div class="px-4 mt-4 mb-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Facturación</p>
            </div>
            <?php if (has_permission('facturacion_emision', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/emision/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'emision') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-file-invoice-dollar w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'emision') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Comprobantes
            </a>
            <?php
endif; ?>
            <?php if (has_permission('facturacion_configuracion', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/configuracion/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'configuracion') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-cogs w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'configuracion') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Configuración
            </a>
            <?php
endif; ?>
            <?php if (has_permission('facturacion_tipo_cambio', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/tipo_cambio/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'tipo_cambio') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-exchange-alt w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'tipo_cambio') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Tipos de Cambio
            </a>
            <?php
endif; ?>
            <?php if (has_permission('facturacion_series', 'ver')): ?>
            <a href="<?= $base?>modulos/facturacion/series/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'series') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-list-ol w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'series') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Series
            </a>
            <?php
endif; ?>
            <div class="px-4 mt-6 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Reportes Contables</p>
            </div>
            <?php if (has_permission('reportes_contabilidad', 'ver')): ?>
            <a href="<?= $base?>modulos/reportes/ventas/index.php"
                class="<?= isActive($current_page, $current_dir, 'index.php', 'ventas') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'?> flex items-center px-4 py-3 rounded-xl font-medium">
                <i
                    class="fas fa-chart-bar w-5 h-5 mr-3 <?= isActive($current_page, $current_dir, 'index.php', 'ventas') ? 'text-blue-600' : 'text-gray-400'?> flex items-center justify-center"></i>
                Registro de Ventas
            </a>
            <?php
endif; ?>
        </nav>
    </div>
    <div class="p-4 border-t border-gray-100">
        <a href="<?= $base?>logout.php"
            class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
        </a>
    </div>
</aside>