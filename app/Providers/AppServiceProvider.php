<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Request::macro('getCompanyId', function (): ?int {
            /** @var Request $this */
            $id = $this->attributes->get('api_company_id');
            if ($id !== null) {
                return (int) $id;
            }
            return $this->user()?->company_id;
        });
    }
}
