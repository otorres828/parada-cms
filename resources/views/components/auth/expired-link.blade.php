         <div class="text-center">
                        <div class="mb-2">
                            <i class="bi bi-clock-history text-warning display-1"></i>
                        </div>

                        <h2 class="h4 font-weight-bold text-dark mb-3">
                            Este enlace ha expirado
                        </h2>

                        <p class="text-muted">
                            No te preocupes, puedes solicitar uno nuevo en el inicio de sesión seleccionando el botó:
                        </p>

                        <p>
                            <strong>“Restablecer contraseña”</strong>
                        </p>

                        <p class="text-secondary small mb-4">
                            ¡Te ayudaremos a volver a nuestro sitio al instante!
                        </p>

                    </div>

                <x-auth.card-footer>

                    <a class="btn btn-secondary" href="{{ route('login')}}" wire:navigate>Ir a iniciar sesión</a>

                </x-auth.card-footer>