<?php
$base_url = '';
require_once 'includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Inicio del Panel</h2>
        <p class="text-sm font-medium text-gray-500 mt-1">Visión general y estadísticas de hoy.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-plus mr-2 -ml-1"></i> Nueva Acción
        </button>
    </div>
</div>

<!-- KPI Cards (Modern) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-200/60 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div class="truncate">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Ingresos Hoy</div>
                <div class="text-3xl font-extrabold text-gray-900">$24,560</div>
            </div>
            <div class="rounded-xl p-3 bg-blue-50 text-blue-600 shrink-0">
                <i class="fas fa-wallet text-xl w-6 h-6 flex justify-center items-center"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-emerald-600 flex items-center font-bold bg-emerald-50 px-2 py-0.5 rounded text-xs">
                <i class="fas fa-arrow-up mr-1 text-[10px]"></i> 12.5%
            </span>
            <span class="text-gray-400 ml-2 font-medium text-xs">vs ayer</span>
        </div>
    </div>
    <!-- Add other KPIs here as needed -->
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
            <h3 class="text-base font-bold text-gray-900">Resumen de Ventas</h3>
        </div>
        <div class="p-6 h-80 flex flex-col items-center justify-center bg-gray-50/50 border-2 border-dashed border-gray-200 m-6 rounded-xl">
            <div class="text-center p-4">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-500 mb-4">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-1">Área para Gráficos</h4>
                <p class="text-sm font-medium text-gray-500">Puedes integrar Chart.js aquí.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
