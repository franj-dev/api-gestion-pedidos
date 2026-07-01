<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrderOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        // capturo el ID del pedido que viene en la URL /api/orders/{id}
        $orderId = $request->route('id');
        $order = Order::find($orderId);

        // Si el pedido no existe lanzo un 404
        if (!$order) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        // Si el pedido no pertenece al usuario autenticado lanzo un 403
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes autorización para acceder a este pedido.'], 403);
        }

        return $next($request);
    }
}