<?php
$base_url = '../../';
require_once '../../config/db.php';
require_once '../../includes/auth_helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
require_permission('clientes', $id > 0 ? 'editar' : 'crear');

require_once '../../includes/header.php';

$cliente = [
    'tipo_cliente' => 'NATURAL', 'tipo_documento' => 'DNI', 'numero_documento' => '', 
    'razon_social' => '', 'nombres' => '', 'apellidos' => '', 
    'direccion' => '', 'departamento' => '', 'provincia' => '', 'distrito' => '',
    'telefono' => '', 'email' => ''
];
$title = "Nuevo Cliente";

if ($id > 0) {
    $title = "Editar Cliente";
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    if ($result) {
        $cliente = $result;
    } else {
        $_SESSION['error'] = "Cliente no encontrado.";
        echo '<script>window.location.href="index.php";</script>';
        exit;
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center transition-colors w-fit">
        <i class="fas fa-arrow-left mr-2"></i> Regresar al listado
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden max-w-5xl">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
        <div>
            <h3 class="text-lg font-bold text-gray-900"><?= $title ?></h3>
            <p class="text-sm text-gray-500 mt-1">Ingresa la información detallada del cliente.</p>
        </div>
    </div>
    
    <form action="save.php" method="POST" class="p-6" id="clienteForm">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <!-- Guardamos los nombres seleccionados en campos ocultos -->
        <input type="hidden" name="departamento_nombre" id="departamento_nombre" value="<?= htmlspecialchars($cliente['departamento']) ?>">
        <input type="hidden" name="provincia_nombre" id="provincia_nombre" value="<?= htmlspecialchars($cliente['provincia']) ?>">
        <input type="hidden" name="distrito_nombre" id="distrito_nombre" value="<?= htmlspecialchars($cliente['distrito']) ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-6">
            <!-- Columna Izquierda: Datos Principales -->
            <div class="md:col-span-8 space-y-6">
                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Datos Principales</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Cliente</label>
                        <select name="tipo_cliente" id="tipo_cliente" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all font-semibold uppercase">
                            <option value="NATURAL" <?= $cliente['tipo_cliente'] == 'NATURAL' ? 'selected' : '' ?>>NATURAL</option>
                            <option value="EMPRESA" <?= $cliente['tipo_cliente'] == 'EMPRESA' ? 'selected' : '' ?>>EMPRESA</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Documento</label>
                        <select name="tipo_documento" id="tipo_documento" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase">
                            <!-- Options logic in JS -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Número de Documento</label>
                        <input type="text" name="numero_documento" id="numero_documento" value="<?= htmlspecialchars($cliente['numero_documento']) ?>" required class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>

                <!-- Campos NATURAL -->
                <div id="campos_natural" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nombres</label>
                        <input type="text" name="nombres" id="nombres" value="<?= htmlspecialchars($cliente['nombres']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" value="<?= htmlspecialchars($cliente['apellidos']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>

                <!-- Campos EMPRESA -->
                <div id="campos_empresa" class="hidden">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Razón Social</label>
                        <input type="text" name="razon_social" id="razon_social" value="<?= htmlspecialchars($cliente['razon_social']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Contacto y Ubicacion -->
            <div class="md:col-span-4 space-y-6">
                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Contacto</h4>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
            </div>
        </div>

        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2 mt-4">Ubicación y Dirección</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Departamento</label>
                <select id="select_departamento" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase">
                    <option value="">SELECCIONE...</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Provincia</label>
                <select id="select_provincia" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" disabled>
                    <option value="">SELECCIONE...</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Distrito</label>
                <select id="select_distrito" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" disabled>
                    <option value="">SELECCIONE...</option>
                </select>
            </div>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección Completa</label>
            <input type="text" name="direccion" value="<?= htmlspecialchars($cliente['direccion']) ?>" class="block w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase" placeholder="AV. EJEMPLO 123" oninput="this.value = this.value.toUpperCase()">
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-100 mt-6">
            <a href="index.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-6 rounded-lg mr-3 transition-colors">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                <i class="fas fa-save mr-2"></i> Guardar Cliente
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoClienteSelect = document.getElementById('tipo_cliente');
    const tipoDocumentoSelect = document.getElementById('tipo_documento');
    const camposNatural = document.getElementById('campos_natural');
    const camposEmpresa = document.getElementById('campos_empresa');
    const nombresInput = document.getElementById('nombres');
    const apellidosInput = document.getElementById('apellidos');
    const razonSocialInput = document.getElementById('razon_social');
    
    // Ubigeo selects
    const selectDepto = document.getElementById('select_departamento');
    const selectProv = document.getElementById('select_provincia');
    const selectDist = document.getElementById('select_distrito');
    
    // Hidden inputs
    const hiddenDepto = document.getElementById('departamento_nombre');
    const hiddenProv = document.getElementById('provincia_nombre');
    const hiddenDist = document.getElementById('distrito_nombre');

    // Values from database for edition
    const currentTipoDoc = "<?= htmlspecialchars($cliente['tipo_documento']) ?>";
    const currentDepto = hiddenDepto.value;
    const currentProv = hiddenProv.value;
    const currentDist = hiddenDist.value;

    function toggleFields() {
        const tipo = tipoClienteSelect.value;
        tipoDocumentoSelect.innerHTML = '';
        
        if (tipo === 'NATURAL') {
            camposNatural.classList.remove('hidden');
            camposEmpresa.classList.add('hidden');
            nombresInput.required = true;
            apellidosInput.required = true;
            razonSocialInput.required = false;
            
            tipoDocumentoSelect.innerHTML = `
                <option value="DNI" ${currentTipoDoc === 'DNI' ? 'selected' : ''}>DNI</option>
                <option value="CE" ${currentTipoDoc === 'CE' ? 'selected' : ''}>CE</option>
            `;
            document.getElementById('numero_documento').removeAttribute('pattern');
            document.getElementById('numero_documento').removeAttribute('title');
            document.getElementById('numero_documento').removeAttribute('maxlength');
        } else {
            camposNatural.classList.add('hidden');
            camposEmpresa.classList.remove('hidden');
            nombresInput.required = false;
            apellidosInput.required = false;
            razonSocialInput.required = true;
            
            tipoDocumentoSelect.innerHTML = `
                <option value="RUC" selected>RUC</option>
            `;
            document.getElementById('numero_documento').setAttribute('pattern', '[0-9]{11}');
            document.getElementById('numero_documento').setAttribute('title', 'El RUC debe tener exactamente 11 dígitos');
            document.getElementById('numero_documento').setAttribute('maxlength', '11');
        }
    }

    tipoClienteSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial call
    
    // API Ubigeo Fetching System
    async function loadDepartamentos() {
        try {
            const res = await fetch('api_ubigeo.php?action=get_departamentos');
            const data = await res.json();
            
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id_ubigeo;
                opt.textContent = `${d.id_ubigeo} - ${d.nombre_ubigeo.toUpperCase()}`;
                
                // Pre-select if matches the saved name in DB
                if (currentDepto && d.nombre_ubigeo.toUpperCase() === currentDepto.toUpperCase()) {
                    opt.selected = true;
                    setTimeout(() => triggerChange(selectDepto), 10);
                }
                selectDepto.appendChild(opt);
            });
        } catch (e) {
            console.error('Error fetching departamentos', e);
        }
    }
    
    async function loadProvincias(deptoId) {
        selectProv.innerHTML = '<option value="">SELECCIONE...</option>';
        selectDist.innerHTML = '<option value="">SELECCIONE...</option>';
        selectProv.disabled = true;
        selectDist.disabled = true;
        
        if (!deptoId) return;
        
        try {
            const res = await fetch(`api_ubigeo.php?action=get_provincias&department_id=${deptoId}`);
            const data = await res.json();
            
            if (data.length > 0) {
                selectProv.disabled = false;
                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id_ubigeo;
                    opt.textContent = `${p.id_ubigeo} - ${p.nombre_ubigeo.toUpperCase()}`;
                    
                    if (currentProv && p.nombre_ubigeo.toUpperCase() === currentProv.toUpperCase()) {
                        opt.selected = true;
                        setTimeout(() => triggerChange(selectProv), 10);
                    }
                    selectProv.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Error fetching provincias', e);
        }
    }
    
    async function loadDistritos(provId) {
        selectDist.innerHTML = '<option value="">SELECCIONE...</option>';
        selectDist.disabled = true;
        
        if (!provId) return;
        
        try {
            const res = await fetch(`api_ubigeo.php?action=get_distritos&province_id=${provId}`);
            const data = await res.json();
            
            if (data.length > 0) {
                selectDist.disabled = false;
                data.forEach(dt => {
                    const opt = document.createElement('option');
                    opt.value = dt.id_ubigeo;
                    opt.textContent = `${dt.id_ubigeo} - ${dt.nombre_ubigeo.toUpperCase()}`;
                    
                    if (currentDist && dt.nombre_ubigeo.toUpperCase() === currentDist.toUpperCase()) {
                        opt.selected = true;
                    }
                    selectDist.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Error fetching distritos', e);
        }
    }

    function triggerChange(element) {
        const event = new Event('change');
        element.dispatchEvent(event);
    }
    
    // Save the texts into hidden inputs for backend saving
    function updateHiddenName(selectElement, hiddenElement) {
        if (selectElement.selectedIndex > 0) {
            const txt = selectElement.options[selectElement.selectedIndex].text;
            hiddenElement.value = txt.substring(txt.indexOf('-') + 1).trim();
        } else {
            hiddenElement.value = '';
        }
    }

    selectDepto.addEventListener('change', function() {
        updateHiddenName(this, hiddenDepto);
        loadProvincias(this.value);
    });
    
    selectProv.addEventListener('change', function() {
        updateHiddenName(this, hiddenProv);
        loadDistritos(this.value);
    });
    
    selectDist.addEventListener('change', function() {
        updateHiddenName(this, hiddenDist);
    });

    // Start load
    loadDepartamentos();
});
</script>

<?php require_once '../../includes/footer.php'; ?>
