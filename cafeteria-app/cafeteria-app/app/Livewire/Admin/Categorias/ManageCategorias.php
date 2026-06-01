<?php

namespace App\Livewire\Admin\Categorias;

use Livewire\Component;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ManageCategorias extends Component
{
    use WithPagination;

    public $nombre;
    public $descripcion;
    public $categoria_id;
    public $isEditing = false;
    public $showModal = false;
    public $search = '';
    public $activo = true;

    protected $rules = [
        'nombre' => ['required', 'string', 'max:100', 'regex:/^(?!.*\b\d+\b).*$/i'],
        'descripcion' => 'nullable|string',
        'activo' => 'boolean',
    ];

    protected $messages = [
        'nombre.regex' => 'El nombre de la categoría no puede contener números correlativos o sueltos (ej. "Bebidas 1").',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $sucursal_id = Auth::user()->sucursal_id;
        $query = Categoria::where('sucursal_id', $sucursal_id)->latest();

        if ($this->search) {
            $likeOperator = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite' ? 'like' : 'ilike';
            $query->where('nombre', $likeOperator, '%' . $this->search . '%');
        }

        $categorias = $query->paginate(10);
        $total = Categoria::where('sucursal_id', $sucursal_id)->count();

        return view('livewire.admin.categorias.manage-categorias', compact('categorias', 'total'))
               ->layout('layouts.admin');
    }

    public function openCreateModal()
    {
        $this->resetInputFields();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $sucursal_id = Auth::user()->sucursal_id;

        if ($this->isEditing) {
            $categoria = Categoria::findOrFail($this->categoria_id);
            if ($categoria->sucursal_id !== $sucursal_id) {
                abort(403);
            }
            
            $exists = Categoria::where('sucursal_id', $sucursal_id)
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($this->nombre, 'UTF-8')])
                ->where('id', '!=', $this->categoria_id)
                ->first();
                
            if ($exists) {
                $this->addError('nombre', 'Ya existe una categoría con ese nombre en esta sucursal.');
                return;
            }

            $categoria->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
            ]);
        } else {
            $exists = Categoria::where('sucursal_id', $sucursal_id)
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($this->nombre, 'UTF-8')])
                ->first();
                
            if ($exists) {
                $this->addError('nombre', 'Ya existe una categoría con ese nombre en esta sucursal.');
                return;
            }

            Categoria::create([
                'sucursal_id' => $sucursal_id,
                'nombre'      => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo'      => $this->activo,
            ]);
        }

        $this->showModal = false;
        $this->resetInputFields();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        if ($categoria->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->categoria_id = $id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        $this->activo = $categoria->activo;
        $this->isEditing = true;
        $this->showModal = true;
        
        $this->dispatch('data-loaded');
    }

    public function toggleActivo($id)
    {
        $categoria = Categoria::findOrFail($id);
        if ($categoria->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $categoria->update([
            'activo' => !$categoria->activo
        ]);
        
        $this->dispatch('categoria-actualizada');
    }

    public function delete($id)
    {
        $categoria = Categoria::findOrFail($id);
        if ($categoria->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        if ($categoria->productos()->count() > 0) {
            $this->addError('general', 'No se puede eliminar la categoría porque tiene productos asociados.');
            return;
        }

        $categoria->delete();
        $this->dispatch('close-modal');
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->activo = true;
        $this->categoria_id = null;
    }
}
