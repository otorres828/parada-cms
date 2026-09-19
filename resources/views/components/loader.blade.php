{{--
    Loader Component
    A global loading overlay controlled by an Alpine.js store.
    Styles are managed in: resources/sass/components/_loader.scss
--}}

<div
    x-data
    x-show="$store.loader?.shouldShow"
    x-transition:leave="transition-opacity"
    id="loader-wrapper"
>
    {{-- Spinner element (styled via CSS animations) --}}
    <div class="loader"></div>
</div>

{{--
    Alpine.js Global Store: 'loader'
    Manages the application-wide loading state.
    Provides methods 'show()' and 'hide()' for global access.

    NOTE: The 'hide' method uses a 600ms timeout to align with
    the CSS transition duration defined in the loader styles.
--}}
<script>
     document.addEventListener('livewire:init', () => {

        Alpine.store('loader', {
            shouldShow: false,

            show() {
                this.shouldShow = true;
            },

            hide() {
                setTimeout(() => {
                    this.shouldShow = false;
                }, 600);
            }
        });
    });
</script>
