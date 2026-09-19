{{--
    Toast Notification Store
    Styles defined in: resources/sass/components/form/_toast.scss
    Integration: Uses 'Toastify-js' library to display global notifications.
--}}

{{--
    Internal wrapper for Toastify-js initialization.
    @param text     - The message to display.
    @param cssClass - The class to apply for custom styling (e.g., toast-success).
--}}
<script>
    document.addEventListener('livewire:init', () => {

        Alpine.store('toast', {

            // Ahora el segundo parámetro recibe el color HEX y el tercero el icono
            triggerToastify(text, bgColor, iconName){
                Toastify({
                    text: text,
                    duration: 8000,
                    // Mantenemos una clase base limpia para los bordes, paddings, etc.
                    className: `toast-${iconName}`,
                    gravity: "top",
                    position: "right",
                    close: true,
                    // AQUÍ APLICAS EL COLOR DIRECTAMENTE:
                    style: {
                        background: bgColor
                    },
                }).showToast();
            },

            success(text) {
                this.triggerToastify(text, '#00B571', 'success');
            },

            warning(text) {
                this.triggerToastify(text, '#d4e633', 'warning');
            },

            error(text) {
                this.triggerToastify(text, '#ec352b', 'error');
            },

            info(text) {
                this.triggerToastify(text, '#146aa0', 'info');
            },

        });
    });
</script>


{{--
    Global Toast Notification System
    - Listens for: Session flashes ('success', 'error','warning').
--}}
<div x-data="toastHandler" x-init="init()"></div>

<script>
    document.addEventListener('livewire:init', () => {

    Alpine.data('toastHandler', () => ({

        init() {

            const urlParams = new URLSearchParams(window.location.search);

                if (urlParams.has('alert') && urlParams.has('message')) {

                    const alert = urlParams.get('alert');

                    const message = urlParams.get('message');

                    switch (alert) {
                        case 'success':
                             this.$store.toast.success(message);
                        break;
                        case 'error':
                             this.$store.toast.error(message);
                        break;
                        case 'warning':
                             this.$store.toast.warning(message);
                        break;
                        case 'info':
                             this.$store.toast.info(message);
                        break;
                    }

                    this.clearQueryString('alert');

                    this.clearQueryString('message');
                }

        },

        clearQueryString(key) {
            const url = new URL(window.location.href);
            url.searchParams.delete(key);
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }
    }));
});
</script>
