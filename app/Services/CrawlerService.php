<?php

namespace App\Services;

use App\Models\ScrapingProject;
use Symfony\Component\Process\Process;

class CrawlerService
{
    protected string $nodePath = 'node';
    protected string $scriptPath;

    public function __construct()
    {
        $this->scriptPath = base_path('bin/crawler.cjs');
    }

    public function fetchDom(string $url, int $timeout = 30000): array
    {
        $payload = json_encode(['url' => $url, 'timeout' => $timeout]);
        return $this->executeNode('fetch-dom', $payload);
    }

    public function extractData(ScrapingProject $project): array
    {
        $project->loadMissing(['schemas', 'selectors']);

        $selectorsConfig = [];
        foreach ($project->selectors as $selector) {
            $schema = $selector->schema;
            $selectorsConfig[$selector->field_name] = [
                'selector' => $selector->primary_selector,
                'attr' => $selector->attribute_target ?? 'text',
                'field_type' => $schema ? $schema->field_type : 'string',
            ];
        }

        $payload = json_encode([
            'url' => $project->target_url,
            'container_selector' => $project->container_selector,
            'pagination_type' => $project->pagination_type,
            'pagination_selector' => $project->pagination_selector,
            'max_pages' => $project->max_pages ?? 1,
            'selectors' => $selectorsConfig,
        ]);

        return $this->executeNode('extract-data', $payload);
    }

    public function testSelectors(string $url, ?string $containerSelector, array $candidates): array
    {
        $payload = json_encode([
            'url' => $url,
            'container_selector' => $containerSelector,
            'candidates' => $candidates,
        ]);

        return $this->executeNode('test-selector', $payload);
    }

    protected function executeNode(string $mode, string $jsonPayload): array
    {
        $tempConfigFile = tempnam(sys_get_temp_dir(), 'omni_cfg_');
        file_put_contents($tempConfigFile, $jsonPayload);

        $process = new Process([$this->nodePath, $this->scriptPath, $mode, $tempConfigFile]);
        $process->setTimeout(90);
        $process->run();

        if (file_exists($tempConfigFile)) {
            @unlink($tempConfigFile);
        }

        $output = trim($process->getOutput());
        $decoded = json_decode($output, true);

        if (!$decoded) {
            return [
                'success' => false,
                'error' => $process->getErrorOutput() ?: 'Invalid crawler response',
                'raw_output' => $output
            ];
        }

        return $decoded;
    }
}