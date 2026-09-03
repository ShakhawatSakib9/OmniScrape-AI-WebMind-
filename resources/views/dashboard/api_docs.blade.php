@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="pb-2 border-b border-glassBorder flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left"></i> Back to {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-white mt-1 flex items-center gap-2">
                <i class="fa-solid fa-code text-cyan-400"></i>
                REST API Explorer & Documentation
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">Integrate this live dataset into your apps using standard HTTP GET requests.</p>
        </div>
    </div>

    <!-- Endpoint URL Card -->
    <div class="glass-card rounded-2xl p-6 space-y-4">
        <label class="block text-xs font-mono font-bold text-slate-300 uppercase tracking-wider">
            Public REST Endpoint
        </label>
        <div class="flex items-center gap-2">
            <span class="px-3 py-2 rounded-xl bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs border border-emerald-500/30">
                GET
            </span>
            <input type="text" id="api-url" readonly value="{{ route('api.datasets.show', $project->slug) }}" class="flex-grow px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-cyan-300 font-mono text-sm">
            <button onclick="copyApiUrl()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium text-xs font-mono transition flex items-center gap-1.5">
                <i class="fa-regular fa-copy"></i> Copy URL
            </button>
            <a href="{{ route('api.datasets.show', $project->slug) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Live
            </a>
        </div>
    </div>

    <!-- Query Parameters Documentation -->
    <div class="glass-card rounded-2xl p-6 space-y-4">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-filter text-cyan-400"></i> Supported Filter & Query Parameters
        </h2>
        
        <div class="overflow-x-auto rounded-xl border border-glassBorder">
            <table class="w-full text-left text-xs font-mono text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase border-b border-glassBorder">
                    <tr>
                        <th class="p-3">Parameter</th>
                        <th class="p-3">Example Usage</th>
                        <th class="p-3">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-glassBorder">
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3 font-bold text-cyan-400">search</td>
                        <td class="p-3 text-slate-200">?search=einstein</td>
                        <td class="p-3 text-slate-400">Full-text search across all extracted record fields.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3 font-bold text-cyan-400">filter[field_name]</td>
                        <td class="p-3 text-slate-200">?filter[author]=Albert Einstein</td>
                        <td class="p-3 text-slate-400">Exact match filter on any specific schema field.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3 font-bold text-cyan-400">filter[price_min]</td>
                        <td class="p-3 text-slate-200">?filter[price_min]=50</td>
                        <td class="p-3 text-slate-400">Numeric comparison filtering for minimum amount.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3 font-bold text-cyan-400">sort</td>
                        <td class="p-3 text-slate-200">?sort=-id or ?sort=price</td>
                        <td class="p-3 text-slate-400">Sort results ascending or descending (with minus prefix).</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3 font-bold text-cyan-400">per_page</td>
                        <td class="p-3 text-slate-200">?per_page=50</td>
                        <td class="p-3 text-slate-400">Number of items per page (default: 20, max: 100).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyApiUrl() {
    const copyText = document.getElementById("api-url");
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    alert("API URL copied to clipboard!");
}
</script>
@endsection