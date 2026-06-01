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
    public $tab = 'productos'; // 'productos', 'adiciones'

    protected $queryString = [
        'tab' => ['except' => 'productos'],
        'search' => ['except' => ''],
        'filterCategoria' => ['except' => 'todas']
    ];

    // Excel
    public $showImportModal = false;
    public $archivoImportacion;
    public $importErrors = [];
    public $importSuccess = null;

    // Variantes
    public $showVariantesModal = false;
    public $selectedProductoId;
    public $selectedProductoNombre;
    public $nuevaVarianteNombre = '';
    public $nuevaVarianteObligatorio = true;
    public $nuevaVarianteOpciones = [];
    public $editingVarianteId = null;

    // Catálogo de Adiciones
    public $adicionId = null;
    public $adicionNombre = '';
    public $adicionPrecio = 0;
    public $adicionCategorias = [];
    public $adicionProductos = [];
    public $isEditingAdicion = false;

    protected $rules = [
        'nombre'                  => 'required|string|max:100',
        'descripcion'             => 'nullable|string',
        'precio'                  => 'required|numeric|min:0',
        'precio_oferta'           => 'nullable|numeric|min:0',
        'categoria_id'            => 'nullable|exists:categorias,id',
        'imagen'                  => 'nullable|image|max:2048', // max 2MB
        'activo'                  => 'boolean',
        'disponible'              => 'boolean',
        'permite_notas'           => 'boolean',
        'limite_minimo_adiciones' => 'required|integer|min:0',
        'limite_maximo_adiciones' => 'nullable|integer|min:0',
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
        $total = Producto::where('sucursal_id', $sucursal_id)->count();
        $categorias = Categoria::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();

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

    public function delete($id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $producto->delete();
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

    // --- ACCIONES DE EXCEL ---

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductosExport(Auth::user()->sucursal_id),
            'productos.xlsx'
        );
    }

    public function descargarPlantilla()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductosTemplateExport(),
            'plantilla_productos.xlsx'
        );
    }

    public function openImportModal()
    {
        $this->archivoImportacion = null;
        $this->importErrors = [];
        $this->importSuccess = null;
        $this->showImportModal = true;
    }

    public function import()
    {
        $this->validate([
            'archivoImportacion' => 'required|file|mimes:xlsx,xls'
        ], [
            'archivoImportacion.required' => 'Debes seleccionar un archivo.',
            'archivoImportacion.mimes' => 'El archivo debe ser un archivo de Excel (.xlsx o .xls).'
        ]);

        try {
            $importer = new \App\Imports\ProductosImport(Auth::user()->sucursal_id);
            \Maatwebsite\Excel\Facades\Excel::import($importer, $this->archivoImportacion->getRealPath());

            if (count($importer->errors) > 0) {
                $this->importErrors = $importer->errors;
                $this->importSuccess = null;
            } else {
                $this->importSuccess = "Se importaron correctamente {$importer->importedCount} productos.";
                $this->importErrors = [];
                $this->showImportModal = false;
                $this->archivoImportacion = null;
            }
        } catch (\Exception $e) {
            $this->importErrors = ['Ocurrió un error al procesar el archivo: ' . $e->getMessage()];
            $this->importSuccess = null;
        }
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

    // --- ACCIONES DE CATÁLOGO DE ADICIONES ---

    public function setTab($tab)
    {
        if (in_array($tab, ['productos', 'adiciones'])) {
            $this->tab = $tab;
            $this->resetPage();
        }
    }

    public function openAdicionesModal()
    {
        $this->resetAdicionForm();
        $this->tab = 'adiciones';
    }

    public function resetAdicionForm()
    {
        $this->adicionId = null;
        $this->adicionNombre = '';
        $this->adicionPrecio = 0.00;
        $this->adicionCategorias = [];
        $this->adicionProductos = [];
        $this->isEditingAdicion = false;
        $this->resetValidation();
    }

    public function saveAdicion()
    {
        $this->validate([
            'adicionNombre' => 'required|string|max:100',
            'adicionPrecio' => 'required|numeric|min:0',
            'adicionCategorias' => 'array',
            'adicionProductos' => 'array',
        ], [
            'adicionNombre.required' => 'El nombre es obligatorio.',
            'adicionPrecio.required' => 'El precio es obligatorio.',
            'adicionPrecio.numeric' => 'El precio debe ser un número.',
            'adicionPrecio.min' => 'El precio debe ser mayor o igual a 0.'
        ]);

        $sucursal_id = Auth::user()->sucursal_id;

        if ($this->isEditingAdicion) {
            $adicion = \App\Models\AdicionCatalogo::findOrFail($this->adicionId);
            if ($adicion->sucursal_id !== $sucursal_id) {
                abort(403);
            }
            $adicion->update([
                'nombre' => $this->adicionNombre,
                'precio' => $this->adicionPrecio
            ]);
        } else {
            $adicion = \App\Models\AdicionCatalogo::create([
                'sucursal_id' => $sucursal_id,
                'nombre' => $this->adicionNombre,
                'precio' => $this->adicionPrecio,
                'activo' => true,
                'disponible' => true
            ]);
        }

        $adicion->categorias()->sync($this->adicionCategorias);
        $adicion->productos()->sync($this->adicionProductos);

        $this->resetAdicionForm();
    }

    public function editAdicion($id)
    {
        $adicion = \App\Models\AdicionCatalogo::findOrFail($id);
        if ($adicion->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->adicionId = $adicion->id;
        $this->adicionNombre = $adicion->nombre;
        $this->adicionPrecio = $adicion->precio;
        $this->adicionCategorias = $adicion->categorias()->pluck('categorias.id')->toArray();
        $this->adicionProductos = $adicion->productos()->pluck('productos.id')->toArray();
        $this->isEditingAdicion = true;
    }

    public function deleteAdicion($id)
    {
        $adicion = \App\Models\AdicionCatalogo::findOrFail($id);
        if ($adicion->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $adicion->delete();
        if ($this->adicionId == $id) {
            $this->resetAdicionForm();
        }
    }

    public function toggleAdicionActivo($id)
    {
        $adicion = \App\Models\AdicionCatalogo::findOrFail($id);
        if ($adicion->sucursal_id === Auth::user()->sucursal_id) {
            $adicion->update(['activo' => !$adicion->activo]);
        }
    }

    public function toggleAdicionDisponible($id)
    {
        $adicion = \App\Models\AdicionCatalogo::findOrFail($id);
        if ($adicion->sucursal_id === Auth::user()->sucursal_id) {
            $adicion->update(['disponible' => !$adicion->disponible]);
        }
    }
}
