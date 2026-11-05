<?php

namespace App\Providers;

use App\View\Composers\AdminSidebarComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.ak');

        View::composer('layouts.admin', AdminSidebarComposer::class);
    }
}
