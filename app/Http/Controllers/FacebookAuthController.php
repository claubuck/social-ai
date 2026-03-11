<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OAuth con Facebook: el usuario autoriza la app y guardamos tokens de página(s) e Instagram.
 */
class FacebookAuthController extends Controller
{
    private const FB_SCOPE = 'pages_manage_posts,pages_read_engagement,instagram_basic,instagram_content_publish';

    /**
     * Redirige al usuario a Facebook para autorizar la app.
     */
    public function connect(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->company_id) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'Necesitas una empresa. Créala desde Temas de contenido.');
        }

        $config = config('services.facebook');
        if (empty($config['app_id']) || empty($config['app_secret'])) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'Facebook OAuth no configurado (FACEBOOK_APP_ID / FACEBOOK_APP_SECRET).');
        }

        $state = encrypt($user->company_id);
        $params = http_build_query([
            'client_id' => $config['app_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => self::FB_SCOPE,
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect($config['oauth_url'].'?'.$params);
    }

    /**
     * Facebook redirige aquí con ?code=...&state=...
     * Intercambiamos code por user token, obtenemos páginas (y Instagram) y guardamos en social_accounts.
     */
    public function callback(Request $request): RedirectResponse
    {
        $error = $request->query('error');
        if ($error) {
            Log::warning('Facebook OAuth error', ['error' => $error, 'description' => $request->query('error_description')]);
            return redirect()->route('social-accounts.index')
                ->with('error', 'Facebook no autorizó la app: '.($request->query('error_description') ?? $error));
        }

        $code = $request->query('code');
        $state = $request->query('state');
        if (! $code || ! $state) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'Faltan code o state en la respuesta de Facebook.');
        }

        try {
            $companyId = decrypt($state);
        } catch (\Throwable $e) {
            Log::warning('Facebook OAuth invalid state');
            return redirect()->route('social-accounts.index')
                ->with('error', 'Sesión inválida. Vuelve a conectar Facebook.');
        }

        $config = config('services.facebook');
        $tokenResponse = Http::get($config['token_url'], [
            'client_id' => $config['app_id'],
            'client_secret' => $config['app_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'code' => $code,
        ]);

        if (! $tokenResponse->successful()) {
            Log::warning('Facebook token exchange failed', ['body' => $tokenResponse->body()]);
            return redirect()->route('social-accounts.index')
                ->with('error', 'No se pudo obtener el token de Facebook.');
        }

        $userToken = $tokenResponse->json('access_token');
        if (! $userToken) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'Facebook no devolvió access_token.');
        }

        // Páginas que el usuario administra (con Instagram business si existe)
        $accountsUrl = $config['graph_url'].'/me/accounts';
        $accountsResponse = Http::get($accountsUrl, [
            'access_token' => $userToken,
            'fields' => 'id,name,access_token,instagram_business_account',
        ]);

        if (! $accountsResponse->successful()) {
            Log::warning('Facebook me/accounts failed', ['body' => $accountsResponse->body()]);
            return redirect()->route('social-accounts.index')
                ->with('error', 'No se pudieron obtener las páginas de Facebook.');
        }

        $pages = $accountsResponse->json('data', []);
        if (empty($pages)) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'No tienes páginas de Facebook. Crea una en Facebook y vuelve a conectar.');
        }

        $saved = 0;
        foreach ($pages as $page) {
            $pageId = $page['id'] ?? null;
            $pageName = $page['name'] ?? 'Página';
            $pageToken = $page['access_token'] ?? null;
            if (! $pageId || ! $pageToken) {
                continue;
            }

            SocialAccount::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'platform' => SocialAccount::PLATFORM_FACEBOOK,
                    'page_id' => $pageId,
                ],
                [
                    'account_name' => $pageName,
                    'access_token' => $pageToken,
                    'meta_user_token' => $userToken,
                ]
            );
            $saved++;

            $igAccount = $page['instagram_business_account'] ?? null;
            if (! empty($igAccount['id'])) {
                $igId = $igAccount['id'];
                $igName = $igAccount['username'] ?? $pageName.' (Instagram)';
                SocialAccount::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
                        'page_id' => $igId,
                    ],
                    [
                        'account_name' => $igName,
                        'access_token' => $pageToken,
                        'meta_user_token' => $userToken,
                    ]
                );
                $saved++;
            }
        }

        return redirect()->route('social-accounts.index')
            ->with('success', 'Facebook e Instagram conectados. Se guardaron '.$saved.' cuenta(s).');
    }
}
