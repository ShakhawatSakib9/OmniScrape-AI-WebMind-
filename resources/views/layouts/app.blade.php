<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniScrape AI — Autonomous Web Data & Self-Healing REST API Engine</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['Fira Code', 'monospace'],
                    },
                    colors: {
                        brand: {
                            500: '#00b4d8',
                            600: '#0096c7',
                        },
                        darkBg: '#090d16',
                        darkCard: '#111827',
                        glassBorder: 'rgba(255, 255, 255, 0.08)'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #080c14;
            background-image: 
                radial-gradient(at 0% 0%, rgba(0, 180, 216, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(114, 9, 183, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-btn {
            box-shadow: 0 0 20px rgba(0, 180, 216, 0.35);
        }
        .glow-btn:hover {
            box-shadow: 0 0 30px rgba(0, 180, 216, 0.6);
        }
    </style>
</head>
<body class="text-slate-100 font-sans min-h-screen flex flex-col antialiased">
    <header class="border-b border-glassBorder bg-darkCard/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-400 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-cyan-500/20">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">OmniScrape</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 uppercase">AI Engine</span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium tracking-wide">Autonomous Web Data & Self-Healing REST API</p>
                    </div>
                </a>
            </div>

            <nav class="hidden md:flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium hover:text-cyan-400 transition {{ request()->routeIs('dashboard') ? 'text-cyan-400 font-semibold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-layer-group mr-1.5"></i> Datasets
                </a>
                <a href="{{ route('projects.create') }}" class="text-sm font-medium hover:text-cyan-400 transition {{ request()->routeIs('projects.create') ? 'text-cyan-400 font-semibold' : 'text-slate-300' }}">
                    <i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i> New Scraper Agent
                </a>
            </nav>

            <div class="flex items-center space-x-3">
                <div class="hidden sm:flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Self-Healing: Operational</span>
                </div>
                <a href="{{ route('projects.create') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-medium text-xs sm:text-sm glow-btn transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Agent</span>
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-glassBorder bg-darkCard/40 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
            <div class="flex items-center space-x-2">
                <span><b>OmniScrape AI</b> — Engineered by Shakhawat Sakib</span>
                <span class="text-slate-600">•</span>
                <span class="text-cyan-400 font-mono">Laravel 12 + Playwright + Gemini 2.5</span>
            </div>
            <div class="flex items-center space-x-4 font-mono">
                <span class="text-emerald-400 flex items-center"><i class="fa-solid fa-shield-halved mr-1"></i> Auto-Recovery 99.4%</span>
                <span class="text-slate-500">|</span>
                <span>REST API v1.0</span>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>