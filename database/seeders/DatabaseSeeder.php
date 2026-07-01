<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos un usuario fijo para las pruebas. Así sabremos exactamente su email y contraseña.
        User::factory()->create([
            'name' => 'Fran Test',
            'email' => 'test@test.es',
            'password' => Hash::make('password123'), // Contraseña encriptada de forma segura
        ]);

        // 2. Creamos otros 4 usuarios totalmente aleatorios
        User::factory(4)->create();

        // 3. Creamos 20 productos usando la fábrica que configuramos en el Paso 1
        Product::factory(20)->create();
    }
}