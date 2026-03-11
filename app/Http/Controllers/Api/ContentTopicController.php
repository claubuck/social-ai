<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * n8n Flujo 1: listado de temas para generar contenido con IA.
 *
 * Con API key y sin empresa → temas de TODAS las empresas, cada uno con company_id.
 * Con empresa (header/query/Sanctum) → solo temas de esa empresa.
 */
class ContentTopicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allTenants = $request->attributes->get('api_all_tenants', false);

        if ($allTenants) {
            $topics = ContentTopic::query()
                ->orderBy('company_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'company_id', 'topic'])
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'company_id' => $t->company_id,
                    'topic' => $t->topic,
                ])
                ->values()
                ->all();
            return response()->json($topics);
        }

        $companyId = $request->getCompanyId();
        if (! $companyId) {
            return response()->json([], 200);
        }

        $topics = ContentTopic::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'company_id', 'topic'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'company_id' => $t->company_id,
                'topic' => $t->topic,
            ])
            ->values()
            ->all();

        return response()->json($topics);
    }
}
