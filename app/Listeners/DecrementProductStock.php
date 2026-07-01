<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Exception;

class DecrementProductStock
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Recorro cada línea del pedido para actualizar el stock de los productos
        foreach ($order->items as $item) {
            $product = $item->product;

            // Validar si hay stock suficiente
            if ($product->stock < $item->quantity) {
                // Al lanzar una excepción, se hace rollback y no se guarda el pedido
                throw new Exception("El producto '{$product->name}' no tiene stock suficiente (Disponible: {$product->stock}).");
            }

            // Si hay stock, resto las unidades correspondientes
            $product->stock -= $item->quantity;
            $product->save();
        }
    }
}