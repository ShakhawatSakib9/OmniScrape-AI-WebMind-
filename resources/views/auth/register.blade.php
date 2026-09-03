<!DOCTYPE html>
<html lang="en" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Workspace — OmniScrape AI Engine</title>
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
            <h1 class="text-2xl font-bold text-white tracking-tight">Create Organization Workspace</h1>
            <p class="text-sm text-slate-400">Deploy your private autonomous scraper fleet</p>
        </div>

        <!-- Registration Card -->
        <div class="ui-card rounded-2xl p-8 space-y-6 shadow-xl">
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

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2">
                        Full Name / Organization
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Acme Data Labs" class="w-full px-4 py-3 rounded-xl ui-input text-sm text-white placeholder:text-slate-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2">
                        Work Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="alex@company.io" class="w-full px-4 py-3 rounded-xl ui-input text-sm text-white placeholder:text-slate-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2">
                        Secret Password
                    </label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl ui-input text-sm text-white placeholder:text-slate-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2">
                        Confirm Password
                    </label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl ui-input text-sm text-white placeholder:text-slate-500 transition">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2 mt-4">
                    <span>Create & Launch Workspace</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-slate-400">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-blue-400 font-bold hover:underline">
                Sign In
            </a>
        </div>
    </div>
</body>
</html>
