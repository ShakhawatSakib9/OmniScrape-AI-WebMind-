@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="glass-card rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition text-xs">
                    <i class="fa-solid fa-arrow-left"></i> Datasets
                </a>
                <span class="text-slate-600">/</span>
                <span class="text-xs font-mono text-cyan-400 uppercase font-bold">Live Ingestion Pipeline</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white mt-1">{{ $project->name }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs font-mono text-slate-400">
                <span>URL: <a href="{{ $project->target_url }}" target="_blank" class="text-cyan-400 hover:underline">{{ $project->target_url }}</a></span>
                <span>•</span>
                <span>Slug: <b class="text-slate-200">{{ $project->slug }}</b></span>
                <span>•</span>
                <span>Container: <code class="text-emerald-400">{{ $project->container_selector ?: 'Global' }}</code></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('projects.run', $project->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs glow-btn transition flex items-center gap-2">
                    <i class="fa-solid fa-play"></i> Run Extraction Now
                </button>
            </form>

            <a href="{{ route('projects.api-docs', $project->id) }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 border border-cyan-500/30 text-xs font-mono font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-code"></i> API Explorer
            </a>

            <a href="{{ route('api.datasets.export', ['slug' => $project->slug, 'format' => 'csv']) }}" class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Active Schema & Selectors -->
    <div class="glass-card rounded-2xl p-5">
        <h2 class="text-sm font-bold text-white uppercase font-mono tracking-wider mb-3 flex items-center gap-2">
            <i class="fa-solid fa-sliders text-cyan-400"></i> Active Schema & Selector Health
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($project->selectors as $sel)
                <div class="p-3 rounded-xl bg-slate-900/60 border border-glassBorder space-y-1 font-mono text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white">{{ $sel->field_name }}</span>
                        @if($sel->status === 'repaired')
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30">Healed</span>
                        @else
                            <span class="text-[10px] text-emerald-400 font-bold">100%</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-cyan-400 truncate" title="{{ $sel->primary_selector }}">
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
    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-glassBorder">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-table-list text-cyan-400"></i>
                    Extracted Dataset Records ({{ number_format($records->total()) }})
                </h2>
                <p class="text-xs text-slate-400">Live queryable records normalized from target website DOM.</p>
            </div>
        </div>

        @if($records->isEmpty())
            <div class="text-center py-12 text-slate-500 text-sm font-mono">
                No records extracted yet. Click <b>"Run Extraction Now"</b> above to trigger the initial crawler job.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-200 font-mono">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase border-b border-glassBorder">
                        <tr>
                            <th class="p-3">#ID</th>
                            @foreach($project->schemas as $schema)
                                <th class="p-3">{{ $schema->field_label ?: $schema->field_name }}</th>
                            @endforeach
                            <th class="p-3 text-right">First Seen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-glassBorder">
                        @foreach($records as $rec)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3 text-slate-500">{{ $rec->id }}</td>
                                @foreach($project->schemas as $schema)
                                    @php
                                        $val = $rec->data_json[$schema->field_name] ?? null;
                                    @endphp
                                    <td class="p-3">
                                        @if($schema->field_type === 'link' && $val)
                                            <a href="{{ $val }}" target="_blank" class="text-cyan-400 hover:underline truncate max-w-xs block">
                                                {{ $val }}
                                            </a>
                                        @elseif($schema->field_type === 'image_url' && $val)
                                            <img src="{{ $val }}" alt="thumb" class="w-8 h-8 rounded object-cover">
                                        @elseif($schema->field_type === 'price' && $val)
                                            <span class="text-emerald-400 font-bold">${{ is_numeric($val) ? number_format($val, 2) : $val }}</span>
                                        @else
                                            <span class="truncate max-w-sm block">{{ $val ?: '-' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="p-3 text-right text-slate-400 text-[10px]">
                                    {{ $rec->first_seen_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection