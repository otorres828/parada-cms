import './bootstrap';
import '../../public/js/flatpickr.js';
import '../../public/js/flatpickr/es.js';

document.addEventListener('livewire:init', () => {
    // Variable de control para evitar múltiples alertas simultáneas
    let isAlertOpen = false;

    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status != 419) {
                return;
            }

            preventDefault();

            window.location.reload();

        });
    });
});
