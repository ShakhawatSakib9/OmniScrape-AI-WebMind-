@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="pb-2 border-b border-glassBorder">
        <h1 class="text-2xl font-extrabold text-white flex items-center gap-2.5">
            <i class="fa-solid fa-wand-magic-sparkles text-cyan-400"></i>
            Deploy Autonomous Scraping Agent
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">
            Provide a target URL and describe what data you need in natural language. Our AI will inspect the live DOM, infer the schema, and configure resilient selectors.
        </p>
    </div>

    <!-- Step 1: Input Form -->
    <div id="step-prompt" class="glass-card rounded-2xl p-6 space-y-6">
        <div>
            <label class="block text-xs font-mono font-bold text-slate-300 uppercase tracking-wider mb-2">
                1. Target Website URL
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 pointer-events-none">
                    <i class="fa-solid fa-globe"></i>
                </span>
                <input type="url" id="target_url" placeholder="https://quotes.toscrape.com/" value="https://quotes.toscrape.com/" class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-700 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 text-white text-sm font-mono placeholder:text-slate-600 transition" required>
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono font-bold text-slate-300 uppercase tracking-wider mb-2">
                2. Natural Language Extraction Prompt
            </label>
            <textarea id="prompt_text" rows="3" placeholder="e.g. Extract all quote texts, author names, and author profile links from this page." class="w-full p-4 rounded-xl bg-slate-900 border border-slate-700 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 text-white text-sm placeholder:text-slate-600 transition" required>Extract all quote texts, author names, and author profile links from this page.</textarea>
            
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-slate-500 font-medium">Quick Presets:</span>
                <button type="button" onclick="setPreset('https://quotes.toscrape.com/', 'Extract all quote texts, author names, and author profile links from this page.')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono transition">
                    💬 Quotes & Authors
                </button>
                <button type="button" onclick="setPreset('https://books.toscrape.com/', 'Extract book titles, prices in GBP, star ratings, and product detail URLs.')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono transition">
                    📚 E-Commerce Books
                </button>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="text-xs font-medium text-slate-400 hover:text-slate-300">
                <i class="fa-solid fa-arrow-left mr-1"></i> Cancel
            </a>
            <button type="button" id="btn-analyze" onclick="startAIAnalysis()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-sm glow-btn transition flex items-center gap-2">
                <i class="fa-solid fa-brain"></i>
                <span>Analyze DOM & Infer Schema</span>
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="hidden glass-card rounded-2xl p-12 text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-2xl mx-auto border border-cyan-500/20">
            <i class="fa-solid fa-circle-notch animate-spin"></i>
        </div>
        <h3 class="text-lg font-bold text-white">Autonomous Agent at Work</h3>
        <div class="max-w-md mx-auto space-y-2 text-xs font-mono text-slate-400">
            <div id="load-step-1" class="text-cyan-400 flex items-center justify-center gap-2">
                <i class="fa-solid fa-spinner animate-spin"></i> Launching Headless Chromium & Rendering DOM...
            </div>
            <div class="text-slate-500">Minifying Semantic DOM tree & Inferring Selectors via AI...</div>
        </div>
    </div>

    <!-- Step 2: Schema Review -->
    <div id="step-review" class="hidden glass-card rounded-2xl p-6 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-glassBorder">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    AI Inferred Schema & Resilient Selectors
                </h2>
                <p class="text-xs text-slate-400">Review the autonomous extraction configuration before launching the agent.</p>
            </div>
            <span id="badge-confidence" class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                Confidence: 98%
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono text-slate-400 uppercase mb-1">Dataset Name</label>
                <input type="text" id="project_name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-mono text-slate-400 uppercase mb-1">Repeating Container Selector</label>
                <input type="text" id="container_selector" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-cyan-400 font-mono text-xs">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono font-bold text-slate-300 uppercase tracking-wider mb-2">
                Discovered Data Fields & Selectors
            </label>
            <div class="overflow-x-auto rounded-xl border border-glassBorder">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase border-b border-glassBorder">
                        <tr>
                            <th class="p-3">Field Name</th>
                            <th class="p-3">Data Type</th>
                            <th class="p-3">Primary Selector</th>
                            <th class="p-3">Attribute</th>
                            <th class="p-3">Confidence</th>
                        </tr>
                    </thead>
                    <tbody id="fields-table-body" class="divide-y divide-glassBorder"></tbody>
                </table>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-glassBorder">
            <button type="button" onclick="resetForm()" class="text-xs font-medium text-slate-400 hover:text-slate-300">
                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Start Over
            </button>
            <button type="button" id="btn-save-project" onclick="saveAndDeployProject()" class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-sm shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-rocket"></i>
                <span>Save & Deploy Ingestion Agent</span>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentInference = null;

function setPreset(url, prompt) {
    document.getElementById('target_url').value = url;
    document.getElementById('prompt_text').value = prompt;
}

async function startAIAnalysis() {
    const url = document.getElementById('target_url').value.trim();
    const prompt = document.getElementById('prompt_text').value.trim();

    if (!url || !prompt) {
        alert('Please provide both target URL and prompt.');
        return;
    }

    document.getElementById('step-prompt').classList.add('hidden');
    document.getElementById('loading-state').classList.remove('hidden');

    try {
        const res = await fetch("{{ route('projects.infer') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ target_url: url, prompt: prompt })
        });

        const data = await res.json();

        if (!data.success) {
            alert('Analysis Error: ' + (data.error || 'Failed to analyze'));
            resetForm();
            return;
        }

        currentInference = data.inference;
        renderReviewStep(data);

    } catch (err) {
        alert('Network or server error: ' + err.message);
        resetForm();
    }
}

function renderReviewStep(data) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('step-review').classList.remove('hidden');

    const inf = data.inference;
    document.getElementById('project_name').value = inf.name || 'Dataset from ' + (data.page_title || 'Web');
    document.getElementById('container_selector').value = inf.container_selector || '';

    const tbody = document.getElementById('fields-table-body');
    tbody.innerHTML = '';

    const fields = inf.fields || [];
    let avgConfidence = 0;

    fields.forEach((f) => {
        avgConfidence += (f.confidence_score || 0.9);
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-800/40';
        tr.innerHTML = `
            <td class="p-3 font-bold text-white">${f.field_name}</td>
            <td class="p-3"><span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">${f.field_type}</span></td>
            <td class="p-3 text-cyan-400">${f.primary_selector}</td>
            <td class="p-3 text-slate-400">${f.attribute_target || 'text'}</td>
            <td class="p-3 text-emerald-400 font-bold">${Math.round((f.confidence_score || 0.95) * 100)}%</td>
        `;
        tbody.appendChild(tr);
    });

    const finalAvg = fields.length > 0 ? Math.round((avgConfidence / fields.length) * 100) : 95;
    document.getElementById('badge-confidence').innerText = `AI Confidence: ${finalAvg}%`;
}

async function saveAndDeployProject() {
    if (!currentInference) return;

    const payload = {
        name: document.getElementById('project_name').value,
        target_url: document.getElementById('target_url').value,
        prompt: document.getElementById('prompt_text').value,
        container_selector: document.getElementById('container_selector').value,
        fields: currentInference.fields,
        pagination: currentInference.pagination || { type: 'none' }
    };

    const btn = document.getElementById('btn-save-project');
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Deploying...`;

    try {
        const res = await fetch("{{ route('projects.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
        } else {
            alert('Failed to save project.');
            btn.disabled = false;
        }
    } catch (err) {
        alert('Save error: ' + err.message);
        btn.disabled = false;
    }
}

function resetForm() {
    document.getElementById('step-prompt').classList.remove('hidden');
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('step-review').classList.add('hidden');
}
</script>
@endsection