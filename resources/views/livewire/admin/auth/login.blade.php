{{--
    INICIAR SESIÓN
    --------------------------------------------------------------------------
    Presenta el formulario de acceso al panel administrativo. Valida las credenciales introducidas
    y muestra las notificaciones del inicio de sesión.

    Componentes reutilizables utilizados:
    - <x-layout.spinner />: Indicador general de procesamiento.
    - <x-auth.card />: Tarjeta contenedora del acceso al panel.
    - <x-auth.card-header />: Cabecera e identidad visual del acceso.
    - <x-auth.card-body />: Cuerpo de la tarjeta de autenticación.
    - <x-auth.card-title />: Título o indicaciones del formulario de acceso.
    - <x-auth.username-input />: Campo del nombre de usuario para iniciar sesión.
    - <x-auth.password-input />: Campo de contraseña del acceso.
    - <x-form.switch />: Interruptor para activar o desactivar una opción.
    - <x-auth.card-footer />: Pie de la tarjeta de autenticación.
    --------------------------------------------------------------------------
--}}

@section('title', 'Iniciar sesión')

<div class="login-box" x-data="login">

    {{-- Global UI Loader - Triggered via preGuardar() middleware --}}

    <x-layout.spinner />

    <x-auth.card>

        {{-- Branding/Logo section of the AdminLTE card --}}

        <x-auth.card-header />

        <x-auth.card-body>

            {{-- Instructional text with support for HTML line breaks --}}

            <x-auth.card-title>

                Ingresar credenciales <br> para iniciar sesión

            </x-auth.card-title>

            {{--
                Form Submission Pipeline:
                1. Intercepts standard submit with .prevent
                2. Runs Alpine 'preGuardar' to show UI feedback
                3. Handles Enter key for accessibility
            --}}
            <form x-on:submit.prevent="preGuardar" x-on:keydown.enter.stop.prevent="preGuardar" id="loginForm">

                {{-- Username field synchronized with Livewire $username property --}}

                <x-auth.username-input wire:model="username" :hasError="$errors->has('username')" :errorMessage="$errors->first('username')" name="username" />

                {{--
                    Password field with dynamic masking logic.
                    Type toggles between 'text' and 'password' via Alpine state.
                --}}

                <x-auth.password-input wire:model="password" :hasError="$errors->has('password')" x-bind:type="(isPasswordVisible) ? 'text' : 'password'"
                    :errorMessage="$errors->first('password')" name="password" />

                <div class="mb-4">

                    {{-- Reactive toggle linked to isPasswordVisible boolean --}}

                    <x-form.switch x-model="isPasswordVisible">

                        Mostrar contraseña

                    </x-form.switch>

                </div>

                <x-auth.card-footer>

                    {{-- Primary action button --}}
                    <button type="submit" class="btn btn-secondary">Iniciar sesión</button>

                </x-auth.card-footer>

            </form>

        </x-auth.card-body>

    </x-auth.card>

</div>

@script
    <script>
        Alpine.data('login', () => ({
            openGroup: null,
            isPasswordVisible: false,
            validator: null,
            init() {
                Livewire.on('success', data => {
                    data = data[0]
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos enviados correctamente',
                        text: event.detail.message || '',
                        timer: 2000,
                        showConfirmButton: false,
                    }).finally(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect
                        }
                    })
                })

                Livewire.on('error', data => {
                    data = data[0]
                    Swal.fire({
                        icon: 'warning',
                        title: data.message,
                        text: '',
                        timer: 2000,
                        showConfirmButton: false,
                    })
                })

                this.validator = new JustValidate('#loginForm', {
                    errorLabelCssClass: ['invalid-feedback'],
                    errorFieldCssClass: ['is-invalid'],
                    successFieldCssClass: ['is-valid'],
                });

                this.validator
                    .addField('[name="username"]', [{
                            rule: 'required',
                            errorMessage: 'Campo requerido'
                        }

                    ])
                    .addField('[name="password"]', [{
                        rule: 'required',
                        errorMessage: 'Campo requerido'
                    }])

            },
            preGuardar() {

                this.validator.revalidate().then((valid) => {

                    if (valid) {

                        spinner();
                        // grecaptcha.ready(async () => {
                        spinner();
                        // const token = await grecaptcha.execute("{{ env('GOOGLE_RECAPTCHA_ID') }}", {action: 'forgot_password_emil'});}
                        const token = 26;
                        @this.call('submit', token)
                        // })k;

                    }
                });
            }
        }));
    </script>
@endscript
