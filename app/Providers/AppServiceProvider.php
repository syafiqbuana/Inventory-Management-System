<?php

namespace App\Providers;

use App\Models\PurchaseItem;
use App\Observers\PurchaseItemObserver;
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
        PurchaseItem::observe(PurchaseItemObserver::class);
    }
}
