<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\TiendaController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register/cliente', [AuthController::class, 'registerCliente']);
Route::post('register/vendedor', [AuthController::class, 'registerVendedor']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:vendedor')->group(function () {
    // Rutas para tiendas
    Route::apiResource('tiendas', TiendaController::class);

    // Rutas para productos
    Route::apiResource('tiendas.productos', ProductoController::class)->except(['show']);
});
