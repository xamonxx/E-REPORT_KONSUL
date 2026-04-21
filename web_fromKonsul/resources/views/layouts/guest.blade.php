<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | E-REPORT</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#d97706",
                        "on-primary": "#ffffff",
                        "primary-container": "#fef3c7",
                        "on-surface": "#2b3437",
                        "on-surface-variant": "#586064",
                        "surface": "#f8f9fa",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e2e9ec",
                        "outline-variant": "#abb3b7",
                        "error": "#9f403d",
                        "tertiary": "#006d4a",
                    },
                    fontFamily: {
                        headline: ["Manrope", "sans-serif"],
                        body: ["Inter", "sans-serif"],
                    }
                }
            }
        }
    </script>
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-headline { font-family: 'Manrope', sans-serif; }

    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-amber-50/40 to-surface flex items-center justify-center p-4">
    @yield('content')
</body>
</html>
