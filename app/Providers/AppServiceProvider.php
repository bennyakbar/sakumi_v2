<?php

namespace App\Providers;

use App\Events\TransactionCreated;
use App\Listeners\SendPaymentNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(TransactionCreated::class, SendPaymentNotification::class);
    }
}
