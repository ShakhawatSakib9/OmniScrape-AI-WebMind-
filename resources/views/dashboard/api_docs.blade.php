@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="pb-3 border-b border-surface-border">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-white transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to {{ $project->name }}
            </a>
        </div>
        <h1 class="text-xl font-semibold text-white tracking-tight mt-1">
            REST API Reference
        </h1>
        <p class="text-slate-400 text-xs mt-0.5">Integrate this normalized dataset into external applications via standard HTTP GET queries.</p>
    </div>

    <!-- Endpoint URL Box -->
    <div class="enterprise-card rounded-xl p-4 space-y-3">
        <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider font-mono">
            Dataset Endpoint URL
        </span>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1.5 rounded-md bg-emerald-950 text-emerald-400 font-mono font-medium text-xs border border-emerald-800">
                GET
            </span>
            <input type="text" id="api-url" readonly value="{{ route('api.datasets.show', $project->slug) }}" class="flex-grow px-3.5 py-2 rounded-lg enterprise-input text-xs font-mono text-slate-200">
            <button onclick="copyApiUrl()" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs transition border border-slate-700 flex items-center gap-1.5">
                <i class="fa-regular fa-copy text-[11px]"></i> Copy
            </button>
            <a href="{{ route('api.datasets.show', $project->slug) }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition flex items-center gap-1.5">
                <span>Open Raw JSON</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
            </a>
        </div>
    </div>

    <!-- Query Parameters Documentation Table -->
    <div class="enterprise-card rounded-xl overflow-hidden">
        <div class="p-4 border-b border-surface-border">
            <h2 class="text-sm font-semibold text-white">Supported Query Parameters</h2>
            <p class="text-xs text-slate-400 mt-0.5">Dynamic query filtering, pagination, and sorting arguments.</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono text-slate-300">
                <thead class="bg-slate-900/60 text-slate-400 font-medium border-b border-surface-border text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Parameter</th>
                        <th class="py-3 px-4">Example Request</th>
                        <th class="py-3 px-4">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border font-sans">
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3 px-4 font-mono font-medium text-blue-400">search</td>
                        <td class="py-3 px-4 font-mono text-slate-300">?search=einstein</td>
                        <td class="py-3 px-4 text-slate-400">Full-text search across all extracted record attributes.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3 px-4 font-mono font-medium text-blue-400">filter[field_name]</td>
                        <td class="py-3 px-4 font-mono text-slate-300">?filter[author]=Albert Einstein</td>
                        <td class="py-3 px-4 text-slate-400">Exact match filter on any specific schema property.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3 px-4 font-mono font-medium text-blue-400">filter[price_min]</td>
                        <td class="py-3 px-4 font-mono text-slate-300">?filter[price_min]=50</td>
                        <td class="py-3 px-4 text-slate-400">Numeric comparison for minimum price/amount boundary.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3 px-4 font-mono font-medium text-blue-400">sort</td>
                        <td class="py-3 px-4 font-mono text-slate-300">?sort=-id or ?sort=price</td>
                        <td class="py-3 px-4 text-slate-400">Sort results ascending or descending (prefix with minus).</td>
                    </tr>
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3 px-4 font-mono font-medium text-blue-400">per_page</td>
                        <td class="py-3 px-4 font-mono text-slate-300">?per_page=50</td>
                        <td class="py-3 px-4 text-slate-400">Records per page (default: 20, max: 100).</td>
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