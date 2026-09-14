<?php

namespace App\Providers;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\VisitRequest;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        // Badge counts shown next to sidebar links
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $counts = [];

            if ($user?->isAdmin()) {
                $counts['visits'] = VisitRequest::where('status', 'new')->count();
                $counts['payments'] = Payment::where('status', 'pending')->count();
            } elseif ($user?->isTenant()) {
                $counts['contract'] = Contract::where('status', 'sent')
                    ->whereHas('lease', fn ($q) => $q->where('tenant_id', $user->id))
                    ->count();
            }

            $view->with('navCounts', $counts);
        });
    }
}
