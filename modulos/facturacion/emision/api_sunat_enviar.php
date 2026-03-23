<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
require_once '../../../vendor/autoload.php';

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Detraction;
use Greenter\Model\Sale\Charge;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Sale\Cuota;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

if (!$id) {
    echo json_encode(['error' => 'Comprobante no especificado']);
    exit;
}

function NumerosEnLetras($monto, $moneda)
{
    $enteros = floor($monto);
    $decimales = round(($monto - $enteros) * 100);
    $textoMoneda = $moneda === 'PEN' ? 'SOLES' : 'DOLARES AMERICANOS';
    
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

try {
    // 1. Obtener datos locales
    $stmt = $pdo->prepare("SELECT * FROM comprobantes WHERE id = ?");
    $stmt->execute([$id]);
    $comprobante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comprobante) {
        throw new Exception("Comprobante no existe en BD");
    }

    $stmtItems = $pdo->prepare("SELECT * FROM comprobantes_items WHERE comprobante_id = ?");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // 2. Configurar Greenter (Ver)
    $config = require '../../../config/sunat.php';

    $see = new See();
    $see->setCertificate(file_get_contents($config['greenter']['cert_path']));
    $see->setService($config['greenter']['endpoint']);
    $see->setClaveSOL($config['empresa']['ruc'], $config['greenter']['user'], $config['greenter']['password']);

    // 3. Crear Estructura de Factura
    // Mapeo basico de Empresa
    $address = (new Address())
        ->setUbigueo($config['empresa']['direccion']['ubigeo'])
        ->setDepartamento($config['empresa']['direccion']['departamento'])
        ->setProvincia($config['empresa']['direccion']['provincia'])
        ->setDistrito($config['empresa']['direccion']['distrito'])
        ->setUrbanizacion('-')
        ->setDireccion($config['empresa']['direccion']['direccion'])
        ->setCodLocal('0000'); // Local principal

    $company = (new Company())
        ->setRuc($config['empresa']['ruc'])
        ->setRazonSocial($config['empresa']['razon_social'])
        ->setNombreComercial($config['empresa']['nombre_comercial'])
        ->setAddress($address);

    // Cliente
    $tipoDocCli = strlen($comprobante['cliente_numero_documento']) == 11 ? '6' : '1';
    
    $client = (new Client())
        ->setTipoDoc($tipoDocCli)
        ->setNumDoc($comprobante['cliente_numero_documento'])
        ->setRznSocial($comprobante['cliente_razon_social']);

    $esNota = in_array($comprobante['tipo_comprobante'], ['NOTA_CREDITO', 'NOTA_DEBITO']);

    // --- 3. Procesar Items (Extraer sumas sin descuento) ---
    $greenItems = [];

    foreach ($items as $it) {
        $precio_unitario_igv = floatval($it['precio_unitario']);
        $valor_unitario = $precio_unitario_igv / 1.18; 
        $cantidad = floatval($it['cantidad']);
        
        $mtoValorVentaBruto = $valor_unitario * $cantidad;
        $descuentoIgv = floatval($it['descuento']);
        $descuentoBase = $descuentoIgv / 1.18;
        
        $mtoValorVentaNeto = $mtoValorVentaBruto - $descuentoBase;
        $igvNeto = $mtoValorVentaNeto * 0.18;

        $detail = (new SaleDetail())
            ->setCodProducto($it['codigo'] ?: 'P001')
            ->setUnidad($it['unidad_medida'] ?: 'NIU')
            ->setCantidad($cantidad)
            ->setDescripcion($it['descripcion'])
            ->setMtoBaseIgv(round($mtoValorVentaNeto, 2))
            ->setPorcentajeIgv(18.00)
            ->setIgv(round($igvNeto, 2))
            ->setTipAfeIgv('10') // Gravado - Operación Onerosa
            ->setTotalImpuestos(round($igvNeto, 2))
            ->setMtoValorVenta(round($mtoValorVentaNeto, 2))
            ->setMtoValorUnitario(round($valor_unitario, 5))
            ->setMtoPrecioUnitario(round(($mtoValorVentaNeto + $igvNeto) / $cantidad, 5));

        if ($descuentoIgv > 0) {
            $factor = round($descuentoBase / $mtoValorVentaBruto, 5);
            $charge = (new Charge())
                ->setCodTipo('00') // Descuento que afecta la Base Imponible
                ->setMontoBase(round($mtoValorVentaBruto, 2))
                ->setFactor($factor)
                ->setMonto(round($descuentoBase, 2));
            
            $detail->setDescuentos([$charge]);
        }

        $greenItems[] = $detail;
    }

    if ($esNota) {
        if (empty($comprobante['comprobante_relacionado_id'])) {
            throw new Exception("La nota requiere un comprobante de referencia válido.");
        }
        $stmtRel = $pdo->prepare("SELECT serie, correlativo, codigo_tipo_documento FROM comprobantes WHERE id = ?");
        $stmtRel->execute([$comprobante['comprobante_relacionado_id']]);
        $docAf = $stmtRel->fetch(PDO::FETCH_ASSOC);
        
        if (!$docAf) throw new Exception("Comprobante relacionado no hallado en la Base de Datos.");

        // Construir Nota (CreditNote o DebitNote dependiendo del codigo)
        $invoice = (new Note())
            ->setUblVersion('2.1')
            ->setTipDocAfectado($docAf['codigo_tipo_documento'])
            ->setNumDocfectado($docAf['serie'].'-'.$docAf['correlativo'])
            ->setCodMotivo($comprobante['codigo_motivo'])
            ->setDesMotivo($comprobante['descripcion_motivo'])
            ->setTipoDoc($comprobante['codigo_tipo_documento'])
            ->setSerie($comprobante['serie'])
            ->setCorrelativo($comprobante['correlativo'])
            ->setFechaEmision(new DateTime($comprobante['fecha_emision']))
            ->setTipoMoneda($comprobante['moneda'])
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(floatval($comprobante['subtotal']))
            ->setMtoIGV(floatval($comprobante['igv']))
            ->setTotalImpuestos(floatval($comprobante['igv']))
            ->setMtoImpVenta(floatval($comprobante['total']));
            
    } else {
        $formaPago = new FormaPagoContado();
        $cuotas = [];
        if ($comprobante['condicion_pago'] === 'CREDITO') {
            $formaPago = new FormaPagoCredito(floatval($comprobante['total']));
            $cuotas[] = (new Cuota())
                ->setMonto(floatval($comprobante['total']))
                ->setFechaPago(new DateTime($comprobante['fecha_vencimiento']));
        }

        // Invoice normal (Factura/Boleta)
        $tipoOp = $comprobante['tiene_detraccion'] == 1 ? '1001' : '0101';
        
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion($tipoOp)
            ->setTipoDoc($comprobante['codigo_tipo_documento'])
            ->setSerie($comprobante['serie'])
            ->setCorrelativo($comprobante['correlativo'])
            ->setFechaEmision(new DateTime($comprobante['fecha_emision']))
            ->setFormaPago($formaPago)
            ->setTipoMoneda($comprobante['moneda'])
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(floatval($comprobante['subtotal']))
            ->setMtoIGV(floatval($comprobante['igv']))
            ->setTotalImpuestos(floatval($comprobante['igv']))
            ->setValorVenta(floatval($comprobante['subtotal']))
            ->setSubTotal(floatval($comprobante['total']))
            ->setMtoImpVenta(floatval($comprobante['total']));

        if (!empty($cuotas)) {
            $invoice->setCuotas($cuotas);
        }

        if ($comprobante['tiene_detraccion'] == 1) {
            $montoDetraccionFinal = floatval($comprobante['monto_detraccion']);
            if ($comprobante['moneda'] === 'USD') {
                $tc = floatval($comprobante['tipo_cambio']);
                $tc = $tc > 0 ? $tc : 1;
                $montoDetraccionFinal = round($montoDetraccionFinal * $tc, 2);
            }

            $invoice->setDetraccion(
                (new Detraction())
                    ->setCodBienDetraccion($comprobante['codigo_detraccion'])
                    ->setCodMedioPago('001') // Depósito en cuenta
                    ->setCtaBanco(empty($config['empresa']['direccion']['cuenta_banco_nacion']) ? '00-000-000000' : $config['empresa']['direccion']['cuenta_banco_nacion'])
                    ->setPercent(floatval($comprobante['porcentaje_detraccion']))
                    ->setMount($montoDetraccionFinal)
            );
        }
    }

    // Cargos y Descuentos Globales
    $arrCargosDescuentos = [];

    if (!$esNota && isset($comprobante['tiene_retencion']) && $comprobante['tiene_retencion'] == 1) {
        $retencion = (new Charge())
            ->setCodTipo('62') // Catálogo 53: Retención del IGV
            ->setMontoBase(floatval($comprobante['total']))
            ->setFactor(floatval($comprobante['porcentaje_retencion']) / 100)
            ->setMonto(floatval($comprobante['monto_retencion']));
        
        $arrCargosDescuentos[] = $retencion;
    }

    if (!empty($arrCargosDescuentos)) {
        $invoice->setDescuentos($arrCargosDescuentos);
    }

    // Funcion utilitaria
    $textoMonto = NumerosEnLetras((float)$comprobante['total'], $comprobante['moneda']);

    $legends = [
        (new Legend())
            ->setCode('1000') // Monto en letras
            ->setValue($textoMonto)
    ];

    if (isset($comprobante['tiene_retencion']) && $comprobante['tiene_retencion'] == 1) {
        $legends[] = (new Legend())
            ->setCode('2006')
            ->setValue('Operación sujeta a retención del IGV');
    }

    $invoice->setDetails($greenItems)
        ->setLegends($legends);

    // Enviar a SUNAT
    $res = $see->send($invoice);

    $fileName = $invoice->getName(); // RUC-TIPO-SERIE-CORR
    $xmlPath = "../../../data/xml/{$fileName}.xml";
    file_put_contents($xmlPath, $see->getFactory()->getLastXml());

    if (!$res->isSuccess()) {
        // Error de negocio
        $err = $res->getError();
        $stmtUpdate = $pdo->prepare("UPDATE comprobantes SET estado_sunat = 'RECHAZADO', archivo_xml = ? WHERE id = ?");
        $stmtUpdate->execute(["{$fileName}.xml", $id]);

        echo json_encode(['error' => "{$err->getCode()}: {$err->getMessage()}"]);
        exit;
    }

    // Aceptado
    $cdrPath = "../../../data/cdr/R-{$fileName}.zip";
    file_put_contents($cdrPath, $res->getCdrZip());
    $cdr = $res->getCdrResponse();

    $estado = 'ACEPTADO';
    $codeCdr = (int)$cdr->getCode();
    if ($codeCdr === 0) {
        $estado = 'ACEPTADO';
    } elseif ($codeCdr >= 4000) {
        $estado = 'EXCEPCION';
    }

    $stmt2 = $pdo->prepare("UPDATE comprobantes SET estado_sunat = ?, archivo_xml = ?, archivo_cdr = ? WHERE id = ?");
    $stmt2->execute([$estado, "{$fileName}.xml", "R-{$fileName}.zip", $id]);

    echo json_encode(['success' => true, 'mensaje' => $cdr->getDescription(), 'estado' => $estado]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error Interno: ' . $e->getMessage()]);
}
?>
