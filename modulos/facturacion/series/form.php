<?php
$base_url = '../../../';
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
require_permission('facturacion_series', $id > 0 ? 'editar' : 'crear');

$serie_data = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM series_facturacion WHERE id = ?");
    $stmt->execute([$id]);
    $serie_data = $stmt->fetch();
    
    if (!$serie_data) {
        $_SESSION['error'] = "Serie no encontrada.";
        header("Location: index.php");
        exit;
    }
}

require_once '../../../includes/header.php';
?>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight"><?= $id > 0 ? 'Editar Serie' : 'Nueva Serie' ?></h2>
        <p class="text-sm font-medium text-gray-500 mt-1"><?= $id > 0 ? 'Modifica los datos de la serie existente.' : 'Registra una nueva serie de comprobante.' ?></p>
    </div>
    <a href="index.php" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Volver
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-start" role="alert">
        <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
        <p class="text-sm font-medium"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden max-w-2xl">
    <form action="save.php" method="POST" class="p-6 sm:p-8">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
            <div class="sm:col-span-2">
                <label for="tipo_comprobante" class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Comprobante <span class="text-red-500">*</span></label>
                <select id="tipo_comprobante" name="tipo_comprobante" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-4 py-2.5 bg-gray-50 border text-gray-900 transition-colors">
                    <option value="">Seleccione...</option>
                    <option value="BOLETA" <?= ($serie_data['tipo_comprobante'] ?? '') === 'BOLETA' ? 'selected' : '' ?>>Boleta</option>
                    <option value="FACTURA" <?= ($serie_data['tipo_comprobante'] ?? '') === 'FACTURA' ? 'selected' : '' ?>>Factura</option>
                    <option value="NOTA_CREDITO" <?= ($serie_data['tipo_comprobante'] ?? '') === 'NOTA_CREDITO' ? 'selected' : '' ?>>Nota de Crédito</option>
                    <option value="NOTA_DEBITO" <?= ($serie_data['tipo_comprobante'] ?? '') === 'NOTA_DEBITO' ? 'selected' : '' ?>>Nota de Débito</option>
                </select>
            </div>

            <div class="sm:col-span-1">
                <label for="serie" class="block text-sm font-semibold text-gray-700 mb-1">Prefijo de Serie <span class="text-red-500">*</span></label>
                <input type="text" id="serie" name="serie" value="<?= htmlspecialchars($serie_data['serie'] ?? '') ?>" required maxlength="4" placeholder="Ej: F001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-4 py-2.5 border text-gray-900 transition-colors uppercase">
                <p class="mt-1 text-xs text-gray-500">Máximo 4 caracteres. Ej: B001, F001.</p>
            </div>

            <div class="sm:col-span-1">
                <label for="correlativo_actual" class="block text-sm font-semibold text-gray-700 mb-1">Correlativo Inicio/Actual <span class="text-red-500">*</span></label>
                <input type="number" id="correlativo_actual" name="correlativo_actual" value="<?= htmlspecialchars($serie_data['correlativo_actual'] ?? '1') ?>" required min="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-4 py-2.5 border text-gray-900 transition-colors">
            </div>

            <div class="sm:col-span-2">
                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción / Uso</label>
                <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($serie_data['descripcion'] ?? '') ?>" placeholder="Ej: Ventas mostrador principal" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-4 py-2.5 border text-gray-900 transition-colors">
                <p class="mt-1 text-xs text-gray-500">Opcional. Breve detalle sobre el uso de esta serie de facturación.</p>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center mt-2">
                    <input id="estado" name="estado" type="checkbox" value="1" <?= (!isset($serie_data) || $serie_data['estado'] == 1) ? 'checked' : '' ?> class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                    <label for="estado" class="ml-3 block text-sm font-medium text-gray-700 cursor-pointer">
                        Serie Activa (Disponible para emitir facturas)
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex items-center justify-end space-x-3">
            <a href="index.php" class="bg-white py-2.5 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="inline-flex justify-center whitespace-nowrap bg-blue-600 py-2.5 px-6 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-save mr-2"></i> Guardar Serie
            </button>
        </div>
    </form>
</div>

<?php require_once '../../../includes/footer.php'; ?>
