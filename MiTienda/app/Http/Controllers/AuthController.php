<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Vendedor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Registro para clientes
    public function registerCliente(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|string|email|unique:clientes',
            'password' => 'required|string|min:8',
        ]);

        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $cliente->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    // Registro para vendedores
    public function registerVendedor(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|string|email|unique:vendedores',
            'password' => 'required|string|min:8',
        ]);

        $vendedor = Vendedor::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $vendedor->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    // Login para clientes y vendedores
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'tipo' => 'required|string|in:cliente,vendedor',
        ]);

        if ($request->tipo === 'cliente') {
            if (!Auth::guard('cliente')->attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }
            $user = Cliente::where('email', $request->email)->firstOrFail();
        } else {
            if (!Auth::guard('vendedor')->attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }
            $user = Vendedor::where('email', $request->email)->firstOrFail();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    // Logout para clientes y vendedores
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
