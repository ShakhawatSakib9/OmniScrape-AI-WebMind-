@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-surface-border">
        <div>
            <h1 class="text-xl font-semibold text-white tracking-tight">
                Data Infrastructure & Datasets
            </h1>
            <p class="text-slate-400 text-xs mt-0.5">
                Autonomous web extraction pipelines, continuous DOM synchronization, and dynamic REST endpoints.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('projects.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>New Scraper Agent</span>
            </a>
        </div>
    </div>

    <!-- Live KPI Metrics Cards (Linear / Stripe Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="enterprise-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Active Datasets</span>
                <span class="text-xs text-slate-500"><i class="fa-solid fa-database"></i></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-semibold text-white font-mono tracking-tight">{{ $metrics['total_projects'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                    <span class="text-emerald-400 font-medium">{{ $metrics['active_crawlers'] }}</span> active ingestion agents
                </div>
            </div>
        </div>

        <div class="enterprise-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Extracted Records</span>
                <span class="text-xs text-slate-500"><i class="fa-solid fa-table-cells"></i></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-semibold text-white font-mono tracking-tight">{{ number_format($metrics['total_records']) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Indexed & queryable via REST
                </div>
            </div>
        </div>

        <div class="enterprise-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Self-Healing Repairs</span>
                <span class="text-xs text-slate-500"><i class="fa-solid fa-shield-halved"></i></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-semibold text-amber-400 font-mono tracking-tight">{{ $metrics['healed_count'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Automated selector recoveries
                </div>
            </div>
        </div>

        <div class="enterprise-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Crawler Health</span>
                <span class="text-xs text-slate-500"><i class="fa-solid fa-chart-simple"></i></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-semibold text-emerald-400 font-mono tracking-tight">99.8%</div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Headless Playwright pool
                </div>
            </div>
        </div>
    </div>

    <!-- Telemetry & Performance Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="enterprise-card rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-300">Ingestion Throughput by Dataset</span>
                <span class="text-[10px] font-mono text-slate-500">Records count</span>
            </div>
            <div class="h-48 relative">
                <canvas id="chartThroughput"></canvas>
            </div>
        </div>

        <div class="enterprise-card rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-300">Worker Execution Latency (ms)</span>
                <span class="text-[10px] font-mono text-slate-500">Run history</span>
            </div>
            <div class="h-48 relative">
                <canvas id="chartLatency"></canvas>
            </div>
        </div>
    </div>

    <!-- Main Datasets Table (Dense Enterprise Style) -->
    <div class="enterprise-card rounded-xl overflow-hidden">
        <div class="p-4 border-b border-surface-border flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-white">Configured Datasets</h2>
                <p class="text-xs text-slate-400 mt-0.5">Manage live pipelines, inspect schemas, and query REST endpoints.</p>
            </div>
        </div>

        @if($projects->isEmpty())
            <div class="text-center py-12 px-4">
                <div class="w-10 h-10 rounded-lg bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-sm">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h3 class="text-sm font-medium text-slate-200">No scraping agents deployed yet</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    Create your first autonomous data agent using a plain English prompt or the visual selector.
                </p>
                <div class="mt-4">
                    <a href="{{ route('projects.create') }}" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-plus text-[10px]"></i> Create Agent
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/60 text-slate-400 font-medium border-b border-surface-border">
                        <tr>
                            <th class="py-3 px-4">Dataset Name & Prompt</th>
                            <th class="py-3 px-4">Target Host</th>
                            <th class="py-3 px-4 text-center">Records</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Last Sync</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border font-sans">
                        @foreach($projects as $proj)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4">
                                    <a href="{{ route('projects.show', $proj->id) }}" class="font-medium text-slate-100 hover:text-blue-400 transition">
                                        {{ $proj->name }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 truncate max-w-xs mt-0.5">
                                        "{{ $proj->prompt }}"
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                                    <a href="{{ $proj->target_url }}" target="_blank" class="hover:text-slate-200 truncate max-w-xs block flex items-center gap-1">
                                        <span>{{ parse_url($proj->target_url, PHP_URL_HOST) }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-600"></i>
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-medium text-slate-200">
                                    {{ $proj->records_count }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($proj->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-950/50 text-emerald-400 border border-emerald-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @elseif($proj->status === 'healing')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-amber-950/50 text-amber-400 border border-amber-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Healing
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                            {{ ucfirst($proj->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[11px] text-slate-400 font-mono">
                                    {{ $proj->last_run_at ? \Carbon\Carbon::parse($proj->last_run_at)->diffForHumans() : 'Never' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="{{ route('projects.run', $proj->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Run Extraction" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs font-medium flex items-center gap-1 border border-slate-700">
                                                <i class="fa-solid fa-play text-[9px]"></i> Run
                                            </button>
                                        </form>

                                        <a href="{{ route('projects.show', $proj->id) }}" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs font-medium flex items-center gap-1 border border-slate-700">
                                            <i class="fa-solid fa-table text-[10px]"></i> Data
                                        </a>

                                        <a href="{{ route('projects.api-docs', $proj->id) }}" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs font-medium flex items-center gap-1 border border-slate-700 font-mono">
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

    <!-- Execution Stream & Self-Healing Audit Trail (Split Panel) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Execution Stream -->
        <div class="enterprise-card rounded-xl p-4">
            <div class="flex items-center justify-between pb-3 border-b border-surface-border">
                <span class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-terminal text-slate-400 text-xs"></i>
                    Recent Execution Stream
                </span>
                <span class="text-[10px] text-slate-500 font-mono">Latest runs</span>
            </div>
            <div class="mt-3 space-y-2">
                @forelse($recentRuns as $run)
                    <div class="p-2.5 rounded-lg bg-slate-900/50 border border-slate-800/80 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            @if($run->status === 'success')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            @elseif($run->status === 'healed')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            @endif
                            <div>
                                <span class="font-medium text-slate-200">{{ $run->project?->name }}</span>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    {{ $run->records_extracted }} records • {{ $run->execution_time_ms }}ms
                                </div>
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-500 font-mono">
                            {{ $run->started_at?->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs font-mono">No recent execution logs.</div>
                @endforelse
            </div>
        </div>

        <!-- Self-Healing Audit Trail -->
        <div class="enterprise-card rounded-xl p-4">
            <div class="flex items-center justify-between pb-3 border-b border-surface-border">
                <span class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-amber-400 text-xs"></i>
                    Self-Healing Recovery Audit
                </span>
                <span class="text-[10px] text-slate-500 font-mono">DOM drift logs</span>
            </div>
            <div class="mt-3 space-y-2">
                @forelse($recentHeals as $heal)
                    <div class="p-2.5 rounded-lg bg-amber-950/10 border border-amber-800/30 text-xs font-mono space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-amber-300">{{ $heal->project?->name }} : {{ $heal->field_name }}</span>
                            <span class="text-[10px] text-slate-500">{{ $heal->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            <span class="text-rose-400 line-through">{{ $heal->broken_selector }}</span>
                            <span class="mx-1 text-slate-600">→</span>
                            <span class="text-emerald-400 font-medium">{{ $heal->repaired_selector }}</span>
                        </div>
                        <div class="text-[10px] text-slate-500">
                            Confidence: <b class="text-amber-400">{{ $heal->new_confidence * 100 }}%</b>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs font-mono">
                        <i class="fa-solid fa-check text-emerald-500 text-sm mb-1 block"></i>
                        All active selectors healthy. No drift repairs needed.
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
    // 1. Throughput Chart (Linear / Supabase Minimalist Style)
    const ctxThroughput = document.getElementById('chartThroughput');
    if (ctxThroughput) {
        const labels = {!! json_encode($projects->pluck('name')->map(fn($n) => \Illuminate\Support\Str::limit($n, 18))) !!};
        const recordCounts = {!! json_encode($projects->map(fn($p) => $p->records()->count())) !!};

        new Chart(ctxThroughput, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Quotes Dataset', 'Books Dataset'],
                datasets: [{
                    label: 'Records Ingested',
                    data: recordCounts.length ? recordCounts : [10, 20],
                    backgroundColor: '#3B82F6',
                    borderRadius: 4,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleColor: '#F8FAFC',
                        bodyColor: '#94A3B8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 8,
                        titleFont: { family: 'Inter', size: 12 },
                        bodyFont: { family: 'JetBrains Mono', size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1E293B' },
                        ticks: { color: '#64748B', font: { family: 'JetBrains Mono', size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Latency Chart (Crisp Monochromatic Line)
    const ctxLatency = document.getElementById('chartLatency');
    if (ctxLatency) {
        const runTimes = {!! json_encode($recentRuns->pluck('execution_time_ms')) !!};
        const runLabels = {!! json_encode($recentRuns->map(fn($r, $i) => "Run #" . ($r->id ?? ($i+1)))) !!};

        new Chart(ctxLatency, {
            type: 'line',
            data: {
                labels: runLabels.length ? runLabels : ['Run #1', 'Run #2', 'Run #3', 'Run #4'],
                datasets: [{
                    label: 'Latency (ms)',
                    data: runTimes.length ? runTimes : [3120, 2450, 6991, 1850],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    fill: true,
                    tension: 0.2,
                    borderWidth: 2,
                    pointBackgroundColor: '#10B981',
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleColor: '#F8FAFC',
                        bodyColor: '#10B981',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 8,
                        titleFont: { family: 'Inter', size: 12 },
                        bodyFont: { family: 'JetBrains Mono', size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { family: 'Inter', size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1E293B' },
                        ticks: { color: '#64748B', font: { family: 'JetBrains Mono', size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endsection