<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Mostrar la página con la lista y el formulario
     * Ruta: GET /admin/categorias
     */
    public function index()
    {
        // Traemos todas las categorías ordenadas por las más recientes
        $categorias = Categoria::latest()->get();

        // Si viene una categoría a editar, la pasamos a la vista
        $editar = null;

        return view('admin.categorias.index', compact('categorias', 'editar'));
    }

    /**
     * Guardar una nueva categoría
     * Ruta: POST /admin/categorias
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre',
        ]);

        Categoria::create($request->only('nombre', 'descripcion'));

        return redirect()->route('admin.categorias.index')
                         ->with('exito', 'Categoría creada correctamente');
    }

    /**
     * Cargar formulario con datos de la categoría a editar
     * Ruta: GET /admin/categorias/{id}/editar
     */
    public function editar($id)
    {
        $categorias = Categoria::latest()->get();
        $editar     = Categoria::findOrFail($id);

        return view('admin.categorias.index', compact('categorias', 'editar'));
    }

    /**
     * Actualizar una categoría existente
     * Ruta: POST /admin/categorias/{id}/actualizar
     */
    public function actualizar(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|max:100|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre',
        ]);

        $categoria->update($request->only('nombre', 'descripcion'));

        return redirect()->route('admin.categorias.index')
                         ->with('exito', 'Categoría actualizada correctamente');
    }

    /**
     * Eliminar una categoría
     * Ruta: POST /admin/categorias/{id}/eliminar
     */
    public function eliminar($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        try {
            $categoria->delete();
            return redirect()->route('admin.categorias.index')
                             ->with('exito', 'Categoría eliminada correctamente');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23503 || $e->errorInfo[1] == 1451) {
                return redirect()->route('admin.categorias.index')
                                 ->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
            }
            return redirect()->route('admin.categorias.index')
                             ->with('error', 'Ocurrió un error al intentar eliminar la categoría.');
        }
    }
}