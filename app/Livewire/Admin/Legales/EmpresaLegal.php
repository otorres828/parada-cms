<?php

namespace App\Livewire\Admin\Legales;

use App\Models\DocumentoLegal;
use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class EmpresaLegal extends Component
{
    use Listing, Permissions, WithFileUploads, WithPagination;

    #[Locked]
    public int $empresa_id;

    public string $titulo = '';

    public string $tipo = 'contrato';

    public string $observaciones = '';

    public string $tipo_filtro = '';

    public $archivo;

    public bool $canFile = false;

    public array $tipos = DocumentoLegal::TIPOS;

    public Empresa $empresa;

    protected array $queryString = [
        'search' => ['except' => ''],
        'tipo_filtro' => ['except' => ''],
        'per_page' => ['except' => 10],
    ];

    public function mount(int $empresa_id): void
    {
        Access::authorize('legales', 'detail');

        $empresa = Empresa::find($empresa_id);
        if (!$empresa) {
            abort(404);
        }
        $this->empresa = $empresa;
        $this->checkPermissions('legales');
        $this->canFile = Access::allows('legales', 'file');

        $this->empresa_id = $empresa_id;
        $this->sortColumn = 'created_at';
        $this->sortDirection = 'desc';
    }

    public function render()
    {
        $query = DocumentoLegal::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'tipo' => $this->tipo_filtro,
        ]);

        $documentos = $this->applySort($query)->paginate($this->per_page);

        return view('livewire.admin.legales.empresa-legal', [
            'documentos' => $documentos,
        ]);
    }

    public function save(): void
    {
        Access::authorize('legales', 'add');

        $data = $this->validate([
            'titulo' => 'required|string|max:255', 
            'tipo' => ['required', Rule::in(array_keys(DocumentoLegal::TIPOS))], 
            'observaciones' => 'nullable|string|max:4000', 
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|mimetypes:application/pdf,image/jpeg,image/png,image/webp|max:10240'
        ]);

        $path = $this->archivo->store('legales/' . $this->empresa_id, 'local');

        if (!$path) {

            throw ValidationException::withMessages(['archivo' => 'No se pudo guardar el archivo. Intenta nuevamente.']);

        }

        try {

            DB::transaction(function () use ($data, $path) {

                Access::authorize('legales', 'add');

                $documento = DocumentoLegal::create([
                    'empresa_id' => $this->empresa_id, 
                    'admin_id' => auth('admin')->id(), 
                    'titulo' => $data['titulo'], 
                    'tipo' => $data['tipo'], 
                    'observaciones' => $data['observaciones'] ?: null, 
                    'archivo' => $path, 
                    'nombre_original' => mb_substr(basename($this->archivo->getClientOriginalName()), 0, 255), 
                    'mime' => $this->archivo->getMimeType(), 
                    'tamano' => $this->archivo->getSize()
                ]);
                
                Audit::record('legal.cargado', $documento, ['empresa_id' => $this->empresa_id, 'tipo' => $documento->tipo, 'titulo' => $documento->titulo]);

            });

        } catch (\Throwable $e) {

            Storage::disk('local')->delete($path);
            throw $e;

        }

        $this->reset('titulo', 'tipo', 'observaciones', 'archivo', 'search', 'tipo_filtro');
        $this->resetValidation();
        $this->resetPage();
        $this->dispatch('successEventList', message: 'Documento guardado correctamente.');
        $this->dispatch('legalSaved');

    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo_filtro', 'per_page'])) {
            $this->resetPage();
        }
    }
}
