<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Melichinkul'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/sweetalert-config.js') }}"></script>
    <style>
        /* Estilos para SweetAlert2 en modo oscuro - sin texto negro */
        .swal2-popup {
            @apply bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100;
        }
        .swal2-title {
            @apply text-gray-900 dark:text-white;
        }
        .swal2-content, .swal2-html-container {
            @apply text-gray-600 dark:text-gray-300;
        }
        .swal2-confirm {
            @apply bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600;
        }
        .swal2-cancel {
            @apply bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200;
        }
        .swal2-toast {
            @apply bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700;
        }
        /* Colores semánticos en modo oscuro: éxito=verde, error=rojo, advertencia=ámbar, info=azul */
        .dark .swal2-popup.swal2-success .swal2-title,
        .dark .swal2-popup.swal2-success .swal2-html-container { color: #86efac; }
        .dark .swal2-popup.swal2-error .swal2-title,
        .dark .swal2-popup.swal2-error .swal2-html-container { color: #fca5a5; }
        .dark .swal2-popup.swal2-warning .swal2-title,
        .dark .swal2-popup.swal2-warning .swal2-html-container { color: #fcd34d; }
        .dark .swal2-popup.swal2-question .swal2-title,
        .dark .swal2-popup.swal2-question .swal2-html-container { color: #93c5fd; }
        /* BADGES modo oscuro: forzar fondo y texto legibles (Correctivo, Preventivo, etc.) - solo elementos con rounded-full */
        html.dark [class*="rounded-full"][class*="bg-orange"],
        .dark [class*="rounded-full"][class*="bg-orange"] {
            background-color: #c2410c !important;
            color: #fed7aa !important;
        }
        html.dark [class*="rounded-full"][class*="bg-blue"],
        .dark [class*="rounded-full"][class*="bg-blue"] {
            background-color: #1d4ed8 !important;
            color: #bfdbfe !important;
        }
        html.dark [class*="rounded-full"][class*="bg-purple"],
        .dark [class*="rounded-full"][class*="bg-purple"] {
            background-color: #6d28d9 !important;
            color: #e9d5ff !important;
        }
        html.dark [class*="rounded-full"][class*="bg-green"],
        .dark [class*="rounded-full"][class*="bg-green"] {
            background-color: #15803d !important;
            color: #bbf7d0 !important;
        }
        html.dark [class*="rounded-full"][class*="bg-yellow"],
        .dark [class*="rounded-full"][class*="bg-yellow"] {
            background-color: #ca8a04 !important;
            color: #fef08a !important;
        }
        html.dark [class*="rounded-full"][class*="bg-amber"],
        .dark [class*="rounded-full"][class*="bg-amber"] {
            background-color: #b45309 !important;
            color: #fde68a !important;
        }
        html.dark [class*="rounded-full"][class*="bg-red"],
        .dark [class*="rounded-full"][class*="bg-red"] {
            background-color: #b91c1c !important;
            color: #fecaca !important;
        }
        html.dark [class*="rounded-full"][class*="bg-gray"],
        .dark [class*="rounded-full"][class*="bg-gray"] {
            background-color: #4b5563 !important;
            color: #e5e7eb !important;
        }
        /* Badges con "rounded" (pestañas Certificaciones y Alertas) - mismo tratamiento */
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-orange"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-orange"] {
            background-color: #c2410c !important;
            color: #fed7aa !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-blue"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-blue"] {
            background-color: #1d4ed8 !important;
            color: #bfdbfe !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-purple"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-purple"] {
            background-color: #6d28d9 !important;
            color: #e9d5ff !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-green"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-green"] {
            background-color: #15803d !important;
            color: #bbf7d0 !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-yellow"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-yellow"] {
            background-color: #ca8a04 !important;
            color: #fef08a !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-amber"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-amber"] {
            background-color: #b45309 !important;
            color: #fde68a !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-red"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-red"] {
            background-color: #b91c1c !important;
            color: #fecaca !important;
        }
        html.dark span[class*="inline-flex"][class*="rounded"][class*="bg-gray"],
        .dark span[class*="inline-flex"][class*="rounded"][class*="bg-gray"] {
            background-color: #4b5563 !important;
            color: #e5e7eb !important;
        }
        /* Pestaña Estadísticas: textos e iconos SIEMPRE legibles en modo oscuro */
        html.dark .vehicle-stats-tab .text-2xl,
        html.dark .vehicle-stats-tab .text-xl,
        html.dark .vehicle-stats-tab .text-sm,
        .dark .vehicle-stats-tab .text-2xl,
        .dark .vehicle-stats-tab .text-xl,
        .dark .vehicle-stats-tab .text-sm {
            color: #f9fafb !important;
        }
        html.dark .vehicle-stats-tab .text-xs,
        .dark .vehicle-stats-tab .text-xs {
            color: #d1d5db !important;
        }
        html.dark .vehicle-stats-tab i.fas,
        html.dark .vehicle-stats-tab i.far,
        html.dark .vehicle-stats-tab i.fab,
        .dark .vehicle-stats-tab i.fas,
        .dark .vehicle-stats-tab i.far,
        .dark .vehicle-stats-tab i.fab {
            color: #e5e7eb !important;
        }
        /* Pestaña Alertas: botón Posponer legible en modo oscuro */
        html.dark .vehicle-alerts-tab button[title="Posponer"],
        .dark .vehicle-alerts-tab button[title="Posponer"] {
            color: #fde047 !important;
        }
    </style>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <style>
        /* DataTables - modo oscuro: todo el texto claro, sin negro */
        .dataTables_wrapper {
            color: #111827;
        }
        
        .dark .dataTables_wrapper {
            color: #e5e7eb !important;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
        }
        
        .dark .dataTables_wrapper .dataTables_length,
        .dark .dataTables_wrapper .dataTables_filter,
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #e5e7eb !important;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            color: #111827;
        }
        
        .dark .dataTables_wrapper .dataTables_length select,
        .dark .dataTables_wrapper .dataTables_filter input {
            background-color: #1f2937 !important;
            border-color: #4b5563 !important;
            color: #f9fafb !important;
        }
        
        table.dataTable {
            width: 100%;
            border-collapse: collapse;
        }
        
        table.dataTable thead th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dark table.dataTable thead th {
            background-color: #111827 !important;
            color: #e5e7eb !important;
            border-bottom-color: #374151;
        }
        
        table.dataTable tbody td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #ffffff;
            color: #111827;
        }
        
        .dark table.dataTable tbody td {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
            border-bottom-color: #374151;
        }
        
        table.dataTable tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .dark table.dataTable tbody tr:hover {
            background-color: #374151;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 0.375rem;
            color: #374151;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #e5e7eb;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #f3f4f6;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #4b5563;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #4f46e5;
            color: #ffffff;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #6366f1;
            color: #ffffff;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .dataTables_wrapper .dataTables_processing {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .dark .dataTables_wrapper .dataTables_processing {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        /* Estilos para botones de acción con iconos */
        table.dataTable tbody td .fa-eye,
        table.dataTable tbody td .fa-edit,
        table.dataTable tbody td .fa-trash-alt {
            font-size: 1.125rem;
            padding: 0.375rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        table.dataTable tbody td .fa-eye:hover,
        table.dataTable tbody td .fa-edit:hover,
        table.dataTable tbody td .fa-trash-alt:hover {
            transform: scale(1.1);
        }
        
        /* Estilos para botones de DataTables */
        .dt-buttons {
            margin-bottom: 1rem;
        }
        
        .dt-buttons button {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .dt-buttons button:hover {
            background-color: #4338ca;
        }
        
        .dark .dt-buttons button {
            background-color: #6366f1;
            color: #ffffff;
        }
        
        .dark .dt-buttons button:hover {
            background-color: #818cf8;
        }
        
        /* Dropdown de columnas */
        .dt-button-collection {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .dark .dt-button-collection {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        .dt-button-collection button {
            background-color: transparent;
            color: #374151;
            padding: 0.5rem 1rem;
            width: 100%;
            text-align: left;
            border: none;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dt-button-collection button:hover {
            background-color: #f9fafb;
        }
        
        .dark .dt-button-collection button {
            color: #e5e7eb !important;
            border-bottom-color: #374151;
        }
        
        .dark .dt-button-collection button:hover {
            background-color: #374151 !important;
        }
        
        /* Ajustar layout de DataTables con botones */
        .dataTables_wrapper .dt-buttons {
            float: left;
            margin-bottom: 1rem;
        }
        
        .dataTables_wrapper .dataTables_length {
            float: left;
            margin-right: 1rem;
        }
        
        .dataTables_wrapper .dataTables_filter {
            float: right;
        }
        
        .dataTables_wrapper::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
    <script>
        // Detectar preferencia de modo oscuro del sistema
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex flex-col h-full">
                <!-- Logo y Header -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img id="logo-light" src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Melichinkul') }}" class="h-24 w-auto">
                        <img id="logo-dark" src="{{ asset('images/logo-dark.png') }}" alt="{{ config('app.name', 'Melichinkul') }}" class="h-24 w-auto hidden">
                    </a>
                    <button id="sidebar-close" class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navegación -->
                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('vehiculos.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('vehiculos.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Vehículos
                    </a>

                    <a href="{{ route('mantenimientos.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('mantenimientos.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Mantenimientos
                    </a>

                    <a href="{{ route('conductores.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('conductores.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Conductores
                    </a>

                    <a href="{{ route('alerts.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('alerts.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Alertas
                    </a>

                    <a href="{{ route('repuestos.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('repuestos.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Repuestos
                    </a>

                    <a href="{{ route('proveedores.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('proveedores.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Proveedores
                    </a>

                    <a href="{{ route('compras.index') }}" 
                       class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('compras.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Compras
                    </a>
                </nav>

                <!-- Footer del Sidebar -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-indigo-500 dark:bg-indigo-600 flex items-center justify-center text-white text-sm font-medium">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst(Auth::user()->rol) }}</p>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Overlay para móvil -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden hidden"></div>

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
            <!-- Header superior -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16 flex items-center justify-between px-4 lg:px-6">
                <button id="sidebar-toggle" class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div class="flex items-center space-x-4">
                    <!-- Toggle modo oscuro -->
                    <button id="theme-toggle" type="button" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 cursor-pointer z-50 relative">
                        <svg id="theme-icon-light" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="theme-icon-dark" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Contenido -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 lg:p-6">
                <!-- Los mensajes flash ahora se muestran con SweetAlert2, no necesitamos estos divs -->

                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    <!-- Interceptar wire:confirm de Livewire para usar SweetAlert -->
    <script>
        // Interceptar wire:confirm cuando Livewire actualiza el DOM
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                setTimeout(() => {
                    el.querySelectorAll('[wire\\:confirm]:not([data-swal-converted])').forEach(element => {
                        const originalConfirm = element.getAttribute('wire:confirm');
                        const wireClick = element.getAttribute('wire:click');
                        
                        if (originalConfirm && wireClick) {
                            element.removeAttribute('wire:confirm');
                            element.setAttribute('data-swal-converted', 'true');
                            
                            element.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                swalConfirmDelete('¿Estás seguro?', originalConfirm, 'Sí, eliminar')
                                    .then((result) => {
                                        if (result.isConfirmed) {
                                            const match = wireClick.match(/delete\((\d+)\)/);
                                            if (match) {
                                                const id = parseInt(match[1]);
                                                component.call('delete', id);
                                            }
                                        }
                                    });
                            });
                        }
                    });
                }, 100);
            });
        });

        // Interceptar wire:confirm en elementos existentes al cargar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelectorAll('[wire\\:confirm]:not([data-swal-converted])').forEach(element => {
                    const originalConfirm = element.getAttribute('wire:confirm');
                    const wireClick = element.getAttribute('wire:click');
                    
                    if (originalConfirm && wireClick) {
                        element.removeAttribute('wire:confirm');
                        element.setAttribute('data-swal-converted', 'true');
                        
                        element.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            swalConfirmDelete('¿Estás seguro?', originalConfirm, 'Sí, eliminar')
                                .then((result) => {
                                    if (result.isConfirmed) {
                                        const wireId = element.closest('[wire\\:id]')?.getAttribute('wire:id');
                                        if (wireId) {
                                            const component = Livewire.find(wireId);
                                            if (component) {
                                                const match = wireClick.match(/delete\((\d+)\)/);
                                                if (match) {
                                                    const id = parseInt(match[1]);
                                                    component.call('delete', id);
                                                }
                                            }
                                        }
                                    }
                                });
                        });
                    }
                });
            }, 500);
        });
    </script>
    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <!-- DataTables Config debe cargarse después de DataTables pero antes de @stack('scripts') -->
    <script src="{{ asset('js/datatables-config.js') }}"></script>
    @stack('scripts')
    <script>
        // Mostrar mensajes flash de Laravel con SweetAlert2
        @if(session('success'))
            swalSuccess('{{ session('success') }}');
        @endif

        @if(session('error'))
            swalError('{{ session('error') }}');
        @endif

        @if(session('warning'))
            swalWarning('{{ session('warning') }}');
        @endif

        @if(session('info'))
            swalInfo('{{ session('info') }}');
        @endif
    </script>
    <script>
        // Función para actualizar logos según el modo
        function updateLogos() {
            const isDark = document.documentElement.classList.contains('dark');
            const logoLight = document.getElementById('logo-light');
            const logoDark = document.getElementById('logo-dark');
            
            if (logoLight && logoDark) {
                if (isDark) {
                    logoLight.classList.add('hidden');
                    logoDark.classList.remove('hidden');
                } else {
                    logoLight.classList.remove('hidden');
                    logoDark.classList.add('hidden');
                }
            }
        }

        // Toggle modo oscuro - ejecutar inmediatamente
        (function() {
            function initThemeToggle() {
                const themeToggle = document.getElementById('theme-toggle');
                if (!themeToggle) {
                    console.warn('Theme toggle button not found');
                    return;
                }
                
                // Remover cualquier listener previo
                themeToggle.onclick = null;
                
                themeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const html = document.documentElement;
                    const isDark = html.classList.contains('dark');
                    
                    if (isDark) {
                        html.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        html.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                    
                    updateLogos(); // Actualizar logos
                    console.log('Theme toggled to:', isDark ? 'light' : 'dark');
                });
                
                // Agregar estilo de cursor
                themeToggle.style.cursor = 'pointer';
            }
            
            // Actualizar logos al cargar
            updateLogos();
            
            // Observar cambios en la clase dark
            const observer = new MutationObserver(updateLogos);
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
            
            // Intentar inicializar inmediatamente
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initThemeToggle);
            } else {
                initThemeToggle();
            }
        })();

        // Resto del código cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar en móvil
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarClose = document.getElementById('sidebar-close');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            function openSidebar() {
                if (sidebar) {
                    sidebar.classList.remove('-translate-x-full');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('hidden');
                }
            }

            function closeSidebar() {
                if (sidebar) {
                    sidebar.classList.add('-translate-x-full');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('hidden');
                }
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', openSidebar);
            }
            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }

            // Cerrar sidebar al hacer clic en un enlace (móvil)
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });
        });
    </script>
</body>
</html>
