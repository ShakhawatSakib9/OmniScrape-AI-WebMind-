@extends('layouts.app')

@section('title', 'REST API Reference — ' . $project->name)

@section('content')
<div class="space-y-8">
    <div class="pb-4 border-b border-[#1f293d]">
        <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
            <a href="{{ route('projects.show', $project->id) }}" class="hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i> Back to {{ $project->name }}
            </a>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight mt-1.5">
            REST API Reference & Explorer
        </h1>
        <p class="text-slate-400 text-sm mt-1">Integrate this normalized dataset into external applications via standard HTTP GET queries.</p>
    </div>

    <!-- Endpoint URL Box -->
    <div class="ui-card rounded-2xl p-6 space-y-4">
        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono">
            Dataset REST Endpoint URL
        </label>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-2.5 rounded-xl bg-emerald-950 text-emerald-400 font-mono font-bold text-sm border border-emerald-800">
                GET
            </span>
            <input type="text" id="api-url" readonly value="{{ route('api.datasets.show', $project->slug) }}" class="flex-grow px-4 py-3 rounded-xl ui-input text-sm font-mono text-white font-medium">
            <button onclick="copyApiUrl()" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm transition border border-slate-700 flex items-center gap-2">
                <i class="fa-regular fa-copy"></i>
                <span>Copy</span>
            </button>
            <a href="{{ route('api.datasets.show', $project->slug) }}" target="_blank" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow transition flex items-center gap-2">
                <span>Open Raw JSON</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Query Parameters Documentation Table -->
    <div class="ui-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-[#1f293d]">
            <h2 class="text-lg font-bold text-white">Supported Query Parameters & Filtering</h2>
            <p class="text-sm text-slate-400 mt-1">Dynamic query filtering, pagination, and sorting arguments.</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-200 font-mono">
                <thead class="bg-slate-900 text-slate-400 uppercase border-b border-[#1f293d] text-xs">
                    <tr>
                        <th class="py-4 px-6">Parameter</th>
                        <th class="py-4 px-6">Example Request</th>
                        <th class="py-4 px-6 font-sans">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f293d] font-sans">
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6 font-mono font-bold text-blue-400 text-sm">search</td>
                        <td class="py-4 px-6 font-mono text-slate-300 text-xs">?search=einstein</td>
                        <td class="py-4 px-6 text-slate-300">Full-text search across all extracted record attributes.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6 font-mono font-bold text-blue-400 text-sm">filter[field_name]</td>
                        <td class="py-4 px-6 font-mono text-slate-300 text-xs">?filter[author]=Albert Einstein</td>
                        <td class="py-4 px-6 text-slate-300">Exact match filter on any specific schema property.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6 font-mono font-bold text-blue-400 text-sm">filter[price_min]</td>
                        <td class="py-4 px-6 font-mono text-slate-300 text-xs">?filter[price_min]=50</td>
                        <td class="py-4 px-6 text-slate-300">Numeric comparison for minimum price/amount boundary.</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6 font-mono font-bold text-blue-400 text-sm">sort</td>
                        <td class="py-4 px-6 font-mono text-slate-300 text-xs">?sort=-id or ?sort=price</td>
                        <td class="py-4 px-6 text-slate-300">Sort results ascending or descending (prefix with minus).</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-4 px-6 font-mono font-bold text-blue-400 text-sm">per_page</td>
                        <td class="py-4 px-6 font-mono text-slate-300 text-xs">?per_page=50</td>
                        <td class="py-4 px-6 text-slate-300">Records per page (default: 20, max: 100).</td>
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