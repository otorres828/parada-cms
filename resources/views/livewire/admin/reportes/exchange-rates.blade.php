@section('title', 'Histórico de tasas de cambio')

<div x-data="exchangeRates" class="py-3">
    <x-list.heading>
        <x-slot:title>
            Histórico de tasas de cambio
        </x-slot:title>

        @if ($canUpdate)
            <x-slot:button>
                <button type="button" class="btn btn-primary" wire:click="updateRates"
                    wire:loading.attr="disabled" wire:target="updateRates">
                    <span wire:loading.remove wire:target="updateRates">
                        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Actualizar ahora
                    </span>
                    <span wire:loading wire:target="updateRates">
                        Consultando BCV…
                    </span>
                </button>
            </x-slot:button>
        @endif
    </x-list.heading>

    <p class="text-body-secondary">
        Tasas oficiales de referencia. Cada valor indica cuántos bolívares equivalen a una unidad de la moneda.
    </p>

    <x-list.table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha de consulta</th>
                <th>1 USD</th>
                <th>1 EUR</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->timestamp?->format('d/m/Y H:i:s') ?? '—' }}</td>
                    <td>Bs. {{ number_format($row->valor_usd, 2, ',', '.') }}</td>
                    <td>Bs. {{ number_format($row->valor_eur, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        Todavía no hay tasas registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-list.table>

    {{ $rows->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('exchangeRates', () => ({
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
        }));
    </script>
@endscript
