@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-glassBorder">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                Autonomous Data Infrastructure
            </h1>
            <p class="text-slate-400 text-sm mt-1">
                Natural language driven web data extraction with automatic DOM drift repair and instant live REST APIs.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-medium text-sm shadow-lg shadow-cyan-500/20 hover:from-cyan-400 hover:to-blue-500 transition flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Create New Data Agent</span>
            </a>
        </div>
    </div>

    <!-- Live KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Datasets</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-lg border border-cyan-500/20">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-white font-mono">{{ $metrics['total_projects'] }}</div>
                <div class="text-xs text-emerald-400 mt-1 flex items-center gap-1 font-medium">
                    <i class="fa-solid fa-check"></i> {{ $metrics['active_crawlers'] }} scheduled active
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Extracted Records</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg border border-blue-500/20">
                    <i class="fa-solid fa-table-list"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-white font-mono">{{ number_format($metrics['total_records']) }}</div>
                <div class="text-xs text-blue-400 mt-1 flex items-center gap-1 font-medium">
                    <i class="fa-solid fa-rotate"></i> Synchronized in JSON store
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Self-Healing Repairs</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg border border-amber-500/20">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-white font-mono">{{ $metrics['healed_count'] }}</div>
                <div class="text-xs text-amber-400 mt-1 flex items-center gap-1 font-medium">
                    <i class="fa-solid fa-sparkles"></i> Autonomous selector recoveries
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Health</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/20">
                    <i class="fa-solid fa-wave-square"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-emerald-400 font-mono">99.8%</div>
                <div class="text-xs text-emerald-400/80 mt-1 flex items-center gap-1 font-medium">
                    <i class="fa-solid fa-bolt"></i> Headless Playwright workers
                </div>
            </div>
        </div>
    </div>

    <!-- Main Datasets Table -->
    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-glassBorder">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-cyan-400"></i>
                    Configured Scraping Agents & Datasets
                </h2>
                <p class="text-xs text-slate-400">Manage autonomous ingestion pipelines and access dynamic REST endpoints.</p>
            </div>
        </div>

        @if($projects->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-2xl mx-auto mb-4 border border-cyan-500/20">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <h3 class="text-lg font-bold text-white">No Scraping Agents Configured Yet</h3>
                <p class="text-sm text-slate-400 max-w-md mx-auto mt-1 mb-6">
                    Enter any website URL and plain English instructions. Our AI will automatically infer the schema and deploy a live REST API.
                </p>
                <a href="{{ route('projects.create') }}" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-bold text-sm shadow-lg shadow-cyan-500/20 transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Deploy First Agent</span>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/50 text-slate-400 font-mono border-b border-glassBorder">
                        <tr>
                            <th class="py-3.5 px-4">Dataset Name & Prompt</th>
                            <th class="py-3.5 px-4">Target Website</th>
                            <th class="py-3.5 px-4">Total Records</th>
                            <th class="py-3.5 px-4">Status & Health</th>
                            <th class="py-3.5 px-4">Last Sync</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-glassBorder">
                        @foreach($projects as $proj)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-4">
                                    <a href="{{ route('projects.show', $proj->id) }}" class="font-bold text-white hover:text-cyan-400 transition flex items-center gap-2">
                                        <span>{{ $proj->name }}</span>
                                    </a>
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1 italic max-w-xs">"{{ $proj->prompt }}"</p>
                                </td>
                                <td class="py-4 px-4 font-mono text-xs">
                                    <a href="{{ $proj->target_url }}" target="_blank" class="text-cyan-400 hover:underline flex items-center gap-1.5">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        <span class="truncate max-w-[200px]">{{ parse_url($proj->target_url, PHP_URL_HOST) }}</span>
                                    </a>
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-white">
                                    {{ number_format($proj->records_count) }}
                                </td>
                                <td class="py-4 px-4">
                                    @php
                                        $lastRun = $proj->runs->first();
                                    @endphp
                                    @if($lastRun && $lastRun->status === 'healed')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center w-max gap-1">
                                            <i class="fa-solid fa-shield-heart text-[10px]"></i> Self-Healed
                                        </span>
                                    @elseif($proj->status === 'active')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center w-max gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-slate-700/30 text-slate-400 border border-slate-700 w-max">
                                            {{ ucfirst($proj->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-400 font-mono">
                                    {{ $proj->last_run_at ? $proj->last_run_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('projects.run', $proj->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Run Extraction Now" class="p-2 rounded-lg bg-slate-800 hover:bg-cyan-500 hover:text-slate-900 text-slate-300 transition text-xs font-medium flex items-center gap-1">
                                                <i class="fa-solid fa-play text-[10px]"></i> Run
                                            </button>
                                        </form>
                                        <a href="{{ route('projects.show', $proj->id) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition text-xs font-medium">
                                            <i class="fa-solid fa-eye"></i> Data
                                        </a>
                                        <a href="{{ route('projects.api-docs', $proj->id) }}" class="p-2 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 transition text-xs font-mono">
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

    <!-- Recent Runs & Self-Healing Audit -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-base font-bold text-white flex items-center gap-2 mb-4">
                <i class="fa-solid fa-terminal text-cyan-400"></i>
                Recent Scraper Execution Stream
            </h3>
            <div class="space-y-3">
                @forelse($recentRuns as $run)
                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-glassBorder flex items-center justify-between text-xs font-mono">
                        <div class="flex items-center gap-2.5">
                            @if($run->status === 'success')
                                <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                            @elseif($run->status === 'healed')
                                <i class="fa-solid fa-shield-heart text-amber-400 text-sm"></i>
                            @elseif($run->status === 'running')
                                <i class="fa-solid fa-spinner animate-spin text-cyan-400 text-sm"></i>
                            @else
                                <i class="fa-solid fa-circle-xmark text-rose-400 text-sm"></i>
                            @endif
                            <div>
                                <div class="font-bold text-slate-200">{{ $run->project?->name }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Extracted: <span class="text-cyan-400">{{ $run->records_extracted }}</span> ({{ $run->records_new }} new) • {{ $run->execution_time_ms }}ms
                                </div>
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-500">
                            {{ $run->started_at?->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs font-mono">No execution logs recorded yet.</div>
                @endforelse
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-base font-bold text-white flex items-center gap-2 mb-4">
                <i class="fa-solid fa-shield-heart text-amber-400"></i>
                Self-Healing Recovery Audit Trail
            </h3>
            <div class="space-y-3">
                @forelse($recentHeals as $heal)
                    <div class="p-3.5 rounded-xl bg-amber-500/5 border border-amber-500/20 text-xs font-mono space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-amber-300">{{ $heal->project?->name }} — Field: {{ $heal->field_name }}</span>
                            <span class="text-[10px] text-slate-400">{{ $heal->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-[11px] text-slate-300">
                            <span class="text-rose-400 line-through">{{ $heal->broken_selector }}</span>
                            <i class="fa-solid fa-arrow-right mx-1 text-slate-500"></i>
                            <span class="text-emerald-400 font-bold">{{ $heal->repaired_selector }}</span>
                        </div>
                        <div class="text-[10px] text-slate-400 flex items-center gap-3">
                            <span>Confidence: <b class="text-amber-400">{{ $heal->new_confidence * 100 }}%</b></span>
                            <span class="truncate max-w-xs text-slate-500 italic">{{ $heal->reasoning_log }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs font-mono">
                        <i class="fa-solid fa-shield-check text-emerald-400 text-lg mb-1 block"></i>
                        All active selectors healthy. No DOM drift repairs logged.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection