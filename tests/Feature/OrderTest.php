<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase; // Esto vacía la base de datos temporal en cada test para no mezclar datos

    public function test_un_usuario_autenticado_puede_crear_un_pedido_con_exito()
    {
        // Prueba
        $usuario = User::factory()->create();
        $producto = Product::factory()->create(['stock' => 10, 'price' => 15.0]);

        $payload = [
            'items' => [
                [
                    'product_id' => $producto->id,
                    'quantity' => 2,
                ]
            ]
        ];

        // Ejecuto la petición simulando estar autenticados
        $respuesta = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/orders', $payload);

        // Verificacion de seguridad
        $respuesta->assertStatus(201); // El estado HTTP debe ser 201 (created)

        // Comprobamos que el stock en la bd realmente bajó (de 10 a 8)
        $this->assertDatabaseHas('products', [
            'id' => $producto->id,
            'stock' => 6 // para forzar error lo cambié antes a 8 por ejemplo
        ]);
    }
}