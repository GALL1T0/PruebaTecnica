<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    // Obtener todas las tiendas del vendedor autenticado
    public function index()
    {
        $vendedor = auth('vendedor')->user();
        return response()->json($vendedor->tiendas);
    }

    public function store(Request $request)
    {
        // Obtener el vendedor autenticado
        $vendedor = auth('vendedor')->user();

        // Validar los datos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        // Crear la tienda asociada al vendedor
        $tienda = $vendedor->tiendas()->create($request->all());

        return response()->json($tienda, 201);
    }

    // Obtener una tienda específica
    public function show(Tienda $tienda)
    {
        return response()->json($tienda);
    }

    // Actualizar una tienda
    public function update(Request $request, Tienda $tienda)
    {
        $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
        ]);

        $tienda->update($request->all());
        return response()->json($tienda);
    }

    // Eliminar una tienda
    public function destroy(Tienda $tienda)
    {
        $tienda->delete();
        return response()->json(null, 204);
    }
}
