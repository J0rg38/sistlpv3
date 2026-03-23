<?php
$base_url = '../../../';
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';
require_permission('facturacion_emision', 'crear');
require_once '../../../includes/header.php';
?>
<div x-data="emisionForm()" x-init="initForm()" x-cloak>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Emitir Comprobante</h2>
            <p class="text-sm font-medium text-gray-500 mt-1">Ingresa los datos para la nueva venta electrónica.</p>
        </div>
        <a href="index.php" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
            <i class="fas fa-list mr-2"></i> Ver Emitidos
        </a>
    </div>

    <!-- Error/Success Alerts -->
    <div x-show="alert.show" :class="alert.type === 'error' ? 'bg-red-50 border-red-500 text-red-700' : 'bg-green-50 border-green-500 text-green-700'" class="border-l-4 p-4 mb-6 rounded shadow-sm flex items-start" style="display: none;">
        <i class="fas mt-0.5 mr-3" :class="alert.type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'"></i>
        <p class="text-sm font-medium" x-text="alert.message"></p>
        <button @click="alert.show = false" class="ml-auto focus:outline-none"><i class="fas fa-times"></i></button>
    </div>

    <form @submit.prevent="submitForm" novalidate>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Datos Principales -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. Documento -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">1. Datos del Comprobante</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                            <select x-model="c.tipo" @change="fetchSeries()" required class="block w-full rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seleccione...</option>
                                <option value="BOLETA">Boleta</option>
                                <option value="FACTURA">Factura</option>
                                <option value="NOTA_CREDITO">Nota de Crédito</option>
                                <option value="NOTA_DEBITO">Nota de Débito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Serie <span class="text-red-500">*</span></label>
                            <select x-model="c.serie_id" @change="updateCorrelativo()" required :disabled="series.length === 0" class="block w-full rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seleccione serie...</option>
                                <template x-for="s in series" :key="s.id">
                                    <option :value="s.id" x-text="s.serie"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Correlativo</label>
                            <input type="text" x-model="c.correlativo" readonly class="block w-full rounded-lg border-gray-300 bg-gray-200 px-3 py-2 text-sm text-gray-500 font-mono">
                        </div>
                    </div>
                    
                    <!-- Bloque para Nota de Crédito / Débito -->
                    <div x-show="isNota()" class="mt-4 pt-4 border-t space-y-4">
                        <div class="flex flex-wrap sm:flex-nowrap gap-4">
                            <div class="w-full sm:w-1/3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Serie Afectada <span class="text-red-500">*</span></label>
                                <input type="text" x-model="comprobanteRelacionado.serie" readonly class="block w-full rounded-lg border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 uppercase">
                            </div>
                            <div class="w-full sm:w-1/3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Correl. Afectado <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" x-model="comprobanteRelacionado.correlativo" @keydown.enter.prevent="buscarComprobanteRelacionado()" placeholder="Ej: 1" class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <button type="button" @click="buscarComprobanteRelacionado()" class="absolute inset-y-0 right-0 px-3 flex items-center text-blue-600 hover:text-blue-800 focus:outline-none">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="w-full sm:w-1/3 flex items-end">
                                <p class="text-xs text-gray-500 pb-2">Presiona Enter tras el correlativo para cargar datos.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Motivo de Emisión <span class="text-red-500">*</span></label>
                            <select x-model="c.codigo_motivo" @change="updateMotivoDesc()" class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="">Seleccione un motivo...</option>
                                <template x-if="c.tipo === 'NOTA_CREDITO'">
                                    <template x-for="m in motivos_nc" :key="m.codigo">
                                        <option :value="m.codigo" x-text="m.codigo + ' - ' + m.descripcion"></option>
                                    </template>
                                </template>
                                <template x-if="c.tipo === 'NOTA_DEBITO'">
                                    <template x-for="m in motivos_nd" :key="m.codigo">
                                        <option :value="m.codigo" x-text="m.codigo + ' - ' + m.descripcion"></option>
                                    </template>
                                </template>
                            </select>
                        </div>
                        <div x-show="c.tipo === 'NOTA_CREDITO' && c.comprobante_relacionado_id" class="p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                            <span class="text-sm font-bold text-blue-800"><i class="fas fa-info-circle mr-1"></i> Saldo Máximo de Emisión (NC):</span>
                            <span class="text-lg font-black text-blue-700" x-text="formatCurrency(c.saldo_permitido)"></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Cliente -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <h3 class="text-lg font-bold text-gray-800">2. Datos del Cliente</h3>
                        <button type="button" @click="openClienteModal()" class="text-sm text-blue-600 font-medium hover:text-blue-800 focus:outline-none" x-show="!isNota()">
                            <i class="fas fa-user-plus mr-1"></i> Nuevo Cliente
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Documento <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" x-model="cliente.documento" :readonly="isNota()" @keydown.enter.prevent="buscarCliente()" placeholder="DNI o RUC" required class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" :class="isNota() ? 'bg-gray-100' : ''">
                                <button type="button" @click="buscarCliente()" x-show="!isNota()" class="absolute inset-y-0 right-0 px-3 flex items-center text-blue-600 hover:text-blue-800">
                                    <i class="fas" :class="isSearchingClient ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Razón Social / Nombres</label>
                            <input type="text" x-model="cliente.nombre" readonly required placeholder="Ingrese documento y presione Enter" class="block w-full rounded-lg border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección</label>
                            <input type="text" x-model="cliente.direccion" readonly class="block w-full rounded-lg border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Condiciones -->
            <div class="space-y-6">
                <!-- 3. Condiciones -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm h-full">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Condiciones de Venta</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Emisión <span class="text-red-500">*</span></label>
                            <input type="date" x-model="c.fecha_emision" @change="calcularVencimiento(); fetchTipoCambio();" required class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex space-x-4">
                            <div class="w-1/2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Moneda <span class="text-red-500">*</span></label>
                                <select x-model="c.moneda" @change="fetchTipoCambio()" :disabled="isNota() && c.comprobante_relacionado_id" class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" :class="(isNota() && c.comprobante_relacionado_id) ? 'bg-gray-100' : ''">
                                    <option value="PEN">Soles (S/)</option>
                                    <option value="USD">Dólares ($)</option>
                                </select>
                            </div>
                            <div class="w-1/2" x-show="c.moneda === 'USD'">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">T. Cambio <span class="text-red-500">*</span></label>
                                <input type="number" step="0.001" x-model="c.tipo_cambio" class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div x-show="!isNota()">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Condición de Pago <span class="text-red-500">*</span></label>
                            <select x-model="c.condicion_pago" @change="calcularVencimiento()" class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="CONTADO">Contado</option>
                                <option value="CREDITO">Crédito</option>
                            </select>
                        </div>

                        <div x-show="c.condicion_pago === 'CREDITO' && !isNota()" class="flex space-x-4">
                            <div class="w-1/3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Días <span class="text-red-500">*</span></label>
                                <input type="number" x-model="c.dias_credito" @input="calcularVencimiento()" min="1" class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="w-2/3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Vencimiento</label>
                                <input type="date" x-model="c.fecha_vencimiento" readonly class="block w-full rounded-lg border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 4. Items -->
            <div class="lg:col-span-3 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-800">3. Detalles del Comprobante</h3>
                    <button type="button" @click="addItem()" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-sm font-bold transition-colors">
                        <i class="fas fa-plus mr-1"></i> Agregar Fila
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 bg-gray-50 uppercase font-bold">
                            <tr>
                                <th class="px-3 py-3 w-24">Código</th>
                                <th class="px-3 py-3 w-1/3">Descripción <span class="text-red-500">*</span></th>
                                <th class="px-3 py-3 w-20">U.M.</th>
                                <th class="px-3 py-3 w-24">Cant. <span class="text-red-500">*</span></th>
                                <th class="px-3 py-3 w-32">P. Unitario <span class="text-red-500">*</span></th>
                                <th class="px-3 py-3 w-28">Dscto.</th>
                                <th class="px-3 py-3 w-32">Total <span class="text-red-500">*</span></th>
                                <th class="px-3 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-b group hover:bg-gray-50">
                                    <td class="px-2 py-2">
                                        <input type="text" x-model="item.codigo" class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" x-model="item.descripcion" required class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                    <td class="px-2 py-2">
                                        <select x-model="item.um" class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="NIU">Unidad</option>
                                            <option value="KG">Kilo</option>
                                            <option value="LTR">Litro</option>
                                            <option value="ZZ">Servicio</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" step="0.01" min="0.01" x-model.number="item.cantidad" @input="calcTotal(item)" required class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 text-right">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" step="0.0001" min="0" x-model.number="item.precio" @input="calcTotal(item)" required class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 text-right">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" step="0.01" min="0" x-model.number="item.descuento" @input="calcTotal(item)" class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 text-right">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" step="0.01" min="0" x-model.number="item.total" @input="calcPrecio(item)" required class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 text-right font-bold bg-yellow-50">
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 focus:outline-none" title="Eliminar final">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="8" class="px-6 py-6 text-center text-gray-500 text-sm italic">
                                    No hay items. Haz clic en "Agregar Fila" para empezar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="flex justify-end mt-6">
                    <div class="w-full max-w-sm bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-600 font-medium">
                            <span>Subtotal:</span>
                            <span x-text="formatCurrency(totales.subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 font-medium">
                            <span>IGV (18%):</span>
                            <span x-text="formatCurrency(totales.igv)"></span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 flex justify-between text-lg text-gray-900 font-black">
                            <span>Total General:</span>
                            <span x-text="formatCurrency(totales.total)"></span>
                        </div>
                    </div>
                </div>

            </div>

        </div> <!-- end grid lg:grid-cols-3 -->

        <div class="mt-8 pt-5 border-t border-gray-200 flex items-center justify-end space-x-4">
            <a href="index.php" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Cancelar</a>
            <button type="submit" :disabled="isSaving || items.length === 0" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition-colors disabled:opacity-50 flex items-center">
                <i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                <span class="ml-2" x-text="isSaving ? 'Guardando...' : 'Emitir Comprobante'"></span>
            </button>
        </div>
    </form>

    <!-- Cliente Modal -->
    <div x-show="showClienteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showClienteModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showClienteModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showClienteModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <div class="bg-white px-6 pt-5 pb-6">
                    <div class="flex justify-between items-center mb-5 border-b pb-2">
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Registrar Nuevo Cliente</h3>
                        <button @click="showClienteModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <form @submit.prevent="saveNewCliente" class="space-y-4" novalidate>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo Cliente</label>
                                <select x-model="modalCliente.tipo_cliente" @change="modalCliente.tipo_documento = (modalCliente.tipo_cliente === 'EMPRESA' ? 'RUC' : 'DNI')" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="NATURAL">Natural</option>
                                    <option value="EMPRESA">Empresa</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo Doc.</label>
                                <select x-model="modalCliente.tipo_documento" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-100" tabindex="-1">
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                    <option value="CE">CE</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Número Documento <span class="text-red-500">*</span></label>
                                <input type="text" x-model="modalCliente.numero_documento" required class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Natural fields -->
                            <template x-if="modalCliente.tipo_cliente === 'NATURAL'">
                                <div class="col-span-2 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="modalCliente.nombres" required class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="modalCliente.apellidos" required class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </template>

                            <!-- Empresa fields -->
                            <template x-if="modalCliente.tipo_cliente === 'EMPRESA'">
                                <div class="col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Razón Social <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="modalCliente.razon_social" required class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </template>

                            <div class="col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección</label>
                                <input type="text" x-model="modalCliente.direccion" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse border-t pt-4">
                            <button type="submit" :disabled="isSavingCliente" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-text="isSavingCliente ? 'Guardando...' : 'Guardar Cliente'"></span>
                            </button>
                            <button type="button" @click="showClienteModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
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
function emisionForm() {
    return {
        c: {
            tipo: '',
            serie_id: '',
            correlativo: '',
            comprobante_relacionado_id: null,
            saldo_permitido: null,
            codigo_motivo: '',
            descripcion_motivo: '',
            fecha_emision: '<?= date('Y-m-d') ?>',
            fecha_vencimiento: '<?= date('Y-m-d') ?>',
            moneda: 'PEN',
            tipo_cambio: '',
            condicion_pago: 'CONTADO',
            dias_credito: 0
        },
        items: [
            { codigo: '', descripcion: '', um: 'NIU', cantidad: 1, precio: 0.00, descuento: 0.00, total: 0.00 }
        ],
        series: [],
        cliente: {
            id: '',
            documento: '',
            nombre: '',
            direccion: ''
        },
        totales: { subtotal: 0, igv: 0, total: 0 },
        alert: { show: false, type: '', message: '' },
        isSaving: false,
        isSearchingClient: false,
        showClienteModal: false,
        isSavingCliente: false,
        comprobanteRelacionado: {
            serie: '',
            correlativo: ''
        },
        motivos_nc: [
            {codigo: '01', descripcion: 'Anulación de la operación'},
            {codigo: '02', descripcion: 'Anulación por error en el RUC'},
            {codigo: '03', descripcion: 'Corrección por error en la descripción'},
            {codigo: '04', descripcion: 'Descuento global'},
            {codigo: '05', descripcion: 'Descuento por ítem'},
            {codigo: '06', descripcion: 'Devolución total'},
            {codigo: '07', descripcion: 'Devolución por ítem'},
            {codigo: '08', descripcion: 'Bono por desempeño'},
            {codigo: '09', descripcion: 'Disminución en el valor'},
            {codigo: '10', descripcion: 'Otros Conceptos'}
        ],
        motivos_nd: [
            {codigo: '01', descripcion: 'Intereses por mora'},
            {codigo: '02', descripcion: 'Aumento en el valor'},
            {codigo: '03', descripcion: 'Penalidades/otros conceptos'},
            {codigo: '11', descripcion: 'Ajustes de operaciones de exportación'},
            {codigo: '12', descripcion: 'Ajustes afectos al IVAP'}
        ],
        modalCliente: {
            tipo_cliente: 'NATURAL',
            tipo_documento: 'DNI',
            numero_documento: '',
            nombres: '',
            apellidos: '',
            razon_social: '',
            direccion: ''
        },

        initForm() {
            this.recalcularTotales();
        },

        isNota() {
            return ['NOTA_CREDITO', 'NOTA_DEBITO'].includes(this.c.tipo);
        },

        async fetchSeries() {
            this.c.serie_id = '';
            this.c.correlativo = '';
            this.c.comprobante_relacionado_id = null;
            this.series = [];
            if (!this.c.tipo) return;

            try {
                const res = await fetch(`api_series.php?tipo=${this.c.tipo}`);
                const data = await res.json();
                if (data.error) throw new Error(data.error);
                this.series = data;
                if (this.series.length > 0) {
                    this.c.serie_id = this.series[0].id;
                    this.updateCorrelativo();
                }
            } catch (err) {
                this.showAlert('error', 'Error al cargar series: ' + err.message);
            }
        },

        updateCorrelativo() {
            const s = this.series.find(x => x.id == this.c.serie_id);
            this.c.correlativo = s ? String(s.correlativo_actual).padStart(8, '0') : '';
            if (this.isNota() && s) {
                this.comprobanteRelacionado.serie = s.serie;
            }
        },

        updateMotivoDesc() {
            let cat = this.c.tipo === 'NOTA_CREDITO' ? this.motivos_nc : this.motivos_nd;
            let m = cat.find(x => x.codigo === this.c.codigo_motivo);
            this.c.descripcion_motivo = m ? m.descripcion : '';
        },

        async buscarCliente() {
            if (!this.cliente.documento) return;
            this.isSearchingClient = true;
            try {
                const res = await fetch(`../../clientes/api_buscar.php?doc=${this.cliente.documento}`);
                const data = await res.json();
                if (data.error) {
                    this.cliente.id = '';
                    this.cliente.nombre = '';
                    this.cliente.direccion = '';
                    this.showAlert('error', data.error);
                } else {
                    this.cliente.id = data.id;
                    this.cliente.nombre = data.tipo_cliente === 'EMPRESA' ? data.razon_social : `${data.nombres} ${data.apellidos}`;
                    this.cliente.direccion = data.direccion_completa || data.direccion || 'Sin dirección registrada';
                    this.showAlert('success', 'Cliente encontrado con éxito.');
                }
            } catch (err) {
                this.showAlert('error', 'Fallo al buscar cliente.');
            } finally {
                this.isSearchingClient = false;
            }
        },

        openClienteModal() {
            this.modalCliente.numero_documento = this.cliente.documento || '';
            this.showClienteModal = true;
        },

        async saveNewCliente() {
            this.isSavingCliente = true;
            try {
                const res = await fetch('../../clientes/api_save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.modalCliente)
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                // Set selected
                this.cliente.id = data.cliente.id;
                this.cliente.documento = data.cliente.numero_documento;
                this.cliente.nombre = data.cliente.tipo_cliente === 'EMPRESA' ? data.cliente.razon_social : `${data.cliente.nombres} ${data.cliente.apellidos}`;
                this.cliente.direccion = data.cliente.direccion_completa || data.cliente.direccion || 'Sin dirección registrada';
                
                this.showClienteModal = false;
                this.showAlert('success', 'Cliente registrado correctamente.');
            } catch (err) {
                alert('Error al guardar cliente: ' + err.message);
            } finally {
                this.isSavingCliente = false;
            }
        },

        async buscarComprobanteRelacionado() {
            if (!this.comprobanteRelacionado.serie || !this.comprobanteRelacionado.correlativo || !this.c.tipo) return;
            try {
                const res = await fetch(`api_buscar_comprobante.php?serie=${this.comprobanteRelacionado.serie.toUpperCase()}&correlativo=${this.comprobanteRelacionado.correlativo}&emitiendo=${this.c.tipo}`);
                const data = await res.json();
                if (data.error) {
                    this.showAlert('error', data.error);
                } else {
                    const comp = data.comprobante;
                    this.c.comprobante_relacionado_id = comp.id;
                    this.c.moneda = comp.moneda;
                    if(comp.moneda === 'USD') this.c.tipo_cambio = comp.tipo_cambio;
                    
                    this.cliente.id = comp.cliente_id;
                    this.cliente.documento = comp.numero_documento;
                    this.cliente.nombre = comp.nombre_cliente;
                    this.cliente.direccion = comp.direccion_cliente || 'Sin dirección registrada';
                    this.c.saldo_permitido = parseFloat(data.saldo || 0);

                    this.items = data.items.map(i => ({
                         codigo: i.codigo, descripcion: i.descripcion, um: i.unidad_medida, 
                         cantidad: parseFloat(i.cantidad), precio: parseFloat(i.precio_unitario), 
                         descuento: parseFloat(i.descuento), total: parseFloat(i.importe_total)
                    }));
                    this.recalcularTotales();
                    this.showAlert('success', 'Comprobante original (' + comp.tipo_comprobante + ') cargado con éxito. Cliente bloqueado.');
                }
            } catch (e) {
                this.showAlert('error', 'Error conectando con el servidor al buscar comprobante.');
            }
        },

        calcularVencimiento() {
            if (!this.c.fecha_emision) return;
            if (this.c.condicion_pago === 'CONTADO') {
                this.c.dias_credito = 0;
            }
            if (this.c.dias_credito < 0) this.c.dias_credito = 0;
            
            const date = new Date(this.c.fecha_emision + 'T12:00:00'); 
            date.setDate(date.getDate() + parseInt(this.c.dias_credito || 0));
            this.c.fecha_vencimiento = date.toISOString().split('T')[0];
        },

        async fetchTipoCambio() {
            if (this.c.moneda !== 'USD') {
                this.c.tipo_cambio = '';
                return;
            }
            if (!this.c.fecha_emision) return;
            try {
                const res = await fetch(`api_obtener_tc.php?fecha=${this.c.fecha_emision}`);
                const data = await res.json();
                if (data.success && data.tc) {
                    this.c.tipo_cambio = parseFloat(data.tc.venta).toFixed(3);
                    this.showAlert('success', 'TC auto-completado: ' + this.c.tipo_cambio);
                } else {
                    this.c.tipo_cambio = '';
                    this.showAlert('error', 'Atención: No hay Tipo de Cambio oficial registrado para la fecha ' + this.c.fecha_emision);
                }
            } catch (e) {
                console.error(e);
            }
        },

        addItem() {
            this.items.push({ codigo: '', descripcion: '', um: 'NIU', cantidad: 1, precio: 0.00, descuento: 0.00, total: 0.00 });
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
            this.recalcularTotales();
        },

        calcTotal(item) {
            const precio = parseFloat(item.precio || 0);
            const cant = parseFloat(item.cantidad || 0);
            const desc = parseFloat(item.descuento || 0);
            item.total = (precio * cant) - desc;
            if(item.total < 0) item.total = 0;
            this.recalcularTotales();
        },

        calcPrecio(item) {
            const total = parseFloat(item.total || 0);
            const cant = parseFloat(item.cantidad || 1);
            const desc = parseFloat(item.descuento || 0);
            if (cant > 0) {
                item.precio = (total + desc) / cant;
            }
            this.recalcularTotales();
        },

        recalcularTotales() {
            let sum = 0;
            this.items.forEach(i => sum += parseFloat(i.total || 0));
            this.totales.total = sum;
            // Considerando IGV incluido en el total:
            this.totales.subtotal = sum / 1.18;
            this.totales.igv = sum - this.totales.subtotal;
        },

        formatCurrency(val) {
            return new Intl.NumberFormat('es-PE', { style: 'currency', currency: this.c.moneda === 'USD' ? 'USD' : 'PEN' }).format(val);
        },

        showAlert(type, msg) {
            this.alert.type = type;
            this.alert.message = msg;
            this.alert.show = true;
            setTimeout(() => { this.alert.show = false; }, 5000);
        },

        async submitForm() {
            if (!this.cliente.id) {
                this.showAlert('error', 'Debe seleccionar o registrar un cliente.');
                return;
            }
            if (this.items.length === 0) {
                this.showAlert('error', 'Debe agregar al menos un ítem al comprobante.');
                return;
            }

            if (this.isNota() && !this.c.comprobante_relacionado_id) {
                this.showAlert('error', 'Para emitir Nota de Crédito/Débito debe buscar y cargar un comprobante original válido.');
                return;
            }

            if (this.isNota() && !this.c.codigo_motivo) {
                this.showAlert('error', 'Debe seleccionar el motivo (catálogo SUNAT) para emitir la nota.');
                return;
            }

            if (this.c.tipo === 'NOTA_CREDITO' && this.c.saldo_permitido !== null && parseFloat(this.totales.total) > parseFloat(this.c.saldo_permitido)) {
                this.showAlert('error', 'Montos excedidos. El saldo de esta factura solo es: ' + this.formatCurrency(this.c.saldo_permitido));
                return;
            }

            if (this.c.condicion_pago === 'CREDITO' && !this.isNota() && (!this.c.dias_credito || this.c.dias_credito < 1)) {
                this.showAlert('error', 'Debe ingresar una cantidad válida de días de crédito.');
                return;
            }

            if (this.c.moneda === 'USD' && (!this.c.tipo_cambio || this.c.tipo_cambio <= 0)) {
                this.showAlert('error', 'Debe ingresar un tipo de cambio válido para moneda en dólares.');
                return;
            }

            if (!this.c.tipo || !this.c.serie_id) {
                this.showAlert('error', 'Debe seleccionar un tipo de comprobante y una serie.');
                return;
            }

            this.isSaving = true;
            const payload = {
                cabecera: this.c,
                totales: this.totales,
                cliente_id: this.cliente.id,
                items: this.items
            };

            try {
                const res = await fetch('save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                this.showAlert('success', 'Guardado localmente. Interconectando con SUNAT...');
                this.enviarASunat(data.comprobante_id);
            } catch (err) {
                this.showAlert('error', err.message);
                this.isSaving = false;
            }
        },
        async enviarASunat(id) {
            try {
                const res = await fetch('api_sunat_enviar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                
                if (data.success) {
                    this.showAlert('success', 'Comprobante ACEPTADO por SUNAT: ' + (data.mensaje || ''));
                } else {
                    this.showAlert('error', 'Guardado local. Error SUNAT: ' + (data.error || 'Rechazado'));
                }
                
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 3000);

            } catch(e) {
                this.showAlert('error', 'Comprobante guardado localmente. Falló internet/SUNAT.');
                setTimeout(() => window.location.href = 'index.php', 3000);
            }
        }
    }
}
</script>

<?php require_once '../../../includes/footer.php'; ?>
