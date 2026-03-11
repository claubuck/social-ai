<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContentTopicController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\GeneratePostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAccountController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Crear empresa (para usuarios sin company_id)
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    // Token de API para n8n (Bearer token)
    Route::post('/api-token', [ApiTokenController::class, 'store'])->name('api-token.store');

    // Generar publicación manual (envía tema a n8n webhook; n8n genera y vuelve a Laravel)
    Route::get('/generate-post', [GeneratePostController::class, 'index'])->name('generate-post.index');
    Route::post('/generate-post', [GeneratePostController::class, 'store'])->name('generate-post.store');

    // Facebook OAuth (conectar páginas e Instagram sin pedir token manual)
    Route::get('/facebook/connect', [FacebookAuthController::class, 'connect'])->name('facebook.connect');
    Route::get('/facebook/callback', [FacebookAuthController::class, 'callback'])->name('facebook.callback');

    // Cuentas de redes sociales (credenciales por empresa)
    Route::get('/social-accounts', [SocialAccountController::class, 'index'])->name('social-accounts.index');
    Route::get('/social-accounts/create', [SocialAccountController::class, 'create'])->name('social-accounts.create');
    Route::post('/social-accounts', [SocialAccountController::class, 'store'])->name('social-accounts.store');
    Route::get('/social-accounts/{socialAccount}/edit', [SocialAccountController::class, 'edit'])->name('social-accounts.edit');
    Route::put('/social-accounts/{socialAccount}', [SocialAccountController::class, 'update'])->name('social-accounts.update');
    Route::delete('/social-accounts/{socialAccount}', [SocialAccountController::class, 'destroy'])->name('social-accounts.destroy');

    // Temas de contenido para n8n (Flujo 1: generar posts con IA)
    Route::get('/content-topics', [ContentTopicController::class, 'index'])->name('content-topics.index');
    Route::post('/content-topics', [ContentTopicController::class, 'store'])->name('content-topics.store');
    Route::put('/content-topics/{contentTopic}', [ContentTopicController::class, 'update'])->name('content-topics.update');
    Route::delete('/content-topics/{contentTopic}', [ContentTopicController::class, 'destroy'])->name('content-topics.destroy');
});

require __DIR__.'/auth.php';
