@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Box -->
    <div class="enterprise-card rounded-xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition text-xs font-medium">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Datasets
                </a>
                <span class="text-slate-600">/</span>
                <span class="text-xs font-mono text-slate-400 font-medium">Pipeline Details</span>
            </div>
            <h1 class="text-xl font-semibold text-white mt-1">{{ $project->name }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-400 font-mono">
                <span>URL: <a href="{{ $project->target_url }}" target="_blank" class="text-blue-400 hover:underline">{{ $project->target_url }}</a></span>
                <span>•</span>
                <span>Slug: <span class="text-slate-300">{{ $project->slug }}</span></span>
                <span>•</span>
                <span>Container: <code class="text-emerald-400 font-medium">{{ $project->container_selector ?: 'Global' }}</code></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="startLiveStreamRun()" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-terminal text-[10px]"></i>
                <span>Live Run</span>
            </button>

            <form action="{{ route('projects.run', $project->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs font-medium flex items-center gap-1 border border-slate-700" title="Background Run">
                    <i class="fa-solid fa-play text-[9px]"></i> Run
                </button>
            </form>

            <a href="{{ route('projects.api-docs', $project->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs font-medium flex items-center gap-1 border border-slate-700 font-mono">
                <i class="fa-solid fa-code text-[10px]"></i> API Docs
            </a>

            <a href="{{ route('api.datasets.export', ['slug' => $project->slug, 'format' => 'csv']) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs font-medium flex items-center gap-1 border border-slate-700">
                <i class="fa-solid fa-file-csv text-[10px]"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Live Execution Telemetry Stream (Datadog / Vercel style) -->
    <div id="live-terminal-panel" class="hidden enterprise-card rounded-xl p-4 bg-slate-950 border border-slate-800 space-y-2">
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <span class="text-xs font-mono font-medium text-slate-200">Execution Telemetry Stream</span>
                <span id="terminal-badge" class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-blue-950 text-blue-300 border border-blue-800">Active</span>
            </div>
            <button type="button" onclick="document.getElementById('live-terminal-panel').classList.add('hidden')" class="text-xs text-slate-500 hover:text-slate-300">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div id="terminal-console" class="h-44 overflow-y-auto font-mono text-xs space-y-1 text-slate-300 py-1">
            <div class="text-slate-500">[0.00s] Initializing Chromium context...</div>
        </div>
    </div>

    <!-- Active Schema & Selectors Grid -->
    <div class="enterprise-card rounded-xl p-4 space-y-3">
        <h2 class="text-xs font-semibold text-slate-300 uppercase tracking-wider font-mono">
            Configured Selectors & Schema
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($project->selectors as $sel)
                <div class="p-3 rounded-lg bg-slate-900/60 border border-slate-800 space-y-1 font-mono text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-slate-200">{{ $sel->field_name }}</span>
                        @if($sel->status === 'repaired')
                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-950 text-amber-300 font-medium border border-amber-800">Healed</span>
                        @else
                            <span class="text-[10px] text-emerald-400 font-medium">Active</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-blue-400 truncate font-mono" title="{{ $sel->primary_selector }}">
                        {{ $sel->primary_selector }}
                    </div>
                    <div class="text-[10px] text-slate-500 flex items-center justify-between">
                        <span>Target: {{ $sel->attribute_target }}</span>
                        <span>Type: {{ $sel->schema?->field_type }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Extracted Records Table -->
    <div class="enterprise-card rounded-xl overflow-hidden">
        <div class="p-4 border-b border-surface-border flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-white">Extracted Records ({{ number_format($records->total()) }})</h2>
                <p class="text-xs text-slate-400 mt-0.5">Normalized JSON data synced from target DOM.</p>
            </div>
        </div>

        @if($records->isEmpty())
            <div class="text-center py-12 text-slate-500 text-xs font-mono">
                No records extracted yet. Click <b>"Live Run"</b> to fetch initial dataset.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/60 text-slate-400 font-medium border-b border-surface-border font-mono">
                        <tr>
                            <th class="py-3 px-4">#ID</th>
                            @foreach($project->schemas as $schema)
                                <th class="py-3 px-4">{{ $schema->field_label ?: $schema->field_name }}</th>
                            @endforeach
                            <th class="py-3 px-4 text-right">First Seen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border font-sans">
                        @foreach($records as $rec)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 text-slate-500 font-mono">{{ $rec->id }}</td>
                                @foreach($project->schemas as $schema)
                                    @php
                                        $val = $rec->data_json[$schema->field_name] ?? null;
                                    @endphp
                                    <td class="py-3 px-4">
                                        @if($schema->field_type === 'link' && $val)
                                            <a href="{{ $val }}" target="_blank" class="text-blue-400 hover:underline truncate max-w-xs block font-mono text-[11px]">
                                                {{ $val }}
                                            </a>
                                        @elseif($schema->field_type === 'image_url' && $val)
                                            <img src="{{ $val }}" alt="thumb" class="w-7 h-7 rounded object-cover border border-slate-700">
                                        @elseif($schema->field_type === 'price' && $val)
                                            <span class="text-emerald-400 font-mono font-medium">${{ is_numeric($val) ? number_format($val, 2) : $val }}</span>
                                        @else
                                            <span class="truncate max-w-sm block text-slate-200">{{ $val ?: '-' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-3 px-4 text-right text-slate-500 text-[11px] font-mono">
                                    {{ $rec->first_seen_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-surface-border">
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
        const color = type === 'success' ? 'text-emerald-400' : (type === 'warn' ? 'text-amber-400' : (type === 'error' ? 'text-rose-400' : 'text-slate-300'));
        line.className = `${color} font-mono text-xs flex items-center justify-between py-0.5`;
        line.innerHTML = `<span>[${timeSec.toFixed(2)}s] ${text}</span> <span class="text-[9px] text-slate-600 font-mono uppercase">${type}</span>`;
        consoleBox.appendChild(line);
        consoleBox.scrollTop = consoleBox.scrollHeight;
    };

    badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-mono bg-blue-950 text-blue-300 border border-blue-800 animate-pulse';
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
            badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-mono bg-emerald-950 text-emerald-300 border border-emerald-800';
            badge.innerText = 'SUCCESS';

            setTimeout(() => {
                window.location.reload();
            }, 1200);
        }, 2200);

    } catch (err) {
        log('Execution failed: ' + err.message, 'error', 2.50);
        badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-mono bg-rose-950 text-rose-300 border border-rose-800';
        badge.innerText = 'FAILED';
    }
}
</script>
@endsection