<?php

use App\Http\Controllers\Api\AiGeneratePostController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api and use the sanctum auth guard.
| Ensure the authenticated user has a company for tenant-scoped actions.
|
*/

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/ai/generate-post', [AiGeneratePostController::class, 'store'])
        ->name('api.ai.generate-post');

    Route::post('/posts', [PostController::class, 'store'])->name('api.posts.store');
    Route::post('/posts/schedule', [PostController::class, 'schedule'])->name('api.posts.schedule');
    Route::get('/posts/pending', [PostController::class, 'pending'])->name('api.posts.pending');
    Route::post('/posts/{post}/mark-published', [PostController::class, 'markPublished'])
        ->name('api.posts.mark-published');
});
