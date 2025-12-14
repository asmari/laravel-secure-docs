<?php

namespace Asmari\SecureDocs;

use Illuminate\Support\ServiceProvider;

class SecureDocsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load migrations automatically when the package is installed
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    public function register()
    {
        //
    }
}