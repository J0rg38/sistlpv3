<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$rango = $_GET['rango'] ?? 'mes';

$hoy = date('Y-m-d');
$fecha_inicio = $hoy;

switch ($rango) {
    case 'hoy':
        $fecha_inicio = $hoy;
        break;
    case 'semana':
        $fecha_inicio = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'mes':
        $fecha_inicio = date('Y-m-01');
        break;
    case 'mes_anterior':
        $fecha_inicio = date('Y-m-01', strtotime('first day of last month'));
        $hoy = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'anio':
        $fecha_inicio = date('Y-01-01');
        break;
    default:
        $fecha_inicio = date('Y-m-01');
}

try {
    $stmtTotales = $pdo->prepare("
        SELECT 
            moneda,
            tipo_comprobante,
            COUNT(id) as cantidad,
            SUM(total) as monto_total
        FROM comprobantes 
        WHERE fecha_emision BETWEEN ? AND ? 
        AND estado_sunat = 'ACEPTADO'
        GROUP BY moneda, tipo_comprobante
    ");
    $stmtTotales->execute([$fecha_inicio, $hoy]);
    $totalesBrutos = $stmtTotales->fetchAll(PDO::FETCH_ASSOC);

    $kpi = [
        'ventas_pen' => 0,
        'ventas_usd' => 0,
        'volumen_total' => 0,
        'ticket_promedio_pen' => 0
    ];

    $facturas_n = 0;
    $boletas_n = 0;

    foreach ($totalesBrutos as $t) {
        $monto = (float)$t['monto_total'];
        $cant = (int)$t['cantidad'];
        $kpi['volumen_total'] += $cant;

        if ($t['tipo_comprobante'] === 'FACTURA') $facturas_n += $cant;
        if ($t['tipo_comprobante'] === 'BOLETA') $boletas_n += $cant;

        // Nota de Credito resta
        $factor = ($t['tipo_comprobante'] === 'NOTA_CREDITO') ? -1 : 1;

        if ($t['moneda'] === 'PEN') {
            $kpi['ventas_pen'] += $monto * $factor;
        } else {
            $kpi['ventas_usd'] += $monto * $factor;
        }
    }

    if ($kpi['volumen_total'] > 0) {
        $vol_ventas_reales = $facturas_n + $boletas_n;
        if($vol_ventas_reales > 0) {
            $kpi['ticket_promedio_pen'] = $kpi['ventas_pen'] / $vol_ventas_reales;
        }
    }

    $stmtEvolucion = $pdo->prepare("
        SELECT 
            fecha_emision as fecha,
            moneda,
            SUM(CASE WHEN tipo_comprobante = 'NOTA_CREDITO' THEN -total ELSE total END) as venta_diaria 
        FROM comprobantes
        WHERE fecha_emision BETWEEN ? AND ?
        AND estado_sunat = 'ACEPTADO'
        GROUP BY fecha_emision, moneda
        ORDER BY fecha_emision ASC
    ");
    $stmtEvolucion->execute([$fecha_inicio, $hoy]);
    $evolucionBruta = $stmtEvolucion->fetchAll(PDO::FETCH_ASSOC);

    $graficoLineas = [
        'fechas' => [],
        'series_pen' => [],
        'series_usd' => []
    ];

    $mapEvolucion = [];
    $d = new DateTime($fecha_inicio);
    $limit = new DateTime($hoy);
    while ($d <= $limit) {
        $fst = $d->format('Y-m-d');
        $mapEvolucion[$fst] = ['PEN' => 0, 'USD' => 0];
        $d->modify('+1 day');
    }

    foreach($evolucionBruta as $ev) {
        $mapEvolucion[$ev['fecha']][$ev['moneda']] += (float)$ev['venta_diaria'];
    }

    foreach($mapEvolucion as $f => $vals) {
        $graficoLineas['fechas'][] = date('d M', strtotime($f));
        $graficoLineas['series_pen'][] = round($vals['PEN'], 2);
        $graficoLineas['series_usd'][] = round($vals['USD'], 2);
    }

    // Recientes
    $stmtRecientes = $pdo->prepare("
        SELECT c.id, c.fecha_emision, c.tipo_comprobante, c.serie, c.correlativo, 
               c.moneda, c.total, c.estado_sunat, cl.razon_social, cl.nombres, cl.apellidos, cl.tipo_cliente 
        FROM comprobantes c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        ORDER BY c.fecha_emision DESC, c.id DESC LIMIT 10
    ");
    $stmtRecientes->execute();
    $recientes = $stmtRecientes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'kpi' => $kpi,
        'grafico_pie' => [
            'facturas' => $facturas_n,
            'boletas' => $boletas_n
        ],
        'grafico_lineas' => $graficoLineas,
        'recientes' => $recientes
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
