<!DOCTYPE html>
<html lang="en" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — OmniScrape AI Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #0b1120;
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .ui-card {
            background-color: #0f172a;
            border: 1px solid #1e293b;
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
<body class="min-h-full flex items-center justify-center p-6 bg-[#0b1120]">
    <div class="max-w-md w-full space-y-6">
        <!-- Logo Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-xl font-extrabold mx-auto shadow-lg shadow-blue-600/20">
                <i class="fa-solid fa-cube"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">OmniScrape Cloud</h1>
            <p class="text-sm text-slate-400">Autonomous Web Data & Self-Healing Infrastructure</p>
        </div>

        <!-- Login Card -->
        <div class="ui-card rounded-2xl p-8 space-y-6 shadow-xl">
            @if(session('success'))
                <div class="p-3 rounded-lg bg-emerald-950/50 border border-emerald-800 text-emerald-300 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3 rounded-lg bg-rose-950/50 border border-rose-800 text-rose-300 text-xs space-y-1">
                    @foreach($errors->all() as $err)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email', 'sakib@omniscrape.io') }}" required autofocus class="w-full px-4 py-3 rounded-xl ui-input text-sm text-white placeholder:text-slate-500 transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono">
                            Password
                        </label>
                    </div>
                    <input type="password" name="password" value="password" required class="w-full px-4 py-3 rounded-xl ui-input text-sm text-white placeholder:text-slate-500 transition">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" checked class="rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-500">
                        <span>Remember session</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2 mt-2">
                    <span>Sign In to Workspace</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Demo Credentials Helper Box -->
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5 text-xs">
                <div class="font-bold text-slate-300 flex items-center justify-between">
                    <span>Demo Credentials:</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-blue-950 text-blue-300 font-mono font-bold">Auto-Filled</span>
                </div>
                <div class="text-slate-400 font-mono">
                    <div>Email: <b class="text-white">sakib@omniscrape.io</b></div>
                    <div>Password: <b class="text-white">password</b></div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-400">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-blue-400 font-bold hover:underline">
                Create Workspace
            </a>
        </div>
    </div>
</body>
</html>
