<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\WorkOrder;
use App\Observers\WorkOrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        WorkOrder::observe(WorkOrderObserver::class);
    }
}
