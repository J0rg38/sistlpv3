<?php
$base_url = '';
require_once 'includes/header.php';
?>

<!-- Import Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div x-data="dashboardManager()" x-init="init()" class="pb-10">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Panel de Facturación</h2>
            <p class="text-sm font-medium text-gray-500 mt-1">Visión general y métricas de emisión electrónica.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <select x-model="filtros.rango" @change="loadData()" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm bg-white">
                <option value="hoy">Hoy</option>
                <option value="semana">Últimos 7 días</option>
                <option value="mes">Este Mes</option>
                <option value="mes_anterior">Mes Anterior</option>
                <option value="anio">Este Año</option>
            </select>
            
            <a href="modulos/facturacion/emision/form.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors whitespace-nowrap">
                <i class="fas fa-file-invoice mr-2 -ml-1"></i> Nueva Emisión
            </a>
        </div>
    </div>

    <!-- Spinner Loading -->
    <div x-show="loading" class="flex justify-center items-center py-20">
        <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
    </div>

    <div x-show="!loading" x-cloak>
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-200/60 p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center opacity-50"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="truncate">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Ventas Netas (PEN)</div>
                        <div class="text-3xl font-extrabold text-gray-900" x-text="'S/ ' + formatNumber(kpi.ventas_pen)"></div>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm relative z-10">
                    <span class="text-emerald-600 flex items-center font-bold bg-emerald-50 px-2 py-0.5 rounded text-xs px-2">
                        Liquidado Aceptado
                    </span>
                </div>
            </div>

            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-200/60 p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center opacity-50"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="truncate">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Ventas Netas (USD)</div>
                        <div class="text-3xl font-extrabold text-gray-900" x-text="'$ ' + formatNumber(kpi.ventas_usd)"></div>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm relative z-10">
                    <span class="text-blue-600 flex items-center font-bold bg-blue-50 px-2 py-0.5 rounded text-xs px-2">
                        Dólares Estadounidenses
                    </span>
                </div>
            </div>

            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-200/60 p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center opacity-50"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="truncate">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Comprobantes</div>
                        <div class="text-3xl font-extrabold text-gray-900" x-text="kpi.volumen_total"></div>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm relative z-10">
                    <span class="text-purple-600 flex items-center font-bold bg-purple-50 px-2 py-0.5 rounded text-xs px-2">
                        Volumen Autorizado
                    </span>
                </div>
            </div>

            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-200/60 p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center opacity-50"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="truncate">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Ticket Promedio (PEN)</div>
                        <div class="text-3xl font-extrabold text-gray-900" x-text="'S/ ' + formatNumber(kpi.ticket_promedio_pen)"></div>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm relative z-10">
                    <span class="text-orange-600 flex items-center font-bold bg-orange-50 px-2 py-0.5 rounded text-xs px-2">
                        Gasto Promedio por Doc
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Chart Lineas -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Evolución de Ventas Diarias</h3>
                <div class="h-72 w-full relative">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Chart Dona -->
            <div class="lg:col-span-1 bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Composición de Emisiones</h3>
                <div class="h-64 w-full relative flex justify-center">
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="mt-4 text-center text-sm text-gray-500 font-medium">
                    Comparación volumétrica de Facturas vs Boletas.
                </div>
            </div>
        </div>

        <!-- Tabla Actividad Reciente -->
        <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-900">Actividad Reciente (Últimos 10)</h3>
                <a href="modulos/facturacion/emision/index.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    Ver Todos &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Comprobante</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Importe</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">SUNAT</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="r in recientes" :key="r.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-1.5 h-8 mr-3 rounded-full" 
                                             :class="r.tipo_comprobante === 'FACTURA' ? 'bg-blue-500' : (r.tipo_comprobante === 'BOLETA' ? 'bg-emerald-500' : 'bg-purple-500')"></div>
                                        <div>
                                            <span class="text-xs font-bold text-gray-500 block" x-text="r.tipo_comprobante"></span>
                                            <span class="text-sm font-black text-gray-900" x-text="r.serie + '-' + String(r.correlativo).padStart(8, '0')"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 truncate max-w-[200px]">
                                    <span class="text-sm font-semibold text-gray-900 block truncate" x-text="r.tipo_cliente === 'EMPRESA' ? r.razon_social : r.nombres + ' ' + r.apellidos"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium" x-text="r.fecha_emision"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-black" :class="r.tipo_comprobante.includes('CREDITO') ? 'text-red-600' : 'text-gray-900'" x-text="(r.tipo_comprobante.includes('CREDITO') ? '- ' : '') + formatMoney(r.total, r.moneda)"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-green-100 text-green-800': r.estado_sunat === 'ACEPTADO',
                                            'bg-gray-100 text-gray-800': r.estado_sunat === 'PENDIENTE',
                                            'bg-red-100 text-red-800': ['RECHAZADO','ANULADO'].includes(r.estado_sunat)
                                        }">
                                        <i class="fas mr-1.5" :class="{
                                            'fa-check': r.estado_sunat === 'ACEPTADO',
                                            'fa-clock': r.estado_sunat === 'PENDIENTE',
                                            'fa-times': ['RECHAZADO','ANULADO'].includes(r.estado_sunat)
                                        }"></i>
                                        <span x-text="r.estado_sunat"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="recientes.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No hay movimientos recientes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let lineChartInstance = null;
let pieChartInstance = null;

function dashboardManager() {
    return {
        filtros: { rango: 'mes' },
        loading: true,
        kpi: { ventas_pen: 0, ventas_usd: 0, volumen_total: 0, ticket_promedio_pen: 0 },
        recientes: [],
        
        formatNumber(num) {
            return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        formatMoney(amount, currency) {
            let sy = currency === 'PEN' ? 'S/' : '$';
            return sy + ' ' + this.formatNumber(amount);
        },

        async loadData() {
            this.loading = true;
            try {
                const res = await fetch(`api_dashboard.php?rango=${this.filtros.rango}`);
                const data = await res.json();
                
                if (data.success) {
                    this.kpi = data.kpi;
                    this.recientes = data.recientes;
                    this.renderCharts(data.grafico_lineas, data.grafico_pie);
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        renderCharts(lineData, pieData) {
            Chart.defaults.font.family = "'Inter', sans-serif";
            
            // Render Line Chart
            const ctxLine = document.getElementById('lineChart');
            if (ctxLine) {
                if (lineChartInstance) lineChartInstance.destroy();
                lineChartInstance = new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: lineData.fechas,
                        datasets: [
                            {
                                label: 'Ingresos Soles (PEN)',
                                data: lineData.series_pen,
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Ingresos Dólares (USD)',
                                data: lineData.series_usd,
                                borderColor: '#8B5CF6',
                                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'top' } },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f3f4f6' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Render Pie/Doughnut Chart
            const ctxPie = document.getElementById('pieChart');
            if (ctxPie) {
                if (pieChartInstance) pieChartInstance.destroy();
                let hasData = pieData.facturas > 0 || pieData.boletas > 0;
                
                pieChartInstance = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: ['Facturas', 'Boletas'],
                        datasets: [{
                            data: hasData ? [pieData.facturas, pieData.boletas] : [1],
                            backgroundColor: hasData ? ['#3B82F6', '#10B981'] : ['#E5E7EB'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { position: 'bottom', display: hasData },
                            tooltip: { enabled: hasData }
                        }
                    }
                });
            }
        },

        init() {
            this.loadData();
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
