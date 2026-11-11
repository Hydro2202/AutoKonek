<?php

namespace App\Providers;

use App\Support\SqliteMathFunctions;
use App\View\Composers\AdminSidebarComposer;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Pdo\Sqlite;

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

        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            if ($event->connection->getDriverName() !== 'sqlite') {
                return;
            }

            $pdo = $event->connection->getPdo();

            if ($pdo instanceof Sqlite) {
                SqliteMathFunctions::register($pdo);
            }
        });
    }
}
