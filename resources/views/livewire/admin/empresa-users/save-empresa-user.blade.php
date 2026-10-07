{{--
    USUARIOS DE EMPRESA — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar usuarios de empresa con sus credenciales, estado y permisos.
    El panel de Admin también permite asignar el acceso de administrador de empresa.

    Componentes utilizados:
    - <x-list.heading />: Encabezado con título y regreso al listado.
    - <x-form.cancel-button />: Regreso al listado en el encabezado.
    - <x-layout.error />: Resumen de errores de validación.
    - <x-empresa-users.form />: Formulario compartido y validación del navegador.
    - <x-layout.loader.fullpage />: Indicador de carga.
--}}

@section('title', $usuario_empresa_id ? 'Editar usuario' : 'Nuevo usuario')

<div class="py-3">

    <x-list.heading>

        <x-slot:title>
            {{ $usuario_empresa_id ? 'Editar usuario' : 'Nuevo usuario' }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.empresas.users.list', ['empresa_id' => $empresa_id])" >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <x-empresa-users.form
        :groups="$groups"
        :usuario-empresa-id="$usuario_empresa_id"
        :show-admin-switch="true"
    />

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>
