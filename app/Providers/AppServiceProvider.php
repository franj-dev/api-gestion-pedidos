<?php

namespace App\Providers;

use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use App\Events\OrderCreated;                     // <-- ¡AÑADE ESTE IMPORT!
use App\Listeners\DecrementProductStock;         // <-- ¡AÑADE ESTE IMPORT!
use Illuminate\Support\Facades\Event;            // <-- ¡AÑADE ESTE IMPORT!
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
        // Registro del observer para el total
        OrderItem::observe(OrderItemObserver::class);

        // Registro del evento y listener para el stock
        Event::listen(
            OrderCreated::class,
            DecrementProductStock::class
        );
    }
}