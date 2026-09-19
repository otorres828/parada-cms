@section('title', 'Clientes')

<div x-data="listUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Clientes
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">

        <div class="col-md-3">

            <label class="form-label" for="listUser-status">
                Estado
            </label>

            <select id="listUser-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>

            </select>

        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Nombre
                    <x-list.sortable-button column="name" :$sortColumn :$sortDirection />
                </th>

                <th>Apellido
                    <x-list.sortable-button column="lastname" :$sortColumn :$sortDirection />
                </th>

                <th>Correo
                    <x-list.sortable-button column="email" :$sortColumn :$sortDirection />
                </th>

                <th>Teléfono
                    <x-list.sortable-button column="telefono" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="status" :$sortColumn :$sortDirection />
                </th>

                <th class="text-end">Acciones</th>

            </tr>
        </thead>

        <tbody>

            @forelse ($users as $user)

                <tr wire:key="listUser-{{ $user->id }}">

                    <td>
                        {{ $user->id }}
                    </td>

                    <td>
                        {{ $user->name ?? '—' }}
                    </td>

                    <td>
                        {{ $user->lastname ?? '—' }}
                    </td>

                    <td>
                        {{ $user->email ?? '—' }}
                    </td>

                    <td>
                        {{ $user->telefono ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$user->status" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)

                                <x-list.view-button :route="route('admin.clientes.detail', ['user_id' => $user->id])" :target="false" />

                            @endif

                            @if ($canEdit)

                                <x-list.edit-button :route="route('admin.clientes.edit', ['user_id' => $user->id])" :target="false" />

                                <x-list.status-button
                                    wire:click="changeStatus({{ $user->id }})"
                                    :status="$user->status"
                                    wire:loading.attr="disabled" />

                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </x-list.table>

    {{ $users->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listUser', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [

                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),

                ];
                const savedMessage = @js(session()->pull('admin_success'));

                if (savedMessage)
                    this.$nextTick(() =>
                        Livewire.dispatch('successEventList', {
                            message: savedMessage
                        }));
            },

        }));
    </script>
@endscript
