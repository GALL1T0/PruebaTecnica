<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // Obtener todos los productos de una tienda
    public function index(Tienda $tienda)
    {
        return response()->json($tienda->productos);
    }

    // Crear un nuevo producto en una tienda
    public function store(Request $request, Tienda $tienda)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $producto = $tienda->productos()->create($request->all());
        return response()->json($producto, 201);
    }

    // Obtener un producto específico
    public function show(Producto $producto)
    {
        return response()->json($producto);
    }

    // Actualizar un producto
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
        ]);

        $producto->update($request->all());
        return response()->json($producto);
    }

    // Eliminar un producto
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return response()->json(null, 204);
    }
}
