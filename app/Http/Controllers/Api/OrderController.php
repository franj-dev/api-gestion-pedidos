<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreOrderRequest;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Recupero los pedidos que pertenecen al usuario autenticado
        $orders = $request->user()->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        // Deuelvo la colección transformada con el mismo resource
        return OrderResource::collection($orders);
    }
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            // Crear el pedido base en estado pending
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => 0,
                'status' => 'pending',
            ]);

            // Crear las lineas copiando el precio del producto en bd
            foreach ($request->validated()['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $product->price, // copia el precio actual del producto
                    'subtotal' => $product->price * $itemData['quantity'],
                ]);
            }

            // Evento que resta stock
            event(new OrderCreated($order));

            return $order;
        });

        return new OrderResource($order->refresh());
    }

    /**
     * GET /api/orders/{id}
     * Ver un pedido específico con sus ítems
    */
    public function show($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * PUT /api/orders/{id}/cancel
     * Cancelar un pedido (si está 'pending')
     */
    public function cancel($id)
    {
        $order = Order::findOrFail($id);

        // No se puede cancelar si está en estado completed o cancelled
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => "No se puede cancelar un pedido con estado: {$order->status}."
            ], 422);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'message' => 'Pedido cancelado correctamente.',
            'data' => new OrderResource($order)
        ]);
    }

}