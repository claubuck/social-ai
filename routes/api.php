<?php

use App\Http\Controllers\Api\AiGeneratePostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ContentTopicController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (n8n + frontend)
|--------------------------------------------------------------------------
|
| Autenticación: (1) API key en .env (N8N_API_KEY) + header X-Company-Id o N8N_COMPANY_ID,
|                (2) o Bearer token Sanctum (usuario con company_id).
| Flujo 1 n8n: GET /api/content-topics → OpenAI → POST /api/posts
| Flujo 2 n8n: GET /api/posts/pending → publicar en red → POST /api/posts/{id}/mark-published
| Flujo 3 n8n: Webhook comentario → OpenAI → POST /api/comments/{id}/reply
|
*/

Route::middleware('auth.api')->group(function (): void {
    // --- Flujo 1: temas para generar contenido ---
    Route::get('/content-topics', [ContentTopicController::class, 'index'])
        ->name('api.content-topics.index');

    Route::post('/ai/generate-post', [AiGeneratePostController::class, 'store'])
        ->name('api.ai.generate-post');

    // --- Posts ---
    Route::post('/posts', [PostController::class, 'store'])->name('api.posts.store');
    Route::post('/posts/schedule', [PostController::class, 'schedule'])->name('api.posts.schedule');
    Route::get('/posts/pending', [PostController::class, 'pending'])->name('api.posts.pending');
    Route::post('/posts/{post}/mark-published', [PostController::class, 'markPublished'])
        ->name('api.posts.mark-published');
    // Alias para n8n: POST /api/posts/{id}/published
    Route::post('/posts/{post}/published', [PostController::class, 'markPublished'])
        ->name('api.posts.published');

    // --- Flujo 3: comentarios (registrar + responder con IA desde n8n) ---
    Route::post('/comments', [CommentController::class, 'store'])->name('api.comments.store');
    Route::post('/comments/{comment}/reply', [CommentController::class, 'reply'])
        ->name('api.comments.reply');
});
