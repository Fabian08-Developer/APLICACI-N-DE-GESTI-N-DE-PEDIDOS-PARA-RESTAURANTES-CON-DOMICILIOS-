<?php

namespace App\Livewire\Admin\Productos;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ManageProductos extends Component
{
    use WithPagination, WithFileUploads;

    // Campos de Producto
    public $nombre;
    public $descripcion;
    public $receta;
    public $precio;
    public $precio_oferta;
    public $categoria_id;
    public $imagen;
    public $imagenPath;
    public $activo = true;
    public $disponible = true;
    public $permite_notas = true;
    public $limite_minimo_adiciones = 0;
    public $limite_maximo_adiciones = null;

    public $producto_id;
    public $isEditing = false;
    public $showModal = false;
    public $search = '';
    public $filterCategoria = 'todas';

    // Para el modal de confirmación de eliminación (puramente Livewire)
    public $showModalEliminarLivewire = false;
    public $producto_eliminar_id;
    public $producto_eliminar_nombre = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCategoria' => ['except' => 'todas']
    ];

    // Variantes
    public $showVariantesModal = false;
    public $selectedProductoId;
    public $selectedProductoNombre;
    public $nuevaVarianteNombre = '';
    public $nuevaVarianteObligatorio = true;
    public $nuevaVarianteOpciones = [];
    public $editingVarianteId = null;

    // Adiciones por Producto
    public $showAdicionesModal = false;
    public $selectedProductoIdAdicion;
    public $selectedProductoNombreAdicion;
    public $adicionesDelProducto = [];
    public $nuevaAdicionNombre = '';
    public $nuevaAdicionPrecio = 0;
    public $editingAdicionId = null;

    protected $rules = [
        'nombre'                  => 'required|string|max:100',
        'descripcion'             => 'nullable|string',
        'receta'                  => 'nullable|string',
        'precio'                  => 'required|numeric|min:0',
        'precio_oferta'           => 'nullable|numeric|min:0|lte:precio',
        'categoria_id'            => 'nullable|exists:categorias,id',
        'imagen'                  => 'nullable|image|max:2048', // max 2MB
        'activo'                  => 'boolean',
        'disponible'              => 'boolean',
        'permite_notas'           => 'boolean',
        'limite_minimo_adiciones' => 'required|integer|min:0',
        'limite_maximo_adiciones' => 'nullable|integer|min:0',
    ];

    protected $messages = [
        'precio_oferta.lte' => 'El precio de oferta no puede ser mayor al precio normal.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterCategoria()
    {
        $this->resetPage();
    }

    public function render()
    {
        $sucursal_id = Auth::user()->sucursal_id;
        
        $query = Producto::with('categoria')->where('sucursal_id', $sucursal_id)->latest();

        if ($this->search) {
            $likeOperator = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite' ? 'like' : 'ilike';
            $query->where('nombre', $likeOperator, '%' . $this->search . '%');
        }

        if ($this->filterCategoria !== 'todas') {
            if ($this->filterCategoria === 'sin_categoria') {
                $query->whereNull('categoria_id');
            } else {
                $query->where('categoria_id', $this->filterCategoria);
            }
        }

        $productos = $query->paginate(10);
        // Usar $productos->total() en la vista en lugar de hacer otra query separada
        $total = $productos->total();
        $categorias = Categoria::where('sucursal_id', $sucursal_id)->select('id', 'nombre')->orderBy('nombre')->get();

        return view('livewire.admin.productos.manage-productos', compact('productos', 'total', 'categorias'))
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

        if ($this->limite_maximo_adiciones !== null && $this->limite_maximo_adiciones !== '') {
            if ($this->limite_maximo_adiciones < $this->limite_minimo_adiciones) {
                $this->addError('limite_maximo_adiciones', 'El límite máximo no puede ser menor que el límite mínimo.');
                return;
            }
        }

        $sucursal_id = Auth::user()->sucursal_id;

        $path = $this->imagenPath;

        if ($this->imagen) {
            if ($this->isEditing && $this->imagenPath) {
                Storage::disk('public')->delete($this->imagenPath);
            }
            $path = $this->imagen->store('productos', 'public');
        }

        if ($this->isEditing) {
            $producto = Producto::findOrFail($this->producto_id);
            if ($producto->sucursal_id !== $sucursal_id) {
                abort(403);
            }
            
            $exists = Producto::where('sucursal_id', $sucursal_id)
                ->where('nombre', $this->nombre)
                ->where('id', '!=', $this->producto_id)
                ->first();
                
            if ($exists) {
                $this->addError('nombre', 'Ya existe un producto con ese nombre en esta sucursal.');
                return;
            }

            $producto->update([
                'categoria_id'            => $this->categoria_id ?: null,
                'nombre'                  => $this->nombre,
                'descripcion'             => $this->descripcion,
                'receta'                  => $this->receta,
                'precio'                  => $this->precio,
                'precio_oferta'           => $this->precio_oferta ?: null,
                'imagen'                  => $path,
                'activo'                  => $this->activo,
                'disponible'              => $this->disponible,
                'permite_notas'           => $this->permite_notas,
                'limite_minimo_adiciones' => $this->limite_minimo_adiciones,
                'limite_maximo_adiciones' => ($this->limite_maximo_adiciones !== null && $this->limite_maximo_adiciones !== '') ? $this->limite_maximo_adiciones : null,
            ]);
        } else {
            $exists = Producto::where('sucursal_id', $sucursal_id)
                ->where('nombre', $this->nombre)
                ->first();
                
            if ($exists) {
                $this->addError('nombre', 'Ya existe un producto con ese nombre en esta sucursal.');
                return;
            }

            Producto::create([
                'sucursal_id'             => $sucursal_id,
                'categoria_id'            => $this->categoria_id ?: null,
                'nombre'                  => $this->nombre,
                'descripcion'             => $this->descripcion,
                'receta'                  => $this->receta,
                'precio'                  => $this->precio,
                'precio_oferta'           => $this->precio_oferta ?: null,
                'imagen'                  => $path,
                'activo'                  => $this->activo,
                'disponible'              => $this->disponible,
                'permite_notas'           => $this->permite_notas,
                'limite_minimo_adiciones' => $this->limite_minimo_adiciones,
                'limite_maximo_adiciones' => ($this->limite_maximo_adiciones !== null && $this->limite_maximo_adiciones !== '') ? $this->limite_maximo_adiciones : null,
            ]);
        }

        $this->showModal = false;
        $this->resetInputFields();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->producto_id = $id;
        $this->categoria_id = $producto->categoria_id;
        $this->nombre = $producto->nombre;
        $this->descripcion = $producto->descripcion;
        $this->receta = $producto->receta;
        $this->precio = $producto->precio;
        $this->precio_oferta = $producto->precio_oferta;
        $this->imagenPath = $producto->imagen;
        $this->activo = $producto->activo;
        $this->disponible = $producto->disponible;
        $this->permite_notas = $producto->permite_notas;
        $this->limite_minimo_adiciones = $producto->limite_minimo_adiciones;
        $this->limite_maximo_adiciones = $producto->limite_maximo_adiciones;
        
        $this->isEditing = true;
        $this->showModal = true;
        
        $this->dispatch('data-loaded');
    }

    public function openEliminarModal($id)
    {
        $producto = Producto::findOrFail($id);
        $this->producto_eliminar_id = $producto->id;
        $this->producto_eliminar_nombre = $producto->nombre;
        $this->showModalEliminarLivewire = true;
    }

    public function eliminarProducto($id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $producto->delete();
        $this->showModalEliminarLivewire = false;
        $this->dispatch('close-modal');
    }

    public function toggleDisponible($id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->sucursal_id === Auth::user()->sucursal_id) {
            $producto->update(['disponible' => !$producto->disponible]);
        }
    }

    public function toggleActivo($id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->sucursal_id === Auth::user()->sucursal_id) {
            $producto->update(['activo' => !$producto->activo]);
        }
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->receta = '';
        $this->precio = '';
        $this->precio_oferta = '';
        $this->categoria_id = '';
        $this->imagen = null;
        $this->imagenPath = null;
        $this->activo = true;
        $this->disponible = true;
        $this->permite_notas = true;
        $this->limite_minimo_adiciones = 0;
        $this->limite_maximo_adiciones = null;
        $this->producto_id = null;
    }

    // --- ACCIONES DE VARIANTES ---

    public function openVariantesModal($productoId)
    {
        $producto = Producto::findOrFail($productoId);
        if ($producto->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->selectedProductoId = $productoId;
        $this->selectedProductoNombre = $producto->nombre;
        $this->resetVarianteForm();
        $this->showVariantesModal = true;
    }

    public function resetVarianteForm()
    {
        $this->nuevaVarianteNombre = '';
        $this->nuevaVarianteObligatorio = true;
        $this->nuevaVarianteOpciones = [
            ['nombre' => '', 'precio' => 0.00, 'tipo_impacto' => 'incremental', 'disponible' => true]
        ];
        $this->editingVarianteId = null;
        $this->resetValidation();
    }

    public function addOpcionToNuevaVariante()
    {
        $this->nuevaVarianteOpciones[] = [
            'nombre' => '',
            'precio' => 0.00,
            'tipo_impacto' => 'incremental',
            'disponible' => true
        ];
    }

    public function removeOpcionFromNuevaVariante($index)
    {
        unset($this->nuevaVarianteOpciones[$index]);
        $this->nuevaVarianteOpciones = array_values($this->nuevaVarianteOpciones);
    }

    public function saveVariante()
    {
        $this->validate([
            'nuevaVarianteNombre' => 'required|string|max:100',
            'nuevaVarianteObligatorio' => 'boolean',
            'nuevaVarianteOpciones' => 'required|array|min:1',
            'nuevaVarianteOpciones.*.nombre' => 'required|string|max:100',
            'nuevaVarianteOpciones.*.precio' => 'required|numeric|min:0',
            'nuevaVarianteOpciones.*.tipo_impacto' => 'required|in:fijo,incremental',
            'nuevaVarianteOpciones.*.disponible' => 'boolean'
        ], [
            'nuevaVarianteNombre.required' => 'El nombre del grupo es obligatorio.',
            'nuevaVarianteOpciones.min' => 'Debes agregar al menos una opción.',
            'nuevaVarianteOpciones.*.nombre.required' => 'El nombre de la opción es obligatorio.',
            'nuevaVarianteOpciones.*.precio.required' => 'El precio es obligatorio.',
            'nuevaVarianteOpciones.*.precio.numeric' => 'El precio debe ser un número.',
            'nuevaVarianteOpciones.*.precio.min' => 'El precio debe ser mayor o igual a 0.'
        ]);

        if ($this->editingVarianteId) {
            $variante = \App\Models\VarianteProducto::findOrFail($this->editingVarianteId);
            $variante->update([
                'nombre' => $this->nuevaVarianteNombre,
                'obligatorio' => $this->nuevaVarianteObligatorio,
                'opciones' => $this->nuevaVarianteOpciones
            ]);
        } else {
            \App\Models\VarianteProducto::create([
                'producto_id' => $this->selectedProductoId,
                'nombre' => $this->nuevaVarianteNombre,
                'obligatorio' => $this->nuevaVarianteObligatorio,
                'opciones' => $this->nuevaVarianteOpciones
            ]);
        }

        $this->resetVarianteForm();
    }

    public function editVariante($id)
    {
        $variante = \App\Models\VarianteProducto::findOrFail($id);
        $this->editingVarianteId = $variante->id;
        $this->nuevaVarianteNombre = $variante->nombre;
        $this->nuevaVarianteObligatorio = $variante->obligatorio;
        $this->nuevaVarianteOpciones = $variante->opciones;
    }

    public function deleteVariante($id)
    {
        $variante = \App\Models\VarianteProducto::findOrFail($id);
        $variante->delete();
        if ($this->editingVarianteId == $id) {
            $this->resetVarianteForm();
        }
    }

    // --- ACCIONES DE ADICIONES POR PRODUCTO ---

    public function openAdicionesModalForProducto($productoId)
    {
        $producto = Producto::findOrFail($productoId);
        if ($producto->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->selectedProductoIdAdicion = $productoId;
        $this->selectedProductoNombreAdicion = $producto->nombre;
        $this->loadAdiciones();
        $this->resetNuevaAdicionForm();
        $this->showAdicionesModal = true;
    }

    public function loadAdiciones()
    {
        $this->adicionesDelProducto = \App\Models\AdicionProducto::where('producto_id', $this->selectedProductoIdAdicion)
                                        ->orderBy('nombre')
                                        ->get()
                                        ->toArray();
    }

    public function resetNuevaAdicionForm()
    {
        $this->nuevaAdicionNombre = '';
        $this->nuevaAdicionPrecio = 0.00;
        $this->editingAdicionId = null;
        $this->resetValidation();
    }

    public function saveNuevaAdicion()
    {
        $this->validate([
            'nuevaAdicionNombre' => 'required|string|max:100',
            'nuevaAdicionPrecio' => 'required|numeric|min:0',
        ], [
            'nuevaAdicionNombre.required' => 'El nombre es obligatorio.',
            'nuevaAdicionPrecio.required' => 'El precio es obligatorio.',
            'nuevaAdicionPrecio.numeric' => 'El precio debe ser numérico.',
            'nuevaAdicionPrecio.min' => 'El precio no puede ser negativo.',
        ]);

        if ($this->editingAdicionId) {
            $adicion = \App\Models\AdicionProducto::findOrFail($this->editingAdicionId);
            $adicion->update([
                'nombre' => $this->nuevaAdicionNombre,
                'precio' => $this->nuevaAdicionPrecio,
            ]);
        } else {
            \App\Models\AdicionProducto::create([
                'producto_id' => $this->selectedProductoIdAdicion,
                'nombre' => $this->nuevaAdicionNombre,
                'precio' => $this->nuevaAdicionPrecio,
                'activo' => true,
            ]);
        }

        $this->loadAdiciones();
        $this->resetNuevaAdicionForm();
    }

    public function editAdicionSimple($id)
    {
        $adicion = \App\Models\AdicionProducto::findOrFail($id);
        $this->editingAdicionId = $adicion->id;
        $this->nuevaAdicionNombre = $adicion->nombre;
        $this->nuevaAdicionPrecio = $adicion->precio;
    }

    public function deleteAdicionSimple($id)
    {
        $adicion = \App\Models\AdicionProducto::findOrFail($id);
        $adicion->delete();
        $this->loadAdiciones();
        if ($this->editingAdicionId == $id) {
            $this->resetNuevaAdicionForm();
        }
    }

    public function toggleAdicionSimpleActivo($id)
    {
        $adicion = \App\Models\AdicionProducto::findOrFail($id);
        $adicion->update(['activo' => !$adicion->activo]);
        $this->loadAdiciones();
    }
}
