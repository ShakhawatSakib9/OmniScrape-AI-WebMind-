@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="pb-3 border-b border-surface-border">
        <h1 class="text-xl font-semibold text-white tracking-tight">
            Create Data Extraction Agent
        </h1>
        <p class="text-slate-400 text-xs mt-0.5">
            Configure an autonomous scraper using a natural language prompt or interactive visual selector.
        </p>
    </div>

    <!-- Step 1: Input Form -->
    <div id="step-prompt" class="enterprise-card rounded-xl p-5 space-y-5">
        <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider font-mono mb-1.5">
                Target Website URL
            </label>
            <div class="relative">
                <input type="url" id="target_url" placeholder="https://quotes.toscrape.com/" value="https://quotes.toscrape.com/" class="w-full px-3.5 py-2.5 rounded-lg enterprise-input text-xs font-mono placeholder:text-slate-600 transition" required>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider font-mono mb-1.5">
                Extraction Specification Prompt
            </label>
            <textarea id="prompt_text" rows="3" placeholder="Describe the data you want extracted..." class="w-full p-3 rounded-lg enterprise-input text-xs placeholder:text-slate-600 transition" required>Extract all quote texts, author names, and author profile links from this page.</textarea>
            
            <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-slate-500 font-medium text-[11px]">Quick Presets:</span>
                <button type="button" onclick="setPreset('https://quotes.toscrape.com/', 'Extract all quote texts, author names, and author profile links from this page.')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono text-[11px] border border-slate-700 transition">
                    Quotes & Authors
                </button>
                <button type="button" onclick="setPreset('https://books.toscrape.com/', 'Extract book titles, prices in GBP, star ratings, and product detail URLs.')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono text-[11px] border border-slate-700 transition">
                    E-Commerce Books
                </button>
            </div>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-surface-border">
            <a href="{{ route('dashboard') }}" class="text-xs font-medium text-slate-400 hover:text-slate-200">
                Cancel
            </a>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <button type="button" onclick="openVisualPicker()" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-crosshairs text-[11px] text-blue-400"></i>
                    <span>Visual Picker</span>
                </button>
                <button type="button" id="btn-analyze" onclick="startAIAnalysis()" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition flex items-center gap-1.5 flex-grow sm:flex-grow-0 justify-center">
                    <i class="fa-solid fa-brain text-[11px]"></i>
                    <span>Analyze DOM & Infer Schema</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Visual Picker Modal (Full Screen) -->
    <div id="visual-picker-modal" class="fixed inset-0 z-[100] hidden bg-surface-base flex flex-col">
        <!-- Header -->
        <div class="h-12 border-b border-surface-border bg-surface-card flex items-center justify-between px-5 shrink-0">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-crosshairs text-blue-400 text-sm"></i>
                <div>
                    <h2 class="text-white font-medium text-xs">Visual Selector Workspace</h2>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="closeVisualPicker()" class="px-3 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition border border-slate-700">
                    Close
                </button>
                <button onclick="applyVisualSelections()" class="px-3 py-1 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium shadow-sm transition">
                    Apply Selections
                </button>
            </div>
        </div>
        
        <!-- Workspace -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Iframe Canvas -->
            <div class="flex-1 bg-white relative">
                <div id="vp-loading" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center z-10 text-slate-200">
                    <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 text-blue-400"></i>
                    <p class="font-mono text-xs font-medium text-slate-300">Rendering DOM Sandbox...</p>
                </div>
                <iframe id="vp-iframe" class="w-full h-full border-0" sandbox="allow-same-origin allow-scripts"></iframe>
            </div>
            
            <!-- Sidebar -->
            <div class="w-80 bg-surface-card border-l border-surface-border flex flex-col shrink-0">
                <div class="p-3.5 border-b border-surface-border">
                    <h3 class="text-xs font-semibold text-white uppercase font-mono tracking-wider">Selected Elements</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Click elements inside the iframe to capture selectors.</p>
                </div>
                <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5" id="vp-selections-list">
                    <div class="text-center text-slate-500 text-xs py-8 border border-dashed border-slate-800 rounded-lg">
                        No elements selected yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="hidden enterprise-card rounded-xl p-10 text-center space-y-3">
        <div class="w-10 h-10 rounded-lg bg-blue-950/40 text-blue-400 flex items-center justify-center text-lg mx-auto border border-blue-800/40">
            <i class="fa-solid fa-spinner animate-spin text-sm"></i>
        </div>
        <h3 class="text-sm font-semibold text-white">Analyzing Live DOM</h3>
        <p class="text-xs font-mono text-slate-400 max-w-sm mx-auto">
            Rendering page via Headless Chromium, minifying DOM, and prompting Gemini 2.5 for schema inference...
        </p>
    </div>

    <!-- Step 2: Schema Review -->
    <div id="step-review" class="hidden enterprise-card rounded-xl p-5 space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-surface-border">
            <div>
                <h2 class="text-sm font-semibold text-white">
                    Inferred Schema & Selectors
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Review generated selectors and field mappings before deploying.</p>
            </div>
            <span id="badge-confidence" class="px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-emerald-950/50 text-emerald-400 border border-emerald-800/50">
                Confidence: 98%
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono text-slate-400 uppercase mb-1">Dataset Name</label>
                <input type="text" id="project_name" class="w-full px-3 py-2 rounded-lg enterprise-input text-xs font-medium">
            </div>
            <div>
                <label class="block text-xs font-mono text-slate-400 uppercase mb-1">Repeating Container Selector</label>
                <input type="text" id="container_selector" class="w-full px-3 py-2 rounded-lg enterprise-input text-blue-400 font-mono text-xs">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Discovered Fields
            </label>
            <div class="overflow-x-auto rounded-lg border border-surface-border">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase border-b border-surface-border text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3">Field Name</th>
                            <th class="py-2.5 px-3">Type</th>
                            <th class="py-2.5 px-3">Primary Selector</th>
                            <th class="py-2.5 px-3">Attribute</th>
                            <th class="py-2.5 px-3">Confidence</th>
                        </tr>
                    </thead>
                    <tbody id="fields-table-body" class="divide-y divide-surface-border"></tbody>
                </table>
            </div>
        </div>

        <div class="pt-3 flex items-center justify-between border-t border-surface-border">
            <button type="button" onclick="resetForm()" class="text-xs font-medium text-slate-400 hover:text-slate-300">
                Start Over
            </button>
            <button type="button" id="btn-save-project" onclick="saveAndDeployProject()" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-check text-[10px]"></i>
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
        list.innerHTML = `<div class="text-center text-slate-500 text-xs py-8 border border-dashed border-slate-800 rounded-lg">No elements selected yet.</div>`;
        return;
    }
    
    let html = '';
    visualSelections.forEach((sel) => {
        html += `
        <div class="p-2.5 bg-slate-900 rounded-lg border border-slate-800 relative group">
            <button onclick="removeSelection(${sel.id})" class="absolute top-2 right-2 text-slate-500 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition">
                <i class="fa-solid fa-xmark text-[10px]"></i>
            </button>
            <div class="text-[10px] font-mono text-blue-400 mb-0.5 truncate pr-5">${sel.selector}</div>
            <div class="text-[11px] text-slate-300 italic truncate border-l border-blue-500 pl-1.5">"${sel.text}"</div>
            <input type="text" id="vs-name-${sel.id}" placeholder="Field name (e.g. price)" class="mt-1.5 w-full enterprise-input rounded px-2 py-1 text-xs placeholder:text-slate-600">
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
        tr.className = 'hover:bg-slate-800/30';
        tr.innerHTML = `
            <td class="py-2.5 px-3 font-medium text-slate-200">${f.field_name}</td>
            <td class="py-2.5 px-3"><span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700 text-[10px]">${f.field_type}</span></td>
            <td class="py-2.5 px-3 text-blue-400 font-mono">${f.primary_selector}</td>
            <td class="py-2.5 px-3 text-slate-400">${f.attribute_target || 'text'}</td>
            <td class="py-2.5 px-3 text-emerald-400 font-semibold">${Math.round((f.confidence_score || 0.95) * 100)}%</td>
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