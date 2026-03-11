<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envía a n8n la solicitud de generar contenido (webhook).
 * n8n genera el texto con IA y llama a Laravel POST /api/posts para crear y publicar.
 */
class N8nWebhookService
{
    public function requestContentGeneration(array $payload): bool
    {
        $url = config('services.n8n.webhook_generate_url');
        if (! $url) {
            Log::warning('N8N_WEBHOOK_GENERATE_URL not set. Skip sending to n8n.');
            return false;
        }

        $response = Http::timeout(30)->post($url, $payload);

        if (! $response->successful()) {
            Log::warning('n8n webhook failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;
    }
}
