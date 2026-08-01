<?php

namespace App\Providers;

use App\Models\ServerStatus;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            $serverStatus = null;
            try {
                $serverHost = config('services.minecraft.host', 'localhost');
                $serverPort = (int) config('services.minecraft.port', 25565);
                $serverStatus = ServerStatus::getStatus($serverHost, $serverPort);
            } catch (\Exception $e) {
                $serverStatus = null;
            }
            $view->with('serverStatus', $serverStatus);
        });
    }
}
