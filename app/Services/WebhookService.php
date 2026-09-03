<?php

namespace App\Services;

use App\Models\ScrapingProject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function dispatchProjectEvent(ScrapingProject $project, string $eventType, array $payload): void
    {
        $webhooks = $project->webhooks()->where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if ($eventType === 'new_records' && !$webhook->event_on_new_records) continue;
            if ($eventType === 'updated_records' && !$webhook->event_on_updated_records) continue;
            if ($eventType === 'self_healing' && !$webhook->event_on_self_healing) continue;

            $this->sendPayload($webhook->target_url, $webhook->secret, [
                'event' => $eventType,
                'project_slug' => $project->slug,
                'timestamp' => now()->toISOString(),
                'payload' => $payload
            ]);
        }
    }

    protected function sendPayload(string $url, ?string $secret, array $data): void
    {
        try {
            $json = json_encode($data);
            $headers = ['Content-Type' => 'application/json'];
            if ($secret) {
                $headers['X-OmniScrape-Signature'] = hash_hmac('sha256', $json, $secret);
            }

            Http::withHeaders($headers)->timeout(10)->post($url, $data);
        } catch (\Exception $e) {
            Log::error("Webhook delivery failed for {$url}: " . $e->getMessage());
        }
    }
}