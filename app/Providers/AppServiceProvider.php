<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    public function boot(UrlGenerator $url): void
    {
        if (config('app.env') === 'production') {
            $url->forceScheme('https');
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}