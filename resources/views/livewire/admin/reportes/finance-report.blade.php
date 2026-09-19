@section('title', 'Movimientos financieros')

<div x-data="financeReport" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Movimientos financieros
        </x-slot:title>

        <x-slot:button>
            <button type="button" class="btn btn-outline-primary" @click="preSave" :disabled="saving" wire:loading.attr="disabled">Exportar
                CSV</button>
        </x-slot:button>

    </x-list.heading>

    @if ($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif
    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="row">

            <div class="col-md-4">

                <x-form.text-input type="date" name="date_from" wire:model.live="date_from">
                    Desde
                </x-form.text-input>

            </div>

            <div class="col-md-4">

                <x-form.text-input type="date" name="date_to" wire:model.live="date_to">
                    Hasta
                </x-form.text-input>

            </div>

        </div>

    </form>
    <p class="text-body-secondary">Movimientos registrados en USD: ventas netas, retiros y reembolsos.</p>

    <x-list.table>

        <thead>

            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Movimientos</th>
                <th>Importe neto USD</th>
            </tr>
        </thead>

        <tbody>
            @forelse($rows as $row)

                <tr>
                    <td>{{ $row->fecha ?? '—' }}</td>
                    <td>{{ $row->tipo ?? '—' }}</td>
                    <td>{{ $row->cantidad ?? '—' }}</td>
                    <td>{{ number_format($row->total ?? 0, 2) }}</td>
            </tr>@empty

                <tr>
                    <td colspan="4" class="text-center py-5">No hay datos para el período.</td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $rows->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('financeReport', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    });
                    this.validator.addField(this.$refs.form.querySelector('[name="date_from"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="date_to"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('export');
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
