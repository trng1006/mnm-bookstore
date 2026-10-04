<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
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
        // Global variables for Header
        View::composer('client.partials.header', function ($view) {
            // Fetch directly to avoid __PHP_Incomplete_Class serialization issues in some environments
            $menuCategories = \App\Models\Category::orderBy('name')->get();

            $view->with('menuCategories', $menuCategories);

            if (auth()->check()) {
                $user = auth()->user();
                // Avoid loading all notifications into memory, just get the count and top 4
                $unreadCount = $user->unreadNotifications()->count();
                $unreadNotifications = $user->unreadNotifications()->take(4)->get();
                
                $view->with('unreadCount', $unreadCount);
                $view->with('unreadNotifications', $unreadNotifications);
            }
        });
    }
}
