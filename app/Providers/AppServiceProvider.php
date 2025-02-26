<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infra\Db\UserDb;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserPersistenceInterface::class, UserDb::class);
        $this->app->bind(UuidGenerator::class, function ($app) {
            return new UuidGenerator();
        });
    }
}
