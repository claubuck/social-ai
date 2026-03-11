<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API multi-tenant: cada request va a una empresa (company_id).
 *
 * 1) API key (.env N8N_API_KEY): un solo key; la empresa por header X-Company-Id
 *    en cada request (varias empresas). Opcional N8N_COMPANY_ID para un solo tenant.
 *
 * 2) Token Sanctum: empresa = user->company_id.
 */
class AuthenticateApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        $apiKey = config('services.n8n.api_key');

        if ($apiKey && $bearer === $apiKey) {
            $companyId = $this->resolveCompanyId($request);
            if ($companyId !== null) {
                if (! Company::where('id', $companyId)->exists()) {
                    return response()->json([
                        'message' => "La empresa con ID {$companyId} no existe.",
                    ], 404);
                }
                $request->attributes->set('api_company_id', $companyId);
            } else {
                // Sin company: permitido para endpoints que devuelven datos de todas las empresas (ej. listado de topics)
                $request->attributes->set('api_all_tenants', true);
            }
            return $next($request);
        }

        // Sin API key: autenticar con Sanctum (token de usuario)
        $user = auth()->guard('sanctum')->user($request);
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    protected function resolveCompanyId(Request $request): ?int
    {
        $header = $request->header('X-Company-Id');
        if ($header !== null && $header !== '') {
            return (int) $header;
        }
        $query = $request->query('company_id');
        if ($query !== null && $query !== '') {
            return (int) $query;
        }
        return config('services.n8n.company_id');
    }
}
