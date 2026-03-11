<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Services\N8nWebhookService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Generar publicación" manual: envía tema a n8n por webhook.
 * n8n genera el contenido y llama a Laravel POST /api/posts; Laravel publica.
 */
class GeneratePostController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user->company_id) {
            return Inertia::render('GeneratePost/Index', [
                'hasCompany' => false,
                'platforms' => SocialAccount::$platforms,
                'webhookConfigured' => (bool) config('services.n8n.webhook_generate_url'),
                'flash' => ['success' => session('success'), 'errors' => session('errors')?->getMessages()],
            ]);
        }

        return Inertia::render('GeneratePost/Index', [
            'hasCompany' => true,
            'companyId' => $user->company_id,
            'platforms' => SocialAccount::$platforms,
            'webhookConfigured' => (bool) config('services.n8n.webhook_generate_url'),
            'flash' => ['success' => session('success'), 'errors' => session('errors')?->getMessages()],
        ]);
    }

    public function store(Request $request, N8nWebhookService $n8n): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'topic' => 'required|string|max:1000',
            'platform' => 'required|string|in:' . implode(',', SocialAccount::$platforms),
            'publish_immediately' => 'nullable|boolean',
            'publish_at' => 'nullable|date',
        ]);

        $user = $request->user();
        if (! $user->company_id) {
            return back()->withErrors(['topic' => 'Debes pertenecer a una empresa.']);
        }

        $url = config('services.n8n.webhook_generate_url');
        if (! $url) {
            return back()->withErrors(['topic' => 'No está configurado N8N_WEBHOOK_GENERATE_URL en .env.']);
        }

        $payload = [
            'topic' => $request->input('topic'),
            'company_id' => $user->company_id,
            'platform' => $request->input('platform'),
            'publish_immediately' => $request->boolean('publish_immediately'),
            'publish_at' => $request->input('publish_at'),
        ];

        $ok = $n8n->requestContentGeneration($payload);

        if (! $ok) {
            return back()->withErrors(['topic' => 'No se pudo enviar la solicitud a n8n. Revisa la URL del webhook y los logs.']);
        }

        return back()->with('success', 'Solicitud enviada a n8n. Cuando genere el contenido, el post se creará y publicará en Laravel.');
    }
}
