<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExtractedRecord;
use App\Models\ScrapingProject;
use App\Services\DynamicRestApiService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetApiController extends Controller
{
    protected DynamicRestApiService $apiService;

    public function __construct(DynamicRestApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function show(string $slug, Request $request)
    {
        $project = ScrapingProject::where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'error' => 'Dataset not found for slug: ' . $slug
            ], 404);
        }

        $result = $this->apiService->queryDataset($project, $request);
        return response()->json($result);
    }

    public function export(string $slug, Request $request)
    {
        $project = ScrapingProject::where('slug', $slug)->firstOrFail();
        $format = $request->input('format', 'json');

        $records = ExtractedRecord::where('project_id', $project->id)
            ->where('status', 'active')
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($project, $records);
        }

        return response()->json([
            'dataset' => $project->name,
            'total_records' => $records->count(),
            'data' => $records->pluck('data_json')
        ]);
    }

    protected function exportCsv(ScrapingProject $project, $records): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$project->slug}-export.csv\"",
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            
            if ($records->isNotEmpty()) {
                $firstRow = $records->first()->data_json;
                fputcsv($handle, array_keys($firstRow));

                foreach ($records as $record) {
                    fputcsv($handle, array_values($record->data_json));
                }
            }
            fclose($handle);
        }, 200, $headers);
    }
}