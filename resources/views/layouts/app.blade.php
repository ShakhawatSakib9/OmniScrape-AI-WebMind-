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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                            950: '#080d1a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0b1120;
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .ui-card {
            background-color: #111827;
            border: 1px solid #1f293d;
        }
        .ui-input {
            background-color: #0b1120;
            border: 1px solid #28354d;
            color: #ffffff;
        }
        .ui-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex bg-[#0b1120] text-slate-100 antialiased">
    
    <!-- Left Navigation Sidebar (Full Height Desktop) -->
    <aside class="w-64 bg-[#0f172a] border-r border-[#1e293b] flex-shrink-0 flex flex-col justify-between hidden md:flex min-h-screen">
        <div>
            <!-- Logo & Brand Header -->
            <div class="h-16 px-6 flex items-center gap-3 border-b border-[#1e293b]">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white text-base font-extrabold shadow-md">
                    <i class="fa-solid fa-cube"></i>
                </div>
                <div>
                    <span class="font-bold text-base text-white tracking-tight">OmniScrape</span>
                    <span class="block text-[11px] text-slate-400 font-mono">Autonomous AI Data</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="p-4 space-y-1.5 font-medium text-sm">
                <div class="px-3 py-1.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider font-mono">
                    Core Platform
                </div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-layer-group text-sm w-4 text-center"></i>
                    <span>Datasets & Pipelines</span>
                </a>
                <a href="{{ route('projects.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('projects.create') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-plus text-sm w-4 text-center"></i>
                    <span>Create Agent</span>
                </a>

                <div class="pt-4 px-3 py-1.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider font-mono">
                    Engine & Services
                </div>
                <div class="flex items-center justify-between px-3.5 py-2 rounded-lg text-slate-400 text-sm">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-shield-heart text-sm w-4 text-amber-400 text-center"></i>
                        <span>Self-Healing</span>
                    </span>
                    <span class="text-[11px] px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 font-mono font-bold border border-emerald-800/50">Online</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2 rounded-lg text-slate-400 text-sm">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-network-wired text-sm w-4 text-blue-400 text-center"></i>
                        <span>Proxy Pool</span>
                    </span>
                    <span class="text-[11px] px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono">Active</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2 rounded-lg text-slate-400 text-sm">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-bolt text-sm w-4 text-cyan-400 text-center"></i>
                        <span>REST Endpoints</span>
                    </span>
                    <span class="text-[11px] px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono">v1.0</span>
                </div>
            </div>
        </div>

        <!-- Sidebar Footer Status -->
        <div class="p-4 border-t border-[#1e293b] space-y-3">
            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 space-y-1">
                <div class="flex items-center justify-between text-slate-200 font-semibold">
                    <span>Engine Runtime</span>
                    <span class="text-emerald-400 font-mono">99.8%</span>
                </div>
                <div>Playwright + Gemini 2.5 Ingestion</div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area (Full Screen Width) -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navbar -->
        <header class="h-16 bg-[#0f172a] border-b border-[#1e293b] px-6 sm:px-8 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-4">
                <!-- Mobile Logo -->
                <a href="{{ route('dashboard') }}" class="md:hidden flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                        <i class="fa-solid fa-cube"></i>
                    </div>
                </a>
                <div class="font-semibold text-base text-white">
                    @yield('title', 'Autonomous Data Operations')
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('projects.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>New Scraper</span>
                </a>
            </div>
        </header>

        <!-- Flash Alert Messages -->
        <div class="px-6 sm:px-8 pt-4">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-200 text-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-800/60 text-rose-200 text-sm flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>

        <!-- Page Dynamic Body -->
        <main class="flex-1 p-6 sm:p-8 space-y-8">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>