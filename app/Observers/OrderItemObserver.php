<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        $order = $orderItem->order;
        
        // Sumo el subtotal de la línea al total general del pedido
        $order->total += $orderItem->subtotal;
        $order->save();
    }
}