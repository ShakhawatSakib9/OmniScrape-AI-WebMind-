<?php

namespace App\Services;

use App\Models\ExtractionRun;
use App\Models\ProjectSelector;
use App\Models\ScrapingProject;
use App\Models\SelfHealingLog;
use Illuminate\Support\Facades\Log;

class SelfHealingService
{
    protected CrawlerService $crawler;
    protected GeminiAIService $ai;

    public function __construct(CrawlerService $crawler, GeminiAIService $ai)
    {
        $this->crawler = $crawler;
        $this->ai = $ai;
    }

    /**
     * Inspects extracted records and triggers self-healing if degradation is detected
     */
    public function evaluateAndHeal(ScrapingProject $project, array $extractedRecords, ExtractionRun $run): array
    {
        $project->loadMissing(['schemas', 'selectors']);
        $totalRecords = count($extractedRecords);

        if ($totalRecords === 0) {
            return $this->triggerFullProjectHealing($project, $run);
        }

        $healedFields = [];

        foreach ($project->selectors as $selector) {
            $schema = $selector->schema;
            $fieldName = $selector->field_name;

            // Calculate fill rate
            $populatedCount = 0;
            foreach ($extractedRecords as $record) {
                if (!empty($record[$fieldName])) {
                    $populatedCount++;
                }
            }

            $fillRate = $totalRecords > 0 ? ($populatedCount / $totalRecords) : 0.0;

            // Trigger healing if required field fill rate < 60%
            if ($schema && $schema->is_required && $fillRate < 0.60) {
                Log::warning("OmniScrape Self-Healing Triggered: Field '{$fieldName}' degraded with fill rate {$fillRate}");
                $healResult = $this->healSingleSelector($project, $selector, $run);
                if ($healResult['success']) {
                    $healedFields[] = $fieldName;
                }
            }
        }

        return [
            'healed_count' => count($healedFields),
            'healed_fields' => $healedFields
        ];
    }

    public function healSingleSelector(ScrapingProject $project, ProjectSelector $selector, ?ExtractionRun $run = null): array
    {
        // 1. Fetch fresh minified DOM
        $domResult = $this->crawler->fetchDom($project->target_url);
        if (!$domResult['success']) {
            return ['success' => false, 'error' => 'Failed to fetch DOM for self-healing'];
        }

        // 2. Call AI diagnosis
        $diagnosis = $this->ai->diagnoseAndRepairBrokenSelector(
            $selector->field_name,
            $selector->primary_selector,
            $domResult['html'],
            $project->container_selector
        );

        $candidates = array_merge([$diagnosis['repaired_selector']], $diagnosis['fallback_selectors'] ?? []);
        $candidates = array_filter(array_unique($candidates));

        // 3. Test candidates using Playwright
        $evalResult = $this->crawler->testSelectors($project->target_url, $project->container_selector, $candidates);

        $bestCandidate = null;
        $highestConfidence = 0.0;
        $sampleValue = null;

        if ($evalResult['success'] && !empty($evalResult['evaluations'])) {
            foreach ($evalResult['evaluations'] as $eval) {
                if ($eval['confidence_score'] > $highestConfidence) {
                    $highestConfidence = $eval['confidence_score'];
                    $bestCandidate = $eval['selector'];
                    $sampleValue = $eval['samples'][0] ?? null;
                }
            }
        }

        if ($bestCandidate && $highestConfidence >= 0.70) {
            $oldSelector = $selector->primary_selector;
            $oldConfidence = $selector->confidence_score;

            // Update selector in DB
            $selector->update([
                'primary_selector' => $bestCandidate,
                'fallback_selectors' => array_diff($candidates, [$bestCandidate]),
                'confidence_score' => $highestConfidence,
                'status' => 'repaired',
            ]);

            // Log Self-Healing Audit Event
            SelfHealingLog::create([
                'project_id' => $project->id,
                'run_id' => $run?->id,
                'field_name' => $selector->field_name,
                'broken_selector' => $oldSelector,
                'repaired_selector' => $bestCandidate,
                'old_confidence' => $oldConfidence,
                'new_confidence' => $highestConfidence,
                'sample_extracted_value' => is_array($sampleValue) ? json_encode($sampleValue) : (string)$sampleValue,
                'reasoning_log' => $diagnosis['reasoning'] ?? 'Autonomous DOM analysis and candidate validation.'
            ]);

            return [
                'success' => true,
                'repaired_selector' => $bestCandidate,
                'confidence' => $highestConfidence
            ];
        }

        return ['success' => false, 'error' => 'No reliable replacement selector found with confidence >= 0.70'];
    }

    protected function triggerFullProjectHealing(ScrapingProject $project, ExtractionRun $run): array
    {
        $domResult = $this->crawler->fetchDom($project->target_url);
        if (!$domResult['success']) return ['success' => false];

        $reInferred = $this->ai->inferSchemaAndSelectors($project->target_url, $project->prompt, $domResult['html']);
        if (!empty($reInferred['container_selector'])) {
            $project->update(['container_selector' => $reInferred['container_selector']]);
        }

        return ['success' => true, 're_inferred' => true];
    }
}