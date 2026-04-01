<?php
$base_url = '../../../';
require_once $base_url . 'includes/auth_helpers.php';
require_permission('reportes_contabilidad', 'ver');
require_once $base_url . 'includes/header.php';

$mesActual = date('m');
$anioActual = date('Y');
?>
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Registro de Ventas (RVIE)</h2>
        <p class="text-sm font-medium text-gray-500 mt-1">Exportación contable según formato SUNAT PLE 14.1 / RVIE (SIRE) y Formato MS Excel.</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8 max-w-4xl">
    <div class="p-4 bg-blue-50 text-blue-800 rounded-xl mb-6 flex items-start">
        <i class="fas fa-info-circle mt-1 mr-3 text-lg"></i>
        <p class="text-sm font-medium">
            Seleccione el período que desea reportar. Los reportes TXT (PLE o SIRE RVIE) se autodenominarán en base a las reglas obligatorias de SUNAT usando el RUC configurado en el sistema.
        </p>
    </div>

    <form method="GET" action="export_excel.php" target="_blank" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end" id="formReporte">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Periodo (Mes)</label>
            <select name="mes" id="mes" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                <?php
                $meses = ['01'=>'Enero (01)', '02'=>'Febrero (02)', '03'=>'Marzo (03)', '04'=>'Abril (04)', '05'=>'Mayo (05)', '06'=>'Junio (06)', '07'=>'Julio (07)', '08'=>'Agosto (08)', '09'=>'Septiembre (09)', '10'=>'Octubre (10)', '11'=>'Noviembre (11)', '12'=>'Diciembre (12)'];
                foreach($meses as $k => $v) {
                    $sel = ($k == $mesActual) ? 'selected' : '';
                    echo "<option value=\"$k\" $sel>$v</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Año</label>
            <select name="anio" id="anio" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                <?php
                for($i = $anioActual; $i >= $anioActual - 5; $i--){
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select>
        </div>
    </form>
    
    <div class="mt-8 border-t border-gray-100 pt-6 flex flex-col sm:flex-row gap-4">
        <button type="button" onclick="exportar('excel')" class="flex-1 bg-[#107C41] hover:bg-[#185c37] text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors flex items-center justify-center">
            <i class="fas fa-file-excel mr-3 text-xl"></i> Formato MS Excel
        </button>
        <button type="button" onclick="exportar('ple')" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors flex items-center justify-center">
            <i class="fas fa-file-alt mr-3 text-xl"></i> TXT (PLE 14.1)
        </button>
        <button type="button" onclick="exportar('sire')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors flex items-center justify-center">
            <i class="fas fa-cloud-upload-alt mr-3 text-xl"></i> TXT (SIRE RVIE)
        </button>
    </div>
</div>

<script>
function exportar(tipo) {
    const form = document.getElementById('formReporte');
    if (tipo === 'excel') {
        form.action = 'export_excel.php';
    } else if(tipo === 'ple') {
        form.action = 'export_txt_ple.php';
    } else if(tipo === 'sire') {
        form.action = 'export_txt_sire.php';
    }
    form.submit();
}
</script>

<?php require_once $base_url . 'includes/footer.php'; ?>
