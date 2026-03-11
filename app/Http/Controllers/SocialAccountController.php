<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialAccountRequest;
use App\Http\Requests\UpdateSocialAccountRequest;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cada empresa gestiona sus cuentas de redes sociales (credenciales, token, page_id).
 */
class SocialAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user->company_id) {
            return Inertia::render('SocialAccounts/Index', [
                'accounts' => [],
                'hasCompany' => false,
                'platforms' => SocialAccount::$platforms,
                'facebookConfigured' => ! empty(config('services.facebook.app_id')),
                'flash' => ['success' => session('success'), 'error' => session('error')],
            ]);
        }

        $accounts = SocialAccount::where('company_id', $user->company_id)
            ->orderBy('platform')
            ->orderBy('account_name')
            ->get()
            ->map(fn (SocialAccount $a) => [
                'id' => $a->id,
                'platform' => $a->platform,
                'account_name' => $a->account_name,
                'page_id' => $a->page_id,
                'has_token' => ! empty($a->access_token),
            ]);

        return Inertia::render('SocialAccounts/Index', [
            'accounts' => $accounts,
            'hasCompany' => true,
            'platforms' => SocialAccount::$platforms,
            'facebookConfigured' => ! empty(config('services.facebook.app_id')),
            'flash' => ['success' => session('success'), 'error' => session('error')],
        ]);
    }

    /**
     * @return Response|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $user = $request->user();
        if (! $user->company_id) {
            return redirect()->route('social-accounts.index');
        }

        return Inertia::render('SocialAccounts/Create', [
            'platforms' => SocialAccount::$platforms,
        ]);
    }

    public function store(StoreSocialAccountRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        if (! $user->company_id) {
            return back()->withErrors(['platform' => 'Debes pertenecer a una empresa.']);
        }

        SocialAccount::create([
            'company_id' => $user->company_id,
            'platform' => $request->validated('platform'),
            'account_name' => $request->validated('account_name'),
            'access_token' => $request->validated('access_token'),
            'page_id' => $request->validated('page_id'),
        ]);

        return redirect()->route('social-accounts.index')->with('success', 'Cuenta agregada.');
    }

    /**
     * @return Response|\Illuminate\Http\RedirectResponse
     */
    public function edit(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();
        if ($socialAccount->company_id !== $user->company_id) {
            abort(403);
        }

        return Inertia::render('SocialAccounts/Edit', [
            'account' => [
                'id' => $socialAccount->id,
                'platform' => $socialAccount->platform,
                'account_name' => $socialAccount->account_name,
                'page_id' => $socialAccount->page_id,
            ],
            'platforms' => SocialAccount::$platforms,
        ]);
    }

    public function update(UpdateSocialAccountRequest $request, SocialAccount $socialAccount): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        if ($socialAccount->company_id !== $user->company_id) {
            abort(403);
        }

        $data = [
            'platform' => $request->validated('platform'),
            'account_name' => $request->validated('account_name'),
            'page_id' => $request->validated('page_id'),
        ];
        if ($request->filled('access_token')) {
            $data['access_token'] = $request->validated('access_token');
        }

        $socialAccount->update($data);

        return redirect()->route('social-accounts.index')->with('success', 'Cuenta actualizada.');
    }

    public function destroy(Request $request, SocialAccount $socialAccount): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        if ($socialAccount->company_id !== $user->company_id) {
            abort(403);
        }

        $socialAccount->delete();
        return redirect()->route('social-accounts.index')->with('success', 'Cuenta eliminada.');
    }
}
