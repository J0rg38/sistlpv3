<?php
require_once '../../../config/db.php';
require_once '../../../vendor/autoload.php';
require_once '../../../includes/auth_helpers.php';

$id = $_GET['id'] ?? 0;
if (!$id) die("ID Inválido");

$stmt = $pdo->prepare("SELECT * FROM comprobantes WHERE id = ?");
$stmt->execute([$id]);
$comp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comp) die("Comprobante no hallado");

$stmtItems = $pdo->prepare("SELECT * FROM comprobantes_items WHERE comprobante_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$config = require '../../../config/sunat.php';

class FacturaPDF extends TCPDF {
    public $headerHtml = '';
    public function Header() {
        $this->SetY(10);
        $this->writeHTML($this->headerHtml, true, false, true, false, '');
    }
}

// Crear PDF avanzado con TCPDF usando HTML
$pdf = new FacturaPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SISTLPV3');
$pdf->SetAuthor($config['empresa']['nombre_comercial']);
$pdf->SetTitle($comp['tipo_comprobante'] . ' ' . $comp['serie'] . '-' . $comp['correlativo']);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 48, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

$numeroDocFmt = str_pad($comp['correlativo'], 8, '0', STR_PAD_LEFT);
$tipoDocNombres = [
    'FACTURA' => 'FACTURA ELECTRÓNICA',
    'BOLETA' => 'BOLETA ELECTRÓNICA',
    'NOTA_CREDITO' => 'NOTA DE CRÉDITO ELECTRÓNICA',
    'NOTA_DEBITO' => 'NOTA DE DÉBITO ELECTRÓNICA'
];
$tipoDocStr = $tipoDocNombres[$comp['tipo_comprobante']] ?? strtoupper($comp['tipo_comprobante']);
$fechaEmision = date('d/m/Y', strtotime($comp['fecha_emision']));
$fechaVencimiento = date('d/m/Y', strtotime($comp['fecha_vencimiento']));

$monedaSymbol = $comp['moneda'] === 'PEN' ? 'S/' : '$';
$monedaLiteral = $comp['moneda'] === 'PEN' ? 'SOLES' : 'DÓLARES';

$logoHTML = '';
if (!empty($config['logo'])) {
    $logoReal = realpath(__DIR__ . '/../../../' . $config['logo']);
    if (file_exists($logoReal)) {
        $logoHTML = '<img src="' . $logoReal . '" width="200" />';
    }
}

$pdf->headerHtml = '
<table width="100%" cellpadding="0">
    <tr>
        <td width="60%">
            ' . $logoHTML . '
            <br/>
            <strong style="color:#1e3a8a; font-size:15pt;">' . htmlspecialchars($config['empresa']['razon_social']) . '</strong><br/>
            <span style="font-size:8.5pt; color:#4a5568;"><strong>Dirección:</strong>' . htmlspecialchars($config['empresa']['direccion']['direccion']) . '<br/>
                ' . htmlspecialchars($config['empresa']['direccion']['distrito']) . ' - ' . htmlspecialchars($config['empresa']['direccion']['provincia']) . ' - ' . htmlspecialchars($config['empresa']['direccion']['departamento']) . '
            </span>
        </td>
        <td width="40%">
            <table width="100%" style="padding-bottom: 12px; border: 2px solid #2d3748; text-align:center; background-color:#f8fafc;">
                <tr>
                    <td>
                        <div style="font-size:12pt; font-weight:bold; color:#1a202c;">R.U.C. ' . $config['empresa']['ruc'] . '</div>
                        <div style="font-size:11pt; font-weight:bold; color:#1a202c;">' . $tipoDocStr . '</div>
                        <div style="font-size:13pt; font-weight:bold; color:#1a202c;">' . $comp['serie'] . ' - ' . $numeroDocFmt . '</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
';

$pdf->AddPage();

$html = '
<br><br>
<table width="100%" cellpadding="5" style="border: 2px solid #2d3748; font-size:9pt; background-color:#ffffff;">
    <tr>
        <td width="15%"><strong>Cliente:</strong></td>
        <td width="55%">' . htmlspecialchars($comp['cliente_razon_social']) . '</td>
        <td width="15%"><strong>Fecha Emisión:</strong></td>
        <td width="15%">' . $fechaEmision . '</td>
    </tr>
    <tr>
        <td width="15%"><strong>RUC/DNI:</strong></td>
        <td width="55%">' . htmlspecialchars($comp['cliente_numero_documento']) . '</td>
        <td width="15%"><strong>Moneda:</strong></td>
        <td width="15%">' . $monedaLiteral . '</td>
    </tr>
    <tr>
        <td width="15%"><strong>Dirección:</strong></td>
        <td width="55%">' . htmlspecialchars($comp['cliente_direccion_completa']) . '</td>
        <td width="15%"><strong>Cond. Pago:</strong></td>
        <td width="15%">' . $comp['condicion_pago'] . '</td>
    </tr>
</table>
<br><br>

<table width="100%" cellpadding="5" style="border-collapse: collapse; font-size:8.5pt; text-align:center; border: 0.5pt solid #2d3748;">
    <tr style="background-color:#ffffff; font-weight:bold; color:#1e293b;">
        <th width="15%" style="border: 0.5pt solid #2d3748;">CÓDIGO</th>
        <th width="39%" style="border: 0.5pt solid #2d3748;">DESCRIPCIÓN</th>
        <th width="6%" style="border: 0.5pt solid #2d3748;">U.M.</th>
        <th width="10%" style="border: 0.5pt solid #2d3748;">CANT.</th>
        <th width="15%" style="border: 0.5pt solid #2d3748;">PRECIO UNIT.</th>
        <th width="15%" style="border: 0.5pt solid #2d3748;">TOTAL (' . $monedaSymbol . ')</th>
    </tr>';

$totalDescuentosPdf = 0;
foreach ($items as $it) {
    $precioConIgv = $it['precio_unitario'];
    $montoLineBruto = $precioConIgv * $it['cantidad'];
    $totalDescuentosPdf += (float)$it['descuento'];
    
    $html .= '
        <tr style="color:#0f172a;">
            <td width="15%" style="border: 0.5pt solid #2d3748; font-size:8px">' . htmlspecialchars($it['codigo']) . '</td>
            <td width="39%" style="border: 0.5pt solid #2d3748; text-align:left; font-size:8px">' . htmlspecialchars($it['descripcion']) . '</td>
            <td width="6%" style="border: 0.5pt solid #2d3748; text-align:center; font-size:8px">' . htmlspecialchars($it['unidad_medida']) . '</td>
            <td width="10%" style="border: 0.5pt solid #2d3748; font-size:8px">' . number_format($it['cantidad'], 2) . '</td>
            <td width="15%" style="border: 0.5pt solid #2d3748; text-align:right; font-size:8px">' . number_format($precioConIgv, 4) . '</td>
            <td width="15%" style="border: 0.5pt solid #2d3748; text-align:right; font-size:8px">' . number_format($montoLineBruto, 2) . '</td>
        </tr>';
}

$html .= '</table>
<br><br>
<table width="100%">
    <tr>
        <td width="60%">';

if ($comp['condicion_pago'] === 'CREDITO') {
    $html .= '<table width="100%" cellpadding="5" style="border-collapse: collapse; border: 1.5px solid #2d3748; background-color:#ffffff;">
                <tr><td colspan="3"><strong style="font-size:9pt; color:#0f172a;">DETALLE DE PAGO A CRÉDITO</strong></td></tr>
                <tr style="font-size:8.5pt; color:#0f172a;">
                    <td width="33%"><strong>Días Crédito:</strong> ' . $comp['dias_credito'] . '</td>
                    <td width="33%"><strong>Vencimiento:</strong> ' . $fechaVencimiento . '</td>
                    <td width="34%"><strong>Cuota:</strong> ' . $monedaSymbol . ' ' . number_format($comp['total'], 2) . '</td>
                </tr>
            </table><br>';
}

// Calcular de antemano detracción real en moneda nacional
$montoDetraccionReal = (float)$comp['monto_detraccion'];
$simboloDetraccion = $monedaSymbol;
$tcSolesMsg = '';
if ($comp['tiene_detraccion'] == 1 && $comp['moneda'] === 'USD') {
    $tc = (float)$comp['tipo_cambio'] > 0 ? (float)$comp['tipo_cambio'] : 1;
    $montoDetraccionReal = round($montoDetraccionReal * $tc, 2);
    $simboloDetraccion = 'S/';
    $tcSolesMsg = ' (TC ' . number_format($tc, 3) . ')';
}

if ($comp['tiene_detraccion'] == 1) {
    $html .= '<br><table width="100%" cellpadding="5" style="border: 1px solid #2300AD; background-color:#eff6ff;">
                <tr><td colspan="3"><strong style="font-size:9pt; color:#1e3a8a;">OPERACIÓN SUJETA A DETRACCIÓN (' . number_format($comp['porcentaje_detraccion'], 0) . '%)</strong></td></tr>
                <tr style="font-size:8.5pt; color:#1e40af;">
                    <td width="33%"><strong>Cta. Banco de la Nación:</strong><br>' . htmlspecialchars($config['empresa']['direccion']['cuenta_banco_nacion'] ?? '') . '</td>
                    <td width="33%"><strong>Catálogo 54:</strong> ' . htmlspecialchars($comp['codigo_detraccion']) . '</td>
                    <td width="34%"><strong>Monto a Detraer' . $tcSolesMsg . ':</strong><br>' . $simboloDetraccion . ' ' . number_format($montoDetraccionReal, 2) . '</td>
                </tr>
            </table><br>';
}

if ($comp['tiene_retencion'] == 1) {
    $html .= '<table width="100%" cellpadding="5" style="border: 1px solid #8F0D13; background-color:#fef2f2;">
                <tr><td><strong style="font-size:9pt; color:#991b1b;">OPERACIÓN SUJETA A RETENCIÓN DEL I.G.V. (3%)</strong></td></tr>
                <tr style="font-size:8.5pt; color:#b91c1c;">
                    <td>El comprobante está sujeto a retención. Monto retenido: <strong>' . $monedaSymbol . ' ' . number_format($comp['monto_retencion'], 2) . '</strong></td>
                </tr>
            </table><br>';
}

function NumerosEnLetras($monto, $moneda)
{
    $enteros = floor($monto);
    $decimales = round(($monto - $enteros) * 100);
    $textoMoneda = $moneda === 'PEN' ? 'SOLES' : 'DÓLARES AMERICANOS';
    
    $f = function($numero) use (&$f) {
        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE', 'VEINTIUN', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];
        $decenas = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($numero == 0) return 'CERO';
        if ($numero == 100) return 'CIEN';
        
        $letras = '';
        if ($numero >= 1000) {
            $miles = floor($numero / 1000);
            $numero = $numero % 1000;
            if ($miles == 1) $letras .= 'MIL ';
            else $letras .= $f($miles) . ' MIL ';
        }
        if ($numero >= 100) {
            $c = floor($numero / 100);
            $numero = $numero % 100;
            $letras .= $centenas[$c] . ' ';
        }
        if ($numero > 0) {
            if ($numero < 30) {
                $letras .= $unidades[$numero];
            } else {
                $d = floor($numero / 10);
                $u = $numero % 10;
                $letras .= $decenas[$d];
                if ($u > 0) $letras .= ' Y ' . $unidades[$u];
            }
        }
        return trim($letras);
    };

    return $f($enteros) . " Y " . str_pad($decimales, 2, '0', STR_PAD_LEFT) . "/100 " . $textoMoneda;
}

$html .= '<div style="padding: 8px; font-size:8.5pt; background-color:#ffffff;">
                <strong>SON: </strong> ' . strtoupper(NumerosEnLetras($comp['total'], $comp['moneda'])) . '<br><br>
                <strong>Observaciones:</strong> ' . htmlspecialchars($comp['observaciones']) . '
            </div>
        </td>
        <td width="1%"></td>
        <td width="39%">
            <table width="100%" cellpadding="6" style="border-collapse: collapse; border: 1.5px solid #2d3748; font-size:9pt; background-color:#ffffff;">';

if ($totalDescuentosPdf > 0) {
    $html .= '
                <tr>
                    <td width="55%" align="right" style="color:#0f172a; font-weight:bold; border-bottom: 1px solid #2d3748;">(-) DESCUENTOS:</td>
                    <td width="45%" align="right" style="border-bottom: 1px solid #2d3748;">' . $monedaSymbol . ' ' . number_format($totalDescuentosPdf, 2) . '</td>
                </tr>';
}

$html .= '
                <tr>
                    <td width="55%" align="right" style="color:#0f172a; font-weight:bold; border-bottom: 1px solid #2d3748;">OP. GRAVADA:</td>
                    <td width="45%" align="right" style="border-bottom: 1px solid #2d3748;">' . $monedaSymbol . ' ' . number_format($comp['subtotal'], 2) . '</td>
                </tr>
                <tr>
                    <td width="55%" align="right" style="color:#0f172a; font-weight:bold; border-bottom: 1px solid #2d3748;">I.G.V. (18%):</td>
                    <td width="45%" align="right" style="border-bottom: 1px solid #2d3748;">' . $monedaSymbol . ' ' . number_format($comp['igv'], 2) . '</td>
                </tr>
                <tr>
                    <td width="55%" align="right" style="color:#0f172a; font-weight:bold; border-bottom: 1px solid #2d3748;">TOTAL FINAL:</td>
                    <td width="45%" align="right" style="color:#0f172a; font-weight:bold; border-bottom: 1px solid #2d3748;">' . $monedaSymbol . ' ' . number_format($comp['total'], 2) . '</td>
                </tr>';

if ($comp['tiene_detraccion'] == 1) {
    $html .= '
                <tr style="background-color:#ffffff;">
                    <td width="55%" align="right" style="color:#dc2626; font-weight:bold; border-bottom: 1px solid #2d3748;">(-) DETRACCIÓN ' . $tcSolesMsg . ':</td>
                    <td width="45%" align="right" style="color:#dc2626; font-weight:bold; border-bottom: 1px solid #2d3748;">' . $simboloDetraccion . ' ' . number_format($montoDetraccionReal, 2) . '</td>
                </tr>';
}
if ($comp['tiene_retencion'] == 1) {
    $html .= '
                <tr style="background-color:#ffffff;">
                    <td width="55%" align="right" style="color:#dc2626; font-weight:bold; border-bottom: 1px solid #2d3748;">(-) RETENCIÓN:</td>
                    <td width="45%" align="right" style="color:#dc2626; font-weight:bold; border-bottom: 1px solid #2d3748;">' . $monedaSymbol . ' ' . number_format($comp['monto_retencion'], 2) . '</td>
                </tr>';
}

if ($comp['tiene_detraccion'] == 1 || $comp['tiene_retencion'] == 1) {
    $neto = $comp['total'] - (float)$comp['monto_detraccion'] - (float)$comp['monto_retencion'];
    $html .= '
                <tr>
                    <td width="55%" align="right" style="color:#0f172a; font-weight:bold; font-size:10pt; padding-top:8px;">NETO PAGAR:</td>
                    <td width="45%" align="right" style="color:#0f172a; font-weight:bold; font-size:10pt; padding-top:8px;">' . $monedaSymbol . ' ' . number_format($neto, 2) . '</td>
                </tr>';
}

$html .= '
            </table>
        </td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

// QR y pie al final del documento alineado al centro
$style = array(
    'border' => 0,
    'padding' => 0,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);
$qr_content = "{$config['empresa']['ruc']}|{$comp['codigo_tipo_documento']}|{$comp['serie']}|{$comp['correlativo']}|{$comp['igv']}|{$comp['total']}|{$comp['fecha_emision']}|6|{$comp['cliente_numero_documento']}|{$comp['hash_cpe']}";

$pdf->Ln(8);
$yAntes = $pdf->GetY();

if ($pdf->getPageHeight() - $yAntes < 50) {
    $pdf->AddPage();
    $yAntes = $pdf->GetY();
}

$pdf->write2DBarcode($qr_content, 'QRCODE,H', 90, $yAntes, 30, 30, $style, 'N');

$pdf->SetY($yAntes + 32);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, 'Representación Impresa de la ' . $tipoDocStr, 0, 1, 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 5, 'Autorizado mediante resolución SUNAT.', 0, 1, 'C');
if ($comp['hash_cpe']) {
    $pdf->Cell(0, 5, 'Resumen Hash: ' . $comp['hash_cpe'], 0, 1, 'C');
}

$pdf->Output("{$comp['serie']}-{$comp['correlativo']}.pdf", 'I');
?>
