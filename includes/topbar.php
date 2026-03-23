<?php
$base = isset($base_url) ? $base_url : '';
?>
<!-- Topbar -->
<header class="h-16 shrink-0 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 w-full">
    <!-- Mobile menu button -->
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none md:hidden p-2 -ml-2 rounded-md hover:bg-gray-100 transition-colors">
        <i class="fas fa-bars text-xl"></i>
    </button>
    
    <!-- Search (Desktop only) -->
    <div class="flex-1 hidden md:flex">
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" placeholder="Buscar en el sistema..." class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors sm:text-sm">
        </div>
    </div>

    <div class="flex items-center space-x-3 sm:space-x-5">
        <!-- Notifications -->
        <button class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none rounded-full hover:bg-gray-100 transition-colors">
            <span class="sr-only">Ver notificaciones</span>
            <i class="far fa-bell text-xl"></i>
            <span class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>

        <!-- Profile Dropdown -->
        <div x-data="{ dropdownOpen: false }" class="relative shrink-0">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center focus:outline-none border-2 border-transparent rounded-full focus:border-gray-200 transition-colors">
                <img class="h-9 w-9 rounded-full object-cover border border-gray-200 shadow-sm" src="https://ui-avatars.com/api/?name=<?= urlencode($nombreCompleto) ?>&background=4F46E5&color=fff&rounded=true&bold=true" alt="Avatar">
                <i class="fas fa-chevron-down ml-2 text-[10px] text-gray-400 hidden sm:block"></i>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900 truncate"><?= $nombreCompleto ?></p>
                    <p class="text-xs font-medium text-gray-500 truncate mt-0.5"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                </div>
                
                <div class="py-1 border-t border-gray-100">
                    <a href="<?= $base ?>logout.php" class="group flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                        <i class="fas fa-sign-out-alt mr-3 text-red-400 group-hover:text-red-500"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
