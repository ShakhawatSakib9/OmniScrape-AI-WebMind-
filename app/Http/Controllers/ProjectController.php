<?php

namespace App\Http\Controllers;

use App\Models\ExtractedRecord;
use App\Models\ExtractionRun;
use App\Models\ProjectSchema;
use App\Models\ProjectSelector;
use App\Models\ScrapingProject;
use App\Models\SelfHealingLog;
use App\Services\CrawlerService;
use App\Services\GeminiAIService;
use App\Services\SelfHealingService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    protected CrawlerService $crawler;
    protected GeminiAIService $ai;
    protected SelfHealingService $healing;
    protected WebhookService $webhooks;

    public function __construct(
        CrawlerService $crawler,
        GeminiAIService $ai,
        SelfHealingService $healing,
        WebhookService $webhooks
    ) {
        $this->crawler = $crawler;
        $this->ai = $ai;
        $this->healing = $healing;
        $this->webhooks = $webhooks;
    }

    public function index()
    {
        $projects = ScrapingProject::withCount('records')
            ->with(['runs' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('id', 'desc')
            ->get();

        $metrics = [
            'total_projects' => ScrapingProject::count(),
            'total_records' => ExtractedRecord::count(),
            'healed_count' => SelfHealingLog::count(),
            'active_crawlers' => ScrapingProject::where('status', 'active')->count(),
        ];

        $recentRuns = ExtractionRun::with('project')->latest()->limit(8)->get();
        $recentHeals = SelfHealingLog::with('project')->latest()->limit(5)->get();

        return view('dashboard.index', compact('projects', 'metrics', 'recentRuns', 'recentHeals'));
    }

    public function create()
    {
        return view('dashboard.create');
    }

    /**
     * AJAX Step 1: Infer Schema & Selectors from URL + Natural Language Prompt
     */
    public function inferSchema(Request $request)
    {
        $request->validate([
            'target_url' => 'required|url',
            'prompt' => 'required|string|min:5'
        ]);

        $url = $request->input('target_url');
        $prompt = $request->input('prompt');

        // 1. Fetch rendered & minified DOM
        $domResult = $this->crawler->fetchDom($url);

        if (!$domResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to render target website: ' . ($domResult['error'] ?? 'Unknown error')
            ], 422);
        }

        // 2. Infer Schema with Gemini AI
        $inference = $this->ai->inferSchemaAndSelectors($url, $prompt, $domResult['html']);

        return response()->json([
            'success' => true,
            'inference' => $inference,
            'page_title' => $domResult['title'] ?? '',
            'minified_length' => $domResult['minified_length'] ?? 0,
            'execution_time_ms' => $domResult['execution_time_ms'] ?? 0,
        ]);
    }

    /**
     * Save Project, Schemas & Selectors
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'required|url',
            'prompt' => 'required|string',
            'container_selector' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'frequency_cron' => 'nullable|string',
            'max_pages' => 'nullable|integer|min:1|max:20',
        ]);

        $project = ScrapingProject::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::lower(Str::random(5)),
            'target_url' => $request->target_url,
            'prompt' => $request->prompt,
            'container_selector' => $request->container_selector,
            'frequency_cron' => $request->frequency_cron ?? '0 8 * * *',
            'max_pages' => $request->max_pages ?? 1,
            'status' => 'active',
            'pagination_type' => $request->input('pagination.type', 'none'),
            'pagination_selector' => $request->input('pagination.selector', null),
        ]);

        foreach ($request->fields as $f) {
            $schema = ProjectSchema::create([
                'project_id' => $project->id,
                'field_name' => $f['field_name'],
                'field_label' => $f['field_label'] ?? ucwords(str_replace('_', ' ', $f['field_name'])),
                'field_type' => $f['field_type'] ?? 'string',
                'is_required' => $f['is_required'] ?? false,
                'description' => $f['description'] ?? null,
            ]);

            ProjectSelector::create([
                'project_id' => $project->id,
                'schema_id' => $schema->id,
                'field_name' => $f['field_name'],
                'primary_selector' => $f['primary_selector'],
                'fallback_selectors' => $f['fallback_selectors'] ?? [],
                'attribute_target' => $f['attribute_target'] ?? 'text',
                'confidence_score' => $f['confidence_score'] ?? 1.00,
                'status' => 'active',
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('projects.show', $project->id)
        ]);
    }

    public function show($id)
    {
        $project = ScrapingProject::with(['schemas', 'selectors', 'runs', 'healingLogs', 'webhooks'])
            ->withCount('records')
            ->findOrFail($id);

        $records = ExtractedRecord::where('project_id', $project->id)
            ->latest('id')
            ->paginate(15);

        return view('dashboard.show', compact('project', 'records'));
    }

    /**
     * Trigger immediate scraper execution with Self-Healing Watchdog
     */
    public function runNow($id)
    {
        $project = ScrapingProject::with(['schemas', 'selectors'])->findOrFail($id);
        $startTime = microtime(true);

        $run = ExtractionRun::create([
            'project_id' => $project->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        // 1. Run Playwright Crawler
        $extractResult = $this->crawler->extractData($project);

        if (!$extractResult['success']) {
            $run->update([
                'status' => 'failed',
                'error_log' => $extractResult['error'] ?? 'Extraction execution failed',
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Extraction failed: ' . ($extractResult['error'] ?? 'Unknown error'));
        }

        $rawRecords = $extractResult['records'] ?? [];

        // 2. Self-Healing Evaluation
        $healingResult = $this->healing->evaluateAndHeal($project, $rawRecords, $run);

        // If self-healing repaired selectors, re-run extraction once to get clean data
        if ($healingResult['healed_count'] > 0) {
            $extractResult = $this->crawler->extractData($project);
            $rawRecords = $extractResult['records'] ?? [];
        }

        // 3. Save / Upsert Records
        $newCount = 0;
        $updatedCount = 0;

        foreach ($rawRecords as $item) {
            $hash = hash('sha256', json_encode($item));

            $existing = ExtractedRecord::where('project_id', $project->id)
                ->where('record_hash', $hash)
                ->first();

            if ($existing) {
                $existing->update(['last_seen_at' => now()]);
                $updatedCount++;
            } else {
                ExtractedRecord::create([
                    'project_id' => $project->id,
                    'record_hash' => $hash,
                    'data_json' => $item,
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                    'status' => 'active',
                ]);
                $newCount++;
            }
        }

        $runtimeMs = (int) round((microtime(true) - $startTime) * 1000);

        $run->update([
            'status' => $healingResult['healed_count'] > 0 ? 'healed' : 'success',
            'records_extracted' => count($rawRecords),
            'records_new' => $newCount,
            'records_updated' => $updatedCount,
            'execution_time_ms' => $runtimeMs,
            'completed_at' => now(),
        ]);

        $project->update(['last_run_at' => now()]);

        // 4. Dispatch Webhooks
        if ($newCount > 0) {
            $this->webhooks->dispatchProjectEvent($project, 'new_records', ['new_records_count' => $newCount]);
        }
        if ($healingResult['healed_count'] > 0) {
            $this->webhooks->dispatchProjectEvent($project, 'self_healing', ['repaired_fields' => $healingResult['healed_fields']]);
        }

        $msg = "Scraper finished: " . count($rawRecords) . " records extracted (" . $newCount . " new).";
        if ($healingResult['healed_count'] > 0) {
            $msg .= " 🛡️ Self-Healing automatically repaired " . $healingResult['healed_count'] . " selector(s)!";
        }

        return back()->with('success', $msg);
    }

    public function apiDocs($id)
    {
        $project = ScrapingProject::with(['schemas'])->findOrFail($id);
        return view('dashboard.api_docs', compact('project'));
    }

    public function proxyRender(\Illuminate\Http\Request $request)
    {
        $url = $request->query('url');
        if (!$url) return response('Missing URL', 400);

        // Run crawler in proxy mode
        $nodePath = 'node';
        $scriptPath = base_path('bin/crawler.cjs');
        
        $config = json_encode(['url' => $url, 'timeout' => 15000]);
        $escapedConfig = addslashes($config);
        
        $cmd = "\"{$nodePath}\" \"{$scriptPath}\" fetch-proxy-dom \"{$escapedConfig}\"";
        
        $process = \Symfony\Component\Process\Process::fromShellCommandline($cmd);
        $process->setTimeout(30);
        $process->run();
        
        if (!$process->isSuccessful()) {
            return response('Proxy fetch failed', 500);
        }

        $output = json_decode($process->getOutput(), true);
        if (!$output || !$output['success']) {
            return response('Proxy extraction failed', 500);
        }

        // Add OmniPicker JS and return
        $html = $output['html'];
        $pickerScript = "<script src='/js/omnipicker.js'></script>";
        $html = str_replace('</body>', $pickerScript . '</body>', $html);

        return response($html);
    }
}