<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth_helpers.php';
$base_url = isset($base_url) ? $base_url : ''; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "index.php");
    exit;
}

$nombreCompleto = htmlspecialchars($_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos']);
$rol = htmlspecialchars($_SESSION['user_rol']);
$iniciales = strtoupper(substr($_SESSION['user_nombre'], 0, 1) . substr($_SESSION['user_apellidos'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SisTLP v3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Sidebar mobile backdrop -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-20 bg-gray-900/50 backdrop-blur-sm transition-opacity md:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#F8FAFC]">
            
            <?php include __DIR__ . '/topbar.php'; ?>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto focus:outline-none">
                <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
