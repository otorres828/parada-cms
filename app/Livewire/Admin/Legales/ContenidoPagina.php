<?php

namespace App\Livewire\Admin\Legales;

use App\Services\Admin\Access;
use App\Support\ContenidoSitio;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.crm')]
class ContenidoPagina extends Component
{
    #[Locked]
    public string $pagina = '';
    public string $contenido = '';

    public function mount(string $pagina): void
    {
        abort_unless(isset(ContenidoSitio::PAGINAS[$pagina]), 404);
        $this->pagina = $pagina;
        $this->contenido = ContenidoSitio::leer($pagina)['contenido'];
    }

    public function render()
    {
        return view('livewire.admin.legales.contenido-pagina', [
            'titulo' => ContenidoSitio::PAGINAS[$this->pagina],
        ]);
    }

    public function save(): void
    {
        Access::authorize($this->pagina, 'edit');

        $this->validate([
            'contenido' => 'required|string|max:100000',
        ]);

        ContenidoSitio::guardar($this->pagina, $this->contenido);

        $this->dispatch('successEventList', message: 'Contenido guardado correctamente.');
    }
}
