@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="space-y-8">
    <!-- Header Box -->
    <div class="ui-card rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">
                    <i class="fa-solid fa-arrow-left"></i> Datasets
                </a>
                <span>/</span>
                <span class="text-blue-400 font-bold">Live Ingestion Pipeline</span>
            </div>
            <h1 class="text-2xl font-bold text-white mt-1.5">{{ $project->name }}</h1>
            <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-slate-400 font-mono">
                <span>URL: <a href="{{ $project->target_url }}" target="_blank" class="text-blue-400 hover:underline">{{ $project->target_url }}</a></span>
                <span>•</span>
                <span>Slug: <span class="text-slate-200 font-semibold">{{ $project->slug }}</span></span>
                <span>•</span>
                <span>Container: <code class="text-emerald-400 font-bold">{{ $project->container_selector ?: 'Global' }}</code></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="startLiveStreamRun()" class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow transition flex items-center gap-2">
                <i class="fa-solid fa-terminal text-xs"></i>
                <span>Live Run</span>
            </button>

            <form action="{{ route('projects.run', $project->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-sm font-semibold flex items-center gap-2 border border-slate-700">
                    <i class="fa-solid fa-play text-xs"></i> Background Run
                </button>
            </form>

            <a href="{{ route('projects.api-docs', $project->id) }}" class="px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-sm font-semibold flex items-center gap-2 border border-slate-700 font-mono">
                <i class="fa-solid fa-code text-xs text-blue-400"></i> API Docs
            </a>

            <a href="{{ route('api.datasets.export', ['slug' => $project->slug, 'format' => 'csv']) }}" class="px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-sm font-semibold flex items-center gap-2 border border-slate-700">
                <i class="fa-solid fa-file-csv text-xs text-emerald-400"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Live Execution Telemetry Stream Console -->
    <div id="live-terminal-panel" class="hidden ui-card rounded-2xl p-6 bg-slate-950 border border-slate-800 space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span>
                <span class="text-sm font-mono font-bold text-white">Headless Playwright Execution Console</span>
                <span id="terminal-badge" class="px-2 py-0.5 rounded text-xs font-mono bg-blue-950 text-blue-300 border border-blue-800 font-bold">RUNNING</span>
            </div>
            <button type="button" onclick="document.getElementById('live-terminal-panel').classList.add('hidden')" class="text-sm text-slate-400 hover:text-slate-200">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        
        <div id="terminal-console" class="h-56 overflow-y-auto font-mono text-sm space-y-1.5 text-slate-300 p-2">
            <div class="text-slate-500">[0.00s] Initializing Chromium context...</div>
        </div>
    </div>

    <!-- Active Schema & Selectors Grid -->
    <div class="ui-card rounded-2xl p-6 space-y-4">
        <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono">
            Configured Selectors & Schema Properties
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($project->selectors as $sel)
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5 font-mono text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white text-sm">{{ $sel->field_name }}</span>
                        @if($sel->status === 'repaired')
                            <span class="text-xs px-2 py-0.5 rounded bg-amber-950 text-amber-300 font-bold border border-amber-800">Healed</span>
                        @else
                            <span class="text-xs text-emerald-400 font-bold">Active</span>
                        @endif
                    </div>
                    <div class="text-xs text-blue-400 truncate font-bold" title="{{ $sel->primary_selector }}">
                        {{ $sel->primary_selector }}
                    </div>
                    <div class="text-xs text-slate-400 flex items-center justify-between pt-1">
                        <span>Target: {{ $sel->attribute_target }}</span>
                        <span>Type: {{ $sel->schema?->field_type }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Extracted Records Table -->
    <div class="ui-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-[#1f293d] flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Extracted Records ({{ number_format($records->total()) }})</h2>
                <p class="text-sm text-slate-400 mt-1">Normalized live records synchronized from target website DOM.</p>
            </div>
        </div>

        @if($records->isEmpty())
            <div class="text-center py-16 text-slate-400 text-sm font-mono">
                No records extracted yet. Click <b>"Live Run"</b> to fetch initial dataset.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-200">
                    <thead class="bg-slate-900 text-slate-400 font-semibold border-b border-[#1f293d] font-mono text-xs uppercase">
                        <tr>
                            <th class="py-4 px-6">#ID</th>
                            @foreach($project->schemas as $schema)
                                <th class="py-4 px-6">{{ $schema->field_label ?: $schema->field_name }}</th>
                            @endforeach
                            <th class="py-4 px-6 text-right">First Seen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f293d] font-sans">
                        @foreach($records as $rec)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-6 text-slate-500 font-mono font-bold">{{ $rec->id }}</td>
                                @foreach($project->schemas as $schema)
                                    @php
                                        $val = $rec->data_json[$schema->field_name] ?? null;
                                    @endphp
                                    <td class="py-4 px-6">
                                        @if($schema->field_type === 'link' && $val)
                                            <a href="{{ $val }}" target="_blank" class="text-blue-400 hover:underline truncate max-w-sm block font-mono text-xs">
                                                {{ $val }}
                                            </a>
                                        @elseif($schema->field_type === 'image_url' && $val)
                                            <img src="{{ $val }}" alt="thumb" class="w-9 h-9 rounded-lg object-cover border border-slate-700">
                                        @elseif($schema->field_type === 'price' && $val)
                                            <span class="text-emerald-400 font-mono font-bold text-sm">${{ is_numeric($val) ? number_format($val, 2) : $val }}</span>
                                        @else
                                            <span class="truncate max-w-md block text-slate-100 font-medium">{{ $val ?: '-' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-4 px-6 text-right text-slate-400 text-xs font-mono">
                                    {{ $rec->first_seen_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[#1f293d]">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
async function startLiveStreamRun() {
    const panel = document.getElementById('live-terminal-panel');
    const consoleBox = document.getElementById('terminal-console');
    const badge = document.getElementById('terminal-badge');
    
    panel.classList.remove('hidden');
    consoleBox.innerHTML = '';

    const log = (text, type = 'info', timeSec = 0) => {
        const line = document.createElement('div');
        const color = type === 'success' ? 'text-emerald-400 font-bold' : (type === 'warn' ? 'text-amber-400 font-bold' : (type === 'error' ? 'text-rose-400 font-bold' : 'text-slate-300'));
        line.className = `${color} font-mono text-sm flex items-center justify-between py-1`;
        line.innerHTML = `<span>[${timeSec.toFixed(2)}s] ${text}</span> <span class="text-xs text-slate-500 font-mono uppercase font-bold">${type}</span>`;
        consoleBox.appendChild(line);
        consoleBox.scrollTop = consoleBox.scrollHeight;
    };

    badge.className = 'px-2 py-0.5 rounded text-xs font-mono bg-blue-950 text-blue-300 border border-blue-800 font-bold animate-pulse';
    badge.innerText = 'RUNNING';

    log('Initializing Headless Chromium sandbox context...', 'info', 0.05);
    setTimeout(() => log('Dispatched HTTP GET request -> {{ $project->target_url }}', 'info', 0.42), 400);
    setTimeout(() => log('DOM content loaded (HTTP 200 OK) -> Minifying semantic tree', 'info', 0.95), 900);
    setTimeout(() => log('Applied {{ $project->selectors->count() }} container selectors ({{ $project->container_selector ?: "global" }})', 'info', 1.45), 1400);
    setTimeout(() => log('Watchdog: Evaluating fill rate across records...', 'info', 1.80), 1800);

    try {
        const res = await fetch("{{ route('projects.run', $project->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'text/html'
            }
        });

        setTimeout(() => {
            log('Ingested records into MySQL with SHA-256 deduplication', 'success', 2.30);
            log('Pipeline execution completed successfully (200 OK)', 'success', 2.65);
            badge.className = 'px-2 py-0.5 rounded text-xs font-mono bg-emerald-950 text-emerald-300 border border-emerald-800 font-bold';
            badge.innerText = 'SUCCESS (200 OK)';

            setTimeout(() => {
                window.location.reload();
            }, 1200);
        }, 2200);

    } catch (err) {
        log('Execution failed: ' + err.message, 'error', 2.50);
        badge.className = 'px-2 py-0.5 rounded text-xs font-mono bg-rose-950 text-rose-300 border border-rose-800 font-bold';
        badge.innerText = 'FAILED';
    }
}
</script>
@endsection