<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // Obtener el historial de compras del cliente autenticado
    public function index(Request $request)
    {
        $cliente = auth('cliente')->user();

        $query = $cliente->compras()->with('items.producto');

        // Filtrar por fecha
        if ($request->has('fecha_inicio')) {
            $query->where('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->where('created_at', '<=', $request->fecha_fin);
        }

        // Filtrar por total mínimo
        if ($request->has('total_min')) {
            $query->where('total', '>=', $request->total_min);
        }

        // Filtrar por total máximo
        if ($request->has('total_max')) {
            $query->where('total', '<=', $request->total_max);
        }

        // Paginar los resultados
        $compras = $query->paginate(10);

        return response()->json($compras);
    }

    // Finalizar la compra
    public function store(Request $request)
    {
        $cliente = auth('cliente')->user();

        // Obtener el carrito del cliente
        $carrito = $cliente->carrito;

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío'], 400);
        }

        // Validar el stock de los productos en el carrito
        foreach ($carrito->items as $item) {
            $producto = Producto::find($item->producto_id);

            if ($producto->stock < $item->cantidad) {
                return response()->json([
                    'message' => 'No hay suficiente stock para el producto: ' . $producto->nombre,
                    'producto' => $producto,
                ], 400);
            }
        }

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            // Crear la compra
            $compra = Compra::create([
                'cliente_id' => $cliente->id,
                'total' => 0, // Se calculará más adelante
            ]);

            $total = 0;

            // Crear los ítems de la compra y descontar el stock
            foreach ($carrito->items as $item) {
                $producto = Producto::find($item->producto_id);

                // Crear el ítem de la compra
                CompraItem::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item->cantidad,
                    'precio' => $producto->precio,
                ]);

                // Descontar el stock del producto
                $producto->stock -= $item->cantidad;
                $producto->save();

                // Calcular el total de la compra
                $total += $producto->precio * $item->cantidad;
            }

            // Actualizar el total de la compra
            $compra->total = $total;
            $compra->save();

            // Vaciar el carrito
            $carrito->items()->delete();

            // Confirmar la transacción
            DB::commit();

            return response()->json($compra->load('items.producto'), 201);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            return response()->json(['message' => 'Error al finalizar la compra: ' . $e->getMessage()], 500);
        }
    }
}
