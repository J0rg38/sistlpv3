<?php
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_departamentos':
        $file = __DIR__ . '/departamentos.json';
        if (file_exists($file)) {
            echo file_get_contents($file);
        } else {
            echo json_encode([]);
        }
        break;

    case 'get_provincias':
        $department_id = $_GET['department_id'] ?? '';
        $file = __DIR__ . '/provincias.json';
        $result = [];
        if (file_exists($file) && $department_id) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data[$department_id])) {
                $result = $data[$department_id];
            }
        }
        echo json_encode($result);
        break;

    case 'get_distritos':
        $province_id = $_GET['province_id'] ?? '';
        $file = __DIR__ . '/distritos.json';
        $result = [];
        if (file_exists($file) && $province_id) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data[$province_id])) {
                $result = $data[$province_id];
            }
        }
        echo json_encode($result);
        break;

    default:
        echo json_encode(['error' => 'Accion no valida']);
        break;
}
