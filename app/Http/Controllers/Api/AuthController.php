<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 1. POST /api/register
    public function register(Request $request)
    {
        // Validación de los datos que nos envía el cliente
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Creación del usuario encriptando la contraseña
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Código 201: Creado con éxito
        return response()->json(['message' => 'Usuario registrado con éxito.'], 201);
    }

    // 2. POST /api/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busco al usuario por su email
        $user = User::where('email', $data['email'])->first();

        // Si el usuario no existe o la contraseña no coincide, devuelvo error
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Código 401: No autorizado
            return response()->json(['message' => 'Las credenciales son incorrectas.'], 401);
        }

        // Si las credenciales son buenas, genero su token de acceso
        $token = $user->createToken('auth_token')->plainTextToken;

        // Respondo con el token que el cliente usará en las próximas peticiones
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}