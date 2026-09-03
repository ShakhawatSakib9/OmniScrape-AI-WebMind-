@extends('layouts.app')

@section('title', 'Datasets & Operations Overview')

@section('content')
<div class="space-y-8">
    <!-- Page Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">
                Data Infrastructure & Pipelines
            </h1>
            <p class="text-slate-400 text-sm mt-1">
                Monitor autonomous web scrapers, data ingestion sync rates, and live API endpoints.
            </p>
        </div>
        <div>
            <a href="{{ route('projects.create') }}" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create New Scraper Agent</span>
            </a>
        </div>
    </div>

    <!-- 4 High-Impact KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="ui-card rounded-2xl p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider font-mono">Active Datasets</span>
                <div class="w-10 h-10 rounded-xl bg-blue-950/60 text-blue-400 flex items-center justify-center text-lg border border-blue-800/40">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-white font-mono tracking-tight">{{ $metrics['total_projects'] }}</div>
                <div class="text-xs text-emerald-400 font-semibold mt-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ $metrics['active_crawlers'] }} pipelines running</span>
                </div>
            </div>
        </div>

        <div class="ui-card rounded-2xl p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider font-mono">Extracted Records</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-950/60 text-cyan-400 flex items-center justify-center text-lg border border-cyan-800/40">
                    <i class="fa-solid fa-table-list"></i>
                </div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-white font-mono tracking-tight">{{ number_format($metrics['total_records']) }}</div>
                <div class="text-xs text-slate-400 font-medium mt-1.5">
                    Indexed & queryable in MySQL
                </div>
            </div>
        </div>

        <div class="ui-card rounded-2xl p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider font-mono">Self-Healing Repairs</span>
                <div class="w-10 h-10 rounded-xl bg-amber-950/60 text-amber-400 flex items-center justify-center text-lg border border-amber-800/40">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-amber-400 font-mono tracking-tight">{{ $metrics['healed_count'] }}</div>
                <div class="text-xs text-amber-300 font-medium mt-1.5">
                    Autonomous selector recoveries
                </div>
            </div>
        </div>

        <div class="ui-card rounded-2xl p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider font-mono">Crawler Health</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-950/60 text-emerald-400 flex items-center justify-center text-lg border border-emerald-800/40">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-emerald-400 font-mono tracking-tight">99.8%</div>
                <div class="text-xs text-slate-400 font-medium mt-1.5">
                    Headless Playwright pool
                </div>
            </div>
        </div>
    </div>

    <!-- Telemetry & Performance Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="ui-card rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-blue-400"></i>
                    <span>Ingestion Throughput by Dataset</span>
                </h3>
                <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700">Records Count</span>
            </div>
            <div class="h-64 relative">
                <canvas id="chartThroughput"></canvas>
            </div>
        </div>

        <div class="ui-card rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-stopwatch text-emerald-400"></i>
                    <span>Crawler Execution Latency (ms)</span>
                </h3>
                <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700">Latency History</span>
            </div>
            <div class="h-64 relative">
                <canvas id="chartLatency"></canvas>
            </div>
        </div>
    </div>

    <!-- Main Configured Datasets Table -->
    <div class="ui-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-[#1f293d] flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Configured Scraping Agents & Datasets</h2>
                <p class="text-sm text-slate-400 mt-1">Manage autonomous ingestion pipelines and query live REST endpoints.</p>
            </div>
        </div>

        @if($projects->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-200">No scraping agents configured</h3>
                <p class="text-sm text-slate-400 mt-1 max-w-sm mx-auto">
                    Create your first data scraper using a plain English prompt or visual selector.
                </p>
                <div class="mt-4">
                    <a href="{{ route('projects.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i> Create Agent
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-200">
                    <thead class="bg-slate-900 text-slate-400 font-semibold border-b border-[#1f293d] text-xs uppercase font-mono">
                        <tr>
                            <th class="py-4 px-6">Dataset Name & Prompt</th>
                            <th class="py-4 px-6">Target Host</th>
                            <th class="py-4 px-6 text-center">Records</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6">Last Sync</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f293d] font-sans">
                        @foreach($projects as $proj)
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="py-4 px-6">
                                    <a href="{{ route('projects.show', $proj->id) }}" class="font-bold text-white hover:text-blue-400 transition text-base">
                                        {{ $proj->name }}
                                    </a>
                                    <div class="text-xs text-slate-400 truncate max-w-md mt-1">
                                        "{{ $proj->prompt }}"
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-mono text-sm text-slate-300">
                                    <a href="{{ $proj->target_url }}" target="_blank" class="hover:text-blue-400 inline-flex items-center gap-1.5 text-blue-400">
                                        <span>{{ parse_url($proj->target_url, PHP_URL_HOST) }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-500"></i>
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-center font-mono font-bold text-base text-white">
                                    {{ $proj->records_count }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($proj->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Active
                                        </span>
                                    @elseif($proj->status === 'healing')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950 text-amber-300 border border-amber-800">
                                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Healing
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ ucfirst($proj->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-400 font-mono">
                                    {{ $proj->last_run_at ? \Carbon\Carbon::parse($proj->last_run_at)->diffForHumans() : 'Never' }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('projects.run', $proj->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Run Extraction" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-xs font-semibold flex items-center gap-1.5 border border-slate-700">
                                                <i class="fa-solid fa-play text-[10px]"></i> Run
                                            </button>
                                        </form>

                                        <a href="{{ route('projects.show', $proj->id) }}" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-xs font-semibold flex items-center gap-1.5 border border-slate-700">
                                            <i class="fa-solid fa-table text-xs text-blue-400"></i> Data
                                        </a>

                                        <a href="{{ route('projects.api-docs', $proj->id) }}" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-xs font-semibold flex items-center gap-1.5 border border-slate-700 font-mono">
                                            API Docs
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Execution Logs & Self-Healing Audit Trail -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Execution Stream -->
        <div class="ui-card rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f293d]">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-terminal text-blue-400"></i>
                    <span>Recent Scraper Runs</span>
                </h3>
                <span class="text-xs text-slate-400 font-mono">Live Activity</span>
            </div>
            <div class="space-y-2.5">
                @forelse($recentRuns as $run)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between text-sm">
                        <div class="flex items-center gap-3">
                            @if($run->status === 'success')
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            @elseif($run->status === 'healed')
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            @endif
                            <div>
                                <span class="font-bold text-white">{{ $run->project?->name }}</span>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">
                                    Extracted <span class="text-blue-400 font-bold">{{ $run->records_extracted }}</span> records • {{ $run->execution_time_ms }}ms runtime
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 font-mono">
                            {{ $run->started_at?->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-sm font-mono">No scraper executions logged yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Self-Healing Audit Trail -->
        <div class="ui-card rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f293d]">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-shield-heart text-amber-400"></i>
                    <span>Self-Healing Recovery Audit Trail</span>
                </h3>
                <span class="text-xs text-slate-400 font-mono">DOM Drift Repairs</span>
            </div>
            <div class="space-y-2.5">
                @forelse($recentHeals as $heal)
                    <div class="p-3.5 rounded-xl bg-amber-950/20 border border-amber-800/40 text-sm font-mono space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-amber-300">{{ $heal->project?->name }} : {{ $heal->field_name }}</span>
                            <span class="text-xs text-slate-400">{{ $heal->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-xs text-slate-300">
                            <span class="text-rose-400 line-through">{{ $heal->broken_selector }}</span>
                            <span class="mx-2 text-slate-500">→</span>
                            <span class="text-emerald-400 font-bold">{{ $heal->repaired_selector }}</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Confidence Score: <b class="text-amber-400">{{ $heal->new_confidence * 100 }}%</b>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-sm font-mono">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg mb-1 block"></i>
                        All selectors healthy. No DOM drift repairs logged.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Throughput Chart (Clean Blue Enterprise Bars)
    const ctxThroughput = document.getElementById('chartThroughput');
    if (ctxThroughput) {
        const labels = {!! json_encode($projects->pluck('name')->map(fn($n) => \Illuminate\Support\Str::limit($n, 20))) !!};
        const recordCounts = {!! json_encode($projects->map(fn($p) => $p->records()->count())) !!};

        new Chart(ctxThroughput, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Quotes Dataset', 'Books Dataset'],
                datasets: [{
                    label: 'Extracted Records Ingested',
                    data: recordCounts.length ? recordCounts : [10, 20],
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#ffffff',
                        bodyColor: '#93c5fd',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                        bodyFont: { family: 'JetBrains Mono', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 12, weight: 600 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1f293d' },
                        ticks: { color: '#94a3b8', font: { family: 'JetBrains Mono', size: 12 } }
                    }
                }
            }
        });
    }

    // 2. Latency Chart (Smooth Emerald Line)
    const ctxLatency = document.getElementById('chartLatency');
    if (ctxLatency) {
        const runTimes = {!! json_encode($recentRuns->pluck('execution_time_ms')) !!};
        const runLabels = {!! json_encode($recentRuns->map(fn($r, $i) => "Run #" . ($r->id ?? ($i+1)))) !!};

        new Chart(ctxLatency, {
            type: 'line',
            data: {
                labels: runLabels.length ? runLabels : ['Run #1', 'Run #2', 'Run #3', 'Run #4'],
                datasets: [{
                    label: 'Crawler Latency (ms)',
                    data: runTimes.length ? runTimes : [3120, 2450, 6991, 1850],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#ffffff',
                        bodyColor: '#6ee7b7',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                        bodyFont: { family: 'JetBrains Mono', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 12, weight: 600 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1f293d' },
                        ticks: { color: '#94a3b8', font: { family: 'JetBrains Mono', size: 12 } }
                    }
                }
            }
        });
    }
});
</script>
@endsection