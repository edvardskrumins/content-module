<?php

namespace TetOtt\ContentModule;

use Illuminate\Support\ServiceProvider;

/**
 * Content Module Service Provider
 */
class ContentModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }
}

