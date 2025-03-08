<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Tienda;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    // Obtener el historial de ventas por tienda para el vendedor autenticado
    public function index(Request $request, $tiendaId)
    {
        $vendedor = auth('vendedor')->user();

        // Verificar que la tienda pertenezca al vendedor autenticado
        $tienda = $vendedor->tiendas()->find($tiendaId);

        if (!$tienda) {
            return response()->json(['message' => 'Tienda no encontrada o no autorizada'], 404);
        }

        // Obtener todas las compras asociadas a la tienda con sus ítems y productos
        $ventas = Compra::whereHas('items.producto', function ($query) use ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        })->with('items.producto')->get();

        return response()->json($ventas);
    }
}
