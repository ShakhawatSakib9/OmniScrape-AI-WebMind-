@extends('layouts.app')

@section('title', 'Deploy Scraping Agent')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <div class="pb-4 border-b border-[#1f293d]">
        <h1 class="text-2xl font-bold text-white tracking-tight">
            Deploy Autonomous Scraping Agent
        </h1>
        <p class="text-slate-400 text-sm mt-1">
            Provide a target website URL and specify the data to extract via natural language or interactive visual point-and-click.
        </p>
    </div>

    <!-- Step 1: Input Form -->
    <div id="step-prompt" class="ui-card rounded-2xl p-8 space-y-6">
        <div>
            <label class="block text-sm font-bold text-slate-200 uppercase tracking-wider font-mono mb-2">
                1. Target Website URL
            </label>
            <div class="relative">
                <input type="url" id="target_url" placeholder="https://quotes.toscrape.com/" value="https://quotes.toscrape.com/" class="w-full px-4 py-3 rounded-xl ui-input text-sm font-mono placeholder:text-slate-500 transition" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-200 uppercase tracking-wider font-mono mb-2">
                2. Extraction Prompt / Requirements
            </label>
            <textarea id="prompt_text" rows="4" placeholder="Describe the fields you want extracted from the page..." class="w-full p-4 rounded-xl ui-input text-sm placeholder:text-slate-500 transition" required>Extract all quote texts, author names, and author profile links from this page.</textarea>
            
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-slate-400 font-semibold">Quick Presets:</span>
                <button type="button" onclick="setPreset('https://quotes.toscrape.com/', 'Extract all quote texts, author names, and author profile links from this page.')" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-mono text-xs border border-slate-700 transition">
                    💬 Quotes & Authors
                </button>
                <button type="button" onclick="setPreset('https://books.toscrape.com/', 'Extract book titles, prices in GBP, star ratings, and product detail URLs.')" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-mono text-xs border border-slate-700 transition">
                    📚 E-Commerce Books
                </button>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-[#1f293d]">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-400 hover:text-white transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Cancel
            </a>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="openVisualPicker()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-crosshairs text-blue-400"></i>
                    <span>Visual Picker</span>
                </button>
                <button type="button" id="btn-analyze" onclick="startAIAnalysis()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow transition flex items-center gap-2 flex-grow sm:flex-grow-0 justify-center">
                    <i class="fa-solid fa-brain"></i>
                    <span>Analyze DOM & Infer Schema</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Visual Picker Modal (Full Screen) -->
    <div id="visual-picker-modal" class="fixed inset-0 z-[100] hidden bg-[#0b1120] flex flex-col">
        <!-- Header -->
        <div class="h-14 border-b border-[#1f293d] bg-[#0f172a] flex items-center justify-between px-6 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-sm">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <div>
                    <h2 class="text-white font-bold text-sm">Interactive Visual Selector Workspace</h2>
                    <p class="text-xs text-slate-400 font-mono" id="vp-url-display">Loading...</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="closeVisualPicker()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition border border-slate-700">
                    Close
                </button>
                <button onclick="applyVisualSelections()" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow transition">
                    Apply Selections
                </button>
            </div>
        </div>
        
        <!-- Workspace -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Iframe Canvas -->
            <div class="flex-1 bg-white relative">
                <div id="vp-loading" class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm flex flex-col items-center justify-center z-10 text-slate-200">
                    <i class="fa-solid fa-spinner animate-spin text-3xl mb-3 text-blue-400"></i>
                    <p class="font-mono text-sm font-bold text-white tracking-wider">RENDERING LIVE DOM SANDBOX...</p>
                </div>
                <iframe id="vp-iframe" class="w-full h-full border-0" sandbox="allow-same-origin allow-scripts"></iframe>
            </div>
            
            <!-- Sidebar -->
            <div class="w-88 bg-[#0f172a] border-l border-[#1f293d] flex flex-col shrink-0">
                <div class="p-5 border-b border-[#1f293d]">
                    <h3 class="text-sm font-bold text-white uppercase font-mono tracking-wider">Selected Elements</h3>
                    <p class="text-xs text-slate-400 mt-1">Click elements in the website canvas to capture exact selectors.</p>
                </div>
                <div class="flex-1 overflow-y-auto p-5 space-y-3" id="vp-selections-list">
                    <div class="text-center text-slate-500 text-xs py-12 border-2 border-dashed border-slate-800 rounded-xl">
                        No elements selected yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="hidden ui-card rounded-2xl p-12 text-center space-y-4">
        <div class="w-14 h-14 rounded-2xl bg-blue-950/60 text-blue-400 flex items-center justify-center text-2xl mx-auto border border-blue-800/40">
            <i class="fa-solid fa-spinner animate-spin text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-white">Analyzing Live DOM & Inferring Selectors</h3>
        <p class="text-sm font-mono text-slate-400 max-w-md mx-auto">
            Rendering website via Headless Chromium, minifying DOM noise, and generating resilient schema via Gemini 2.5...
        </p>
    </div>

    <!-- Step 2: Schema Review -->
    <div id="step-review" class="hidden ui-card rounded-2xl p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-[#1f293d]">
            <div>
                <h2 class="text-lg font-bold text-white">
                    Inferred Schema & Selectors
                </h2>
                <p class="text-sm text-slate-400 mt-0.5">Review generated fields and selectors before deploying the agent.</p>
            </div>
            <span id="badge-confidence" class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                Confidence: 98%
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-mono text-slate-400 uppercase font-bold mb-1.5">Dataset Name</label>
                <input type="text" id="project_name" class="w-full px-4 py-2.5 rounded-xl ui-input text-sm font-bold text-white">
            </div>
            <div>
                <label class="block text-xs font-mono text-slate-400 uppercase font-bold mb-1.5">Repeating Container Selector</label>
                <input type="text" id="container_selector" class="w-full px-4 py-2.5 rounded-xl ui-input text-blue-400 font-mono text-sm font-bold">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono font-bold text-slate-300 uppercase tracking-wider mb-2.5">
                Discovered Fields & Selectors
            </label>
            <div class="overflow-x-auto rounded-xl border border-[#1f293d]">
                <table class="w-full text-left text-sm text-slate-200 font-mono">
                    <thead class="bg-slate-900 text-slate-400 uppercase border-b border-[#1f293d] text-xs">
                        <tr>
                            <th class="py-3 px-4">Field Name</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Primary Selector</th>
                            <th class="py-3 px-4">Attribute</th>
                            <th class="py-3 px-4">Confidence</th>
                        </tr>
                    </thead>
                    <tbody id="fields-table-body" class="divide-y divide-[#1f293d]"></tbody>
                </table>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-[#1f293d]">
            <button type="button" onclick="resetForm()" class="text-sm font-semibold text-slate-400 hover:text-white">
                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Start Over
            </button>
            <button type="button" id="btn-save-project" onclick="saveAndDeployProject()" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow transition flex items-center gap-2">
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
let visualSelections = [];

function setPreset(url, prompt) {
    document.getElementById('target_url').value = url;
    document.getElementById('prompt_text').value = prompt;
}

function openVisualPicker() {
    const url = document.getElementById('target_url').value.trim();
    if (!url) {
        alert('Please enter a target URL first.');
        return;
    }
    
    document.getElementById('vp-url-display').innerText = url;
    document.getElementById('visual-picker-modal').classList.remove('hidden');
    document.getElementById('vp-loading').classList.remove('hidden');
    
    const iframe = document.getElementById('vp-iframe');
    iframe.src = "{{ route('proxy.render') }}?url=" + encodeURIComponent(url);
    
    iframe.onload = () => {
        document.getElementById('vp-loading').classList.add('hidden');
    };
}

function closeVisualPicker() {
    document.getElementById('visual-picker-modal').classList.add('hidden');
    document.getElementById('vp-iframe').src = 'about:blank';
}

window.addEventListener('message', (e) => {
    if (e.data && e.data.type === 'OMNIPICKER_SELECTION') {
        visualSelections.push({
            selector: e.data.selector,
            text: e.data.text,
            tag: e.data.tag,
            id: Date.now()
        });
        renderVisualSelections();
    }
});

function removeSelection(id) {
    visualSelections = visualSelections.filter(s => s.id !== id);
    renderVisualSelections();
}

function renderVisualSelections() {
    const list = document.getElementById('vp-selections-list');
    if (visualSelections.length === 0) {
        list.innerHTML = `<div class="text-center text-slate-500 text-xs py-12 border-2 border-dashed border-slate-800 rounded-xl">No elements selected yet.</div>`;
        return;
    }
    
    let html = '';
    visualSelections.forEach((sel) => {
        html += `
        <div class="p-3.5 bg-slate-900 rounded-xl border border-slate-800 relative group">
            <button onclick="removeSelection(${sel.id})" class="absolute top-2.5 right-2.5 text-slate-500 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
            <div class="text-xs font-mono text-blue-400 mb-1 truncate pr-6 font-bold">${sel.selector}</div>
            <div class="text-xs text-slate-300 italic truncate border-l-2 border-blue-500 pl-2">"${sel.text}"</div>
            <input type="text" id="vs-name-${sel.id}" placeholder="Field name (e.g. price)" class="mt-2 w-full ui-input rounded-lg px-2.5 py-1.5 text-xs text-white placeholder:text-slate-600">
        </div>`;
    });
    list.innerHTML = html;
}

function applyVisualSelections() {
    if (visualSelections.length === 0) {
        closeVisualPicker();
        return;
    }
    
    let promptParts = [];
    visualSelections.forEach(sel => {
        const fieldNameInput = document.getElementById(`vs-name-${sel.id}`);
        const name = fieldNameInput && fieldNameInput.value.trim() ? fieldNameInput.value.trim() : `the ${sel.tag} text`;
        promptParts.push(`Extract ${name} from "${sel.selector}"`);
    });
    
    const newPrompt = "Extract the following fields based on these exact selectors:\n" + promptParts.join('\n');
    document.getElementById('prompt_text').value = newPrompt;
    
    closeVisualPicker();
    startAIAnalysis();
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
            <td class="py-3 px-4 font-bold text-white">${f.field_name}</td>
            <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700 text-xs font-mono">${f.field_type}</span></td>
            <td class="py-3 px-4 text-blue-400 font-mono font-bold">${f.primary_selector}</td>
            <td class="py-3 px-4 text-slate-300 font-mono">${f.attribute_target || 'text'}</td>
            <td class="py-3 px-4 text-emerald-400 font-bold">${Math.round((f.confidence_score || 0.95) * 100)}%</td>
        `;
        tbody.appendChild(tr);
    });

    const finalAvg = fields.length > 0 ? Math.round((avgConfidence / fields.length) * 100) : 95;
    document.getElementById('badge-confidence').innerText = `Confidence: ${finalAvg}%`;
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