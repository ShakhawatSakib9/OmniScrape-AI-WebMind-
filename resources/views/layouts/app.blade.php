<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniScrape — Autonomous Web Data & Self-Healing Infrastructure</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        surface: {
                            base: '#0B0F17',
                            card: '#111827',
                            elevated: '#172033',
                            border: '#1E293B',
                            hover: '#1E293B'
                        },
                        brand: {
                            primary: '#3B82F6',
                            primaryHover: '#2563EB',
                            accent: '#6366F1'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0B0F17;
            color: #F8FAFC;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.011em;
        }
        .enterprise-card {
            background-color: #111827;
            border: 1px solid #1E293B;
        }
        .enterprise-input {
            background-color: #0D131F;
            border: 1px solid #1E293B;
            color: #F8FAFC;
        }
        .enterprise-input:focus {
            border-color: #3B82F6;
            outline: none;
            box-shadow: 0 0 0 1px #3B82F6;
        }
        /* Custom subtle scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0B0F17;
        }
        ::-webkit-scrollbar-thumb {
            background: #1E293B;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Clean Minimalist Top Navbar -->
    <header class="border-b border-surface-border bg-surface-card/90 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        <i class="fa-solid fa-cube"></i>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="font-semibold text-sm tracking-tight text-white">OmniScrape</span>
                        <span class="text-xs px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-mono text-[10px] border border-slate-700">v2.1</span>
                    </div>
                </a>

                <div class="h-4 w-px bg-slate-800 hidden md:block"></div>

                <nav class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'text-white font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                        Datasets
                    </a>
                    <a href="{{ route('projects.create') }}" class="text-xs font-medium transition {{ request()->routeIs('projects.create') ? 'text-white font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                        New Agent
                    </a>
                </nav>
            </div>

            <div class="flex items-center space-x-3">
                <div class="hidden sm:flex items-center space-x-2 px-2.5 py-1 rounded-md bg-emerald-950/40 border border-emerald-800/40 text-emerald-400 text-xs font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px]">System Online</span>
                </div>

                <a href="{{ route('projects.create') }}" class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-950 font-medium text-xs shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Create Agent</span>
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-3.5 rounded-lg bg-emerald-950/30 border border-emerald-800/40 text-emerald-300 text-xs flex items-center space-x-2.5">
                <i class="fa-solid fa-circle-check text-sm text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-3.5 rounded-lg bg-rose-950/30 border border-rose-800/40 text-rose-300 text-xs flex items-center space-x-2.5">
                <i class="fa-solid fa-triangle-exclamation text-sm text-rose-400"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <footer class="border-t border-surface-border bg-surface-card/40 py-5 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-slate-400 font-medium">OmniScrape Engine</span>
                <span>•</span>
                <span>Laravel 12 / Playwright / Gemini 2.5</span>
            </div>
            <div class="flex items-center space-x-4 font-mono text-[11px]">
                <span class="text-slate-400">Self-Healing Watchdog v2.1</span>
                <span>•</span>
                <span class="text-slate-400">REST API v1.0</span>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>