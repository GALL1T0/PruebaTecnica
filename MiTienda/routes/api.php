<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\VentaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rutas públicas (sin autenticación)
Route::post('register/cliente', [AuthController::class, 'registerCliente']);
Route::post('register/vendedor', [AuthController::class, 'registerVendedor']);
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Rutas para vendedores
    Route::middleware('auth:vendedor')->group(function () {
        Route::apiResource('tiendas', TiendaController::class);
        Route::apiResource('tiendas.productos', ProductoController::class)->except(['show']);
    });

    // Rutas para clientes
    Route::middleware('auth:cliente')->group(function () {
        Route::get('carrito', [CarritoController::class, 'index']);
        Route::post('carrito', [CarritoController::class, 'store']);
        Route::delete('carrito/{itemId}', [CarritoController::class, 'destroy']);
    });


    Route::middleware('auth:cliente')->group(function () {
        Route::get('compras/historial', [CompraController::class, 'index']);
        Route::post('compras/finalizar', [CompraController::class, 'store']);
    });

    Route::middleware('auth:vendedor')->group(function () {
        Route::get('tiendas/{tiendaId}/ventas', [VentaController::class, 'index']);
    });
});
