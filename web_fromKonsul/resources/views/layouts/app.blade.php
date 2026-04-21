<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | E-REPORT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php $isSidebarOpen = request()->cookie('sidebar_open', 'false') === 'true'; @endphp
<body class="bg-surface text-on-surface selection:bg-primary-container selection:text-primary"
      data-cities-url="{{ route('api.wilayah.kota') }}"
      data-preview-consultation-id-url="{{ route('api.consultation-id-preview') }}"
      data-consultations-base-url="{{ url('consultations') }}"
      data-master-data-users-base-url="{{ url('master-data/users') }}"
      style="{{ \App\Support\ThemePalette::inlineCssVariables(auth()->user()?->primary_color) }}"
      x-data="appShell({{ $isSidebarOpen ? 'true' : 'false' }})"
      x-init="init()"
      @resize.window="handleResize()">

    <div x-show="sidebarOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden">
    </div>

    <div class="flex min-h-screen lg:h-screen relative overflow-hidden">
        <div class="fixed inset-y-0 left-0 z-50 lg:static transition-all duration-300 ease-in-out shrink-0 overflow-hidden {{ $isSidebarOpen ? 'max-lg:-translate-x-full lg:translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-[5.5rem] w-64' }}"
             :class="sidebarOpen ? '!translate-x-0 !w-64' : '!-translate-x-full lg:!translate-x-0 lg:!w-[5.5rem] !w-64'">
            @include('components.sidebar')
        </div>

        <main class="flex-1 flex flex-col min-w-0 bg-surface-container-low w-full overflow-y-auto">
            @include('components.header')

            @if(session('success'))
            <div class="toast-container" id="toast-success">
                <div class="toast bg-tertiary-container text-on-tertiary-container px-4 sm:px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 mx-4 sm:mx-0">
                    <x-icon name="check_circle" class="w-5 h-5 text-tertiary" />
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="toast-container" id="toast-error">
                <div class="toast bg-error-container text-on-error-container px-4 sm:px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 mx-4 sm:mx-0">
                    <x-icon name="error" class="w-5 h-5 text-error" />
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            </div>
            @endif

            <div class="p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
