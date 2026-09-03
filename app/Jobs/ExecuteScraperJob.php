<?php

namespace App\Jobs;

use App\Models\ExtractionRun;
use App\Models\ExtractedRecord;
use App\Models\ScrapingProject;
use App\Services\CrawlerService;
use App\Services\SelfHealingService;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteScraperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [10, 30, 90];

    protected int $projectId;
    protected ?int $runId;

    public function __construct(int $projectId, ?int $runId = null)
    {
        $this->projectId = $projectId;
        $this->runId = $runId;
    }

    public function handle(CrawlerService $crawler, SelfHealingService $healing, WebhookService $webhooks): void
    {
        $project = ScrapingProject::with(['schemas', 'selectors'])->find($this->projectId);
        if (!$project) return;

        $startTime = microtime(true);
        $run = $this->runId ? ExtractionRun::find($this->runId) : ExtractionRun::create([
            'project_id' => $project->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $extractResult = $crawler->extractData($project);

        if (!$extractResult['success']) {
            $run->update([
                'status' => 'failed',
                'error_log' => $extractResult['error'] ?? 'Crawler execution failed',
                'completed_at' => now(),
            ]);
            return;
        }

        $rawRecords = $extractResult['records'] ?? [];
        $healingResult = $healing->evaluateAndHeal($project, $rawRecords, $run);

        if ($healingResult['healed_count'] > 0) {
            $extractResult = $crawler->extractData($project);
            $rawRecords = $extractResult['records'] ?? [];
        }

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

        if ($newCount > 0) {
            $webhooks->dispatchProjectEvent($project, 'new_records', ['new_records_count' => $newCount]);
        }
        if ($healingResult['healed_count'] > 0) {
            $webhooks->dispatchProjectEvent($project, 'self_healing', ['repaired_fields' => $healingResult['healed_fields']]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ExecuteScraperJob failed for project #{$this->projectId}: " . $exception->getMessage());
        if ($this->runId) {
            ExtractionRun::where('id', $this->runId)->update([
                'status' => 'failed',
                'error_log' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}