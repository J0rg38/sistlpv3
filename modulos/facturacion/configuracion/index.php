<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth_helpers.php';
require_permission('facturacion_configuracion', 'ver');

$stmt = $pdo->query("SELECT * FROM empresa_config WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    die("No se encontro la configuracion inicial.");
}

$pageTitle = "Configuración Facturador";
ob_start();
?>

<div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-blue-600 pl-3">Configuración de Facturación
            Electrónica</h1>
        <p class="mt-1 text-sm text-gray-500">Administra los credenciales SOL y tu Certificado Digital P12/PEM.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6">
        <form id="configForm" class="space-y-6" enctype="multipart/form-data">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Zona General -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Datos de la Empresa</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">RUC</label>
                        <input type="text" name="ruc" value="<?= htmlspecialchars($config['ruc'])?>" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                        <input type="text" name="razon_social" value="<?= htmlspecialchars($config['razon_social'])?>"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre Comercial</label>
                        <input type="text" name="nombre_comercial"
                            value="<?= htmlspecialchars($config['nombre_comercial'] ?? '')?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dirección Completa</label>
                        <input type="text" name="direccion" value="<?= htmlspecialchars($config['direccion'])?>"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ubigeo</label>
                            <input type="text" name="ubigeo" value="<?= htmlspecialchars($config['ubigeo'])?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Distrito</label>
                            <input type="text" name="distrito" value="<?= htmlspecialchars($config['distrito'])?>"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provincia</label>
                            <input type="text" name="provincia" value="<?= htmlspecialchars($config['provincia'])?>"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Departamento</label>
                            <input type="text" name="departamento"
                                value="<?= htmlspecialchars($config['departamento'])?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-sm font-medium text-gray-700">Cuenta Banco de la Nación
                            (Detracciones)</label>
                        <input type="text" name="cuenta_banco_nacion"
                            value="<?= htmlspecialchars($config['cuenta_banco_nacion'] ?? '')?>"
                            placeholder="Ej: 00-000-000000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <p class="text-xs text-gray-400 mt-1">Requerido solo si emitirás facturas sujetas a detracción
                            SPOT.</p>
                    </div>
                </div>

                <!-- Zona SUNAT y Archivos -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Credenciales y Certificados</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Usuario SOL</label>
                            <input type="text" name="sol_usuario"
                                value="<?= htmlspecialchars($config['sol_usuario'])?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Clave SOL</label>
                            <input type="password" name="sol_clave"
                                value="<?= htmlspecialchars($config['sol_clave'])?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contraseña Certificado</label>
                            <input type="password" name="certificado_clave"
                                value="<?= htmlspecialchars($config['certificado_clave'])?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <p class="text-xs text-gray-400 mt-1">Contraseña obligatoria si se sube un certificado .pfx
                                o .p12</p>
                        </div>
                    </div>

                    <div class="mt-4 p-4 border border-blue-100 bg-blue-50 rounded-lg">
                        <label class="block text-sm font-medium text-blue-900">Actualizar Certificado Digital (.pem,
                            .crt, .p12, .pfx)</label>
                        <p class="text-xs text-blue-700 mb-2">Actualmente cargado: <strong>
                                <?= basename($config['certificado_path'])?>
                            </strong></p>
                        <input type="file" name="certificado" accept=".pem,.crt,.p12,.pfx"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>

                    <div class="mt-4 p-4 border border-gray-200 bg-gray-50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-900">Actualizar Logo de la Empresa (.jpg,
                            .png)</label>
                        <?php if (!empty($config['logo_path'])): ?>
                        <p class="text-xs text-gray-600 mb-2">Logo actual registrado. Seleccionar otro para reemplazar.
                        </p>
                        <?php
endif; ?>
                        <input type="file" name="logo" accept="image/jpeg,image/png"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    </div>

                    <div class="flex items-center mt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="estado_facturacion" value="1" class="sr-only peer"
                                <?=$config['estado_facturacion'] ? 'checked' : ''?>>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                            </div>
                            <span class="ml-3 text-sm font-semibold text-gray-700">Produccion Facturacion
                                Electronica</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-5 border-t border-gray-200 flex justify-end">
                <button type="submit" id="btnGuardar"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Guardar Configuración
                </button>
            </div>
            <div id="msgBox" class="hidden mt-3 p-3 rounded text-sm text-center font-medium"></div>

        </form>
    </div>
</div>

<script>
    document.getElementById('configForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnGuardar');
        const msgBox = document.getElementById('msgBox');

        btn.disabled = true;
        btn.innerHTML = 'Guardando...';

        const formData = new FormData(e.target);

        try {
            const response = await fetch('save.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            msgBox.classList.remove('hidden', 'bg-red-50', 'text-red-800', 'bg-green-50', 'text-green-800');
            if (res.success) {
                msgBox.classList.add('bg-green-50', 'text-green-800');
                msgBox.innerText = 'Configuración actualizada correctamente.';
                setTimeout(() => location.reload(), 1500);
            } else {
                msgBox.classList.add('bg-red-50', 'text-red-800');
                msgBox.innerText = res.error || 'Ocurrió un error al guardar.';
                btn.disabled = false;
                btn.innerHTML = 'Guardar Configuración';
            }
        } catch (err) {
            msgBox.classList.remove('hidden');
            msgBox.classList.add('bg-red-50', 'text-red-800');
            msgBox.innerText = 'Error de conexión.';
            btn.disabled = false;
            btn.innerHTML = 'Guardar Configuración';
        }
    });
</script>

<?php
$base_url = '../../../';
$content = ob_get_clean();
require_once '../../../includes/header.php';
require_once '../../../includes/sidebar.php';
echo $content;
require_once '../../../includes/footer.php';
?>