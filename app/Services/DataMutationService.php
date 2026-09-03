<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\ExtractedRecord;
use App\Models\RecordHistory;
use App\Models\ScrapingProject;
use Illuminate\Support\Facades\Log;

class DataMutationService
{
    protected WebhookService $webhooks;

    public function __construct(WebhookService $webhooks)
    {
        $this->webhooks = $webhooks;
    }

    /**
     * Compare incoming record against existing records and track mutation deltas.
     */
    public function trackMutations(ScrapingProject $project, array $newRecords): array
    {
        $mutations = [];
        $rules = AlertRule::where('project_id', $project->id)->where('is_active', true)->get();

        foreach ($newRecords as $item) {
            // Find possible match by title/name or unique key
            $keyField = isset($item['title']) ? 'title' : (isset($item['name']) ? 'name' : null);
            if (!$keyField || empty($item[$keyField])) continue;

            $existing = ExtractedRecord::where('project_id', $project->id)
                ->whereJsonContains("data_json->$keyField", $item[$keyField])
                ->first();

            if (!$existing) continue;

            $oldData = $existing->data_json;

            // Check field differences
            foreach ($item as $field => $newVal) {
                $oldVal = $oldData[$field] ?? null;

                if ($oldVal !== null && $newVal !== null && $oldVal != $newVal) {
                    $changeType = 'modified';
                    $pctDelta = null;

                    // Numeric / Price mutation check
                    if (is_numeric($oldVal) && is_numeric($newVal)) {
                        $oldNum = (float) $oldVal;
                        $newNum = (float) $newVal;

                        if ($oldNum > 0) {
                            $pctDelta = round((($newNum - $oldNum) / $oldNum) * 100, 2);
                        }

                        if ($newNum < $oldNum) {
                            $changeType = 'price_drop';
                        } elseif ($newNum > $oldNum) {
                            $changeType = 'price_increase';
                        }
                    } elseif (str_contains(strtolower($field), 'stock') || str_contains(strtolower($field), 'availability')) {
                        $changeType = 'stock_change';
                    }

                    $history = RecordHistory::create([
                        'project_id' => $project->id,
                        'record_id' => $existing->id,
                        'field_name' => $field,
                        'old_value' => (string) $oldVal,
                        'new_value' => (string) $newVal,
                        'change_type' => $changeType,
                        'percentage_delta' => $pctDelta
                    ]);

                    $mutations[] = $history;

                    // Evaluate Smart Alerts
                    $this->evaluateAlerts($rules, $project, $item, $field, $oldVal, $newVal, $pctDelta);
                }
            }
        }

        return $mutations;
    }

    protected function evaluateAlerts($rules, ScrapingProject $project, array $item, string $field, $oldVal, $newVal, ?float $pctDelta): void
    {
        foreach ($rules as $rule) {
            if ($rule->field_name !== $field && $rule->field_name !== '*') continue;

            $matched = false;
            $op = $rule->operator;
            $target = $rule->target_value;

            if ($op === '<' && is_numeric($newVal) && (float)$newVal < (float)$target) {
                $matched = true;
            } elseif ($op === '>' && is_numeric($newVal) && (float)$newVal > (float)$target) {
                $matched = true;
            } elseif ($op === 'drops_by_percent' && $pctDelta !== null && $pctDelta <= -abs((float)$target)) {
                $matched = true;
            } elseif ($op === 'contains' && str_contains(strtolower((string)$newVal), strtolower($target))) {
                $matched = true;
            }

            if ($matched) {
                $rule->update(['last_triggered_at' => now()]);
                
                $payload = [
                    'event' => 'alert_triggered',
                    'rule' => $rule->rule_name,
                    'field' => $field,
                    'old_value' => $oldVal,
                    'new_value' => $newVal,
                    'delta_percent' => $pctDelta ? "{$pctDelta}%" : null,
                    'item' => $item
                ];

                Log::info("OmniScrape Alert [{$rule->rule_name}] Triggered for project #{$project->id}", $payload);

                $this->webhooks->dispatchProjectEvent($project, 'smart_alert', $payload);
            }
        }
    }
}