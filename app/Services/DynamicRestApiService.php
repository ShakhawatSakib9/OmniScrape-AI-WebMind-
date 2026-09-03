<?php

namespace App\Services;

use App\Models\ExtractedRecord;
use App\Models\ScrapingProject;
use Illuminate\Http\Request;

class DynamicRestApiService
{
    public function queryDataset(ScrapingProject $project, Request $request): array
    {
        $query = ExtractedRecord::where('project_id', $project->id)
            ->where('status', 'active');

        // 1. Text Search across JSON payload
        if ($search = $request->input('search')) {
            $query->where('data_json', 'LIKE', "%{$search}%");
        }

        // 2. Field-specific Filtering (?filter[field]=val or ?filter[price_min]=50000)
        if ($filters = $request->input('filter')) {
            if (is_array($filters)) {
                foreach ($filters as $field => $val) {
                    if (str_ends_with($field, '_min')) {
                        $actualField = substr($field, 0, -4);
                        $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.{$actualField}')) >= ?", [(float)$val]);
                    } elseif (str_ends_with($field, '_max')) {
                        $actualField = substr($field, 0, -4);
                        $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.{$actualField}')) <= ?", [(float)$val]);
                    } else {
                        $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.{$field}')) = ?", [$val]);
                    }
                }
            }
        }

        // 3. Sorting (?sort=-price or ?sort=author)
        $sort = $request->input('sort', '-id');
        $descending = str_starts_with($sort, '-');
        $sortField = ltrim($sort, '-');

        if ($sortField === 'id' || $sortField === 'created_at') {
            $query->orderBy($sortField, $descending ? 'desc' : 'asc');
        } else {
            $direction = $descending ? 'desc' : 'asc';
            $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.{$sortField}')) {$direction}");
        }

        // 4. Pagination
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $paginator = $query->paginate($perPage);

        $records = collect($paginator->items())->map(function ($item) {
            return array_merge([
                '_id' => $item->id,
                '_record_hash' => $item->record_hash,
                '_first_seen_at' => $item->first_seen_at?->toISOString(),
                '_last_seen_at' => $item->last_seen_at?->toISOString(),
            ], $item->data_json);
        });

        return [
            'success' => true,
            'dataset' => [
                'name' => $project->name,
                'slug' => $project->slug,
                'target_url' => $project->target_url,
                'total_records' => $paginator->total(),
                'last_synced_at' => $project->last_run_at?->toISOString(),
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
            'data' => $records
        ];
    }
}