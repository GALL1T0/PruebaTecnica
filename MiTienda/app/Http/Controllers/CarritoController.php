<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    // Obtener el carrito del cliente autenticado
    public function index()
    {
        $cliente = auth('cliente')->user();
        $carrito = $cliente->carrito;

        if (!$carrito) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        return response()->json($carrito->load('items.producto'));
    }

    // Agregar un producto al carrito
    public function store(Request $request)
    {
        $cliente = auth('cliente')->user();

        // Validar los datos de la solicitud
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        // Obtener o crear el carrito del cliente
        $carrito = $cliente->carrito ?? $cliente->carrito()->create();

        // Verificar si el producto ya está en el carrito
        $item = $carrito->items()->where('producto_id', $request->producto_id)->first();

        if ($item) {
            // Si el producto ya está en el carrito, actualizar la cantidad
            $item->update(['cantidad' => $item->cantidad + $request->cantidad]);
        } else {
            // Si el producto no está en el carrito, agregarlo
            $carrito->items()->create([
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidad,
            ]);
        }

        return response()->json($carrito->load('items.producto'), 201);
    }

    // Eliminar un producto del carrito
    public function destroy($itemId)
    {
        $cliente = auth('cliente')->user();
        $carrito = $cliente->carrito;

        if (!$carrito) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        // Buscar y eliminar el ítem del carrito
        $item = $carrito->items()->find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Ítem no encontrado en el carrito'], 404);
        }

        $item->delete();

        return response()->json(null, 204);
    }
}
