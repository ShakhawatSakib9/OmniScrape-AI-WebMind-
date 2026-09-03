<?php

use App\Http\Controllers\Api\DatasetApiController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// Dashboard & Control Plane Routes
Route::get('/', [ProjectController::class, 'index'])->name('dashboard');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/projects/infer-schema', [ProjectController::class, 'inferSchema'])->name('projects.infer');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
Route::post('/projects/{id}/run', [ProjectController::class, 'runNow'])->name('projects.run');
Route::get('/projects/{id}/api-docs', [ProjectController::class, 'apiDocs'])->name('projects.api-docs');
Route::get('/proxy/render', [ProjectController::class, 'proxyRender'])->name('proxy.render');

// Public Dynamic REST API Endpoints
Route::prefix('api/v1')->group(function () {
    Route::get('/datasets/{slug}', [DatasetApiController::class, 'show'])->name('api.datasets.show');
    Route::get('/datasets/{slug}/export', [DatasetApiController::class, 'export'])->name('api.datasets.export');
});