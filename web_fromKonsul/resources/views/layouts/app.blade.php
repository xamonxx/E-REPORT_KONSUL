<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | E-REPORT</title>

    {{-- TailwindCSS CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Alpine.js CDN --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- SweetAlert2 CDN (deferred — not needed for initial render) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Google Fonts (preconnect + swap for faster loading) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>


    {{-- Tailwind Config --}}
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-fixed": "#2a13c5",
                        "on-primary": "#faf6ff",
                        "secondary-fixed-dim": "#c5d6f0",
                        "error-container": "#fe8983",
                        "secondary-dim": "#44546a",
                        "surface-dim": "#d1dce0",
                        "on-secondary-container": "#435368",
                        "on-background": "#2b3437",
                        "tertiary-dim": "#005f40",
                        "secondary": "#506076",
                        "surface-container-highest": "#dbe4e7",
                        "inverse-on-surface": "#9b9d9e",
                        "primary": "#4d44e3",
                        "on-secondary": "#f7f9ff",
                        "background": "#f8f9fa",
                        "on-secondary-fixed": "#314055",
                        "error-dim": "#4e0309",
                        "on-tertiary-container": "#005a3c",
                        "tertiary-fixed-dim": "#58e7ab",
                        "on-secondary-fixed-variant": "#4d5d73",
                        "on-tertiary-fixed-variant": "#006544",
                        "on-primary-fixed-variant": "#4a40e0",
                        "surface-container-low": "#f1f4f6",
                        "outline": "#737c7f",
                        "on-primary-container": "#3f33d6",
                        "surface-variant": "#dbe4e7",
                        "surface": "#f8f9fa",
                        "on-tertiary": "#e6ffee",
                        "primary-container": "#e2dfff",
                        "surface-container-lowest": "#ffffff",
                        "tertiary": "#006d4a",
                        "inverse-primary": "#8582ff",
                        "secondary-fixed": "#d3e4fe",
                        "secondary-container": "#d3e4fe",
                        "primary-dim": "#4034d7",
                        "error": "#9f403d",
                        "inverse-surface": "#0c0f10",
                        "on-error-container": "#752121",
                        "on-tertiary-fixed": "#00452d",
                        "surface-tint": "#4d44e3",
                        "surface-container": "#eaeff1",
                        "primary-fixed": "#e2dfff",
                        "surface-container-high": "#e2e9ec",
                        "tertiary-fixed": "#69f6b8",
                        "primary-fixed-dim": "#d2d0ff",
                        "on-surface": "#2b3437",
                        "tertiary-container": "#69f6b8",
                        "on-error": "#fff7f6",
                        "surface-bright": "#f8f9fa",
                        "outline-variant": "#abb3b7",
                        "on-surface-variant": "#586064"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    fontFamily: {
                        headline: ["Manrope", "sans-serif"],
                        body: ["Inter", "sans-serif"],
                        label: ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>

    {{-- Custom CSS --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet"/>

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-headline { font-family: 'Manrope', sans-serif; }
    </style>
</head>
@php $isSidebarOpen = request()->cookie('sidebar_open', 'false') === 'true'; @endphp
<body class="bg-surface text-on-surface selection:bg-primary-container selection:text-primary"
      x-data="{ 
          isMobile: window.innerWidth < 1024,
          sidebarOpen: window.innerWidth >= 1024 ? {{ $isSidebarOpen ? 'true' : 'false' }} : false 
      }"
      x-init="$watch('sidebarOpen', val => { 
          if (!isMobile) document.cookie = 'sidebar_open=' + val + '; path=/; max-age=31536000'; 
      })"
      @resize.window="isMobile = window.innerWidth < 1024; if(isMobile) sidebarOpen = false">

    {{-- Mobile Sidebar Overlay --}}
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

    <div class="flex h-screen relative overflow-hidden">

        {{-- Sidebar (desktop: static, mobile: drawer) --}}
        <div class="fixed inset-y-0 left-0 z-50 lg:static transition-all duration-300 ease-in-out shrink-0 overflow-hidden {{ $isSidebarOpen ? 'max-lg:-translate-x-full lg:translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-[5.5rem] w-64' }}"
             :class="sidebarOpen ? '!translate-x-0 !w-64' : '!-translate-x-full lg:!translate-x-0 lg:!w-[5.5rem] !w-64'">
            @include('components.sidebar')
        </div>

        {{-- Main Content --}}
        <main class="flex-1 flex flex-col min-w-0 bg-surface-container-low w-full overflow-y-scroll">

            {{-- Header --}}
            @include('components.header')

            {{-- Flash Messages --}}
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

            {{-- Page Content --}}
            <div class="p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Toast auto-dismiss --}}
    <script>
        setTimeout(() => {
            document.querySelectorAll('.toast-container').forEach(el => el.remove());
        }, 3000);
    </script>

    @stack('scripts')
</body>
</html>
