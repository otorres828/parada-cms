{{--
    FORM — EDITOR DE TEXTO ENRIQUECIDO
    --------------------------------------------------------------------------
    Inicializa TinyMCE bajo demanda y sincroniza su contenido con una propiedad
    Livewire mediante entangle. Libera la instancia al abandonar la vista.
    --------------------------------------------------------------------------
--}}

@props([
    'model',
    'label' => 'Contenido',
    'height' => null,
])

<div x-data="richTextEditor({
    content: $wire.entangle(@js($model)),
    height: @js($height),
})">

    <div wire:ignore>
        <textarea {{ $attributes->merge([
            'id' => 'rich-text-editor',
            'class' => 'form-control',
        ]) }} x-ref="editor"></textarea>
    </div>

</div>

@script

    <script>
        Alpine.data('richTextEditor', config => ({

            content: config.content,
            height: config.height,
            editor: null,
            resizeHandler: null,

            async init() {
                try {
                    await this.loadTinyMce();
                    await this.$nextTick();
                    await this.initializeEditor();
                } catch (error) {
                    console.error('No fue posible inicializar TinyMCE:', error);
                    this.$store.toast.info('No fue posible cargar el editor de texto.');
                }
            },

            async loadTinyMce() {
                if (window.tinymce) return;

                if (! window.tinyMceLoader) {
                    window.tinyMceLoader = new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js';
                        script.referrerPolicy = 'origin';
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                }

                await window.tinyMceLoader;
            },

            async initializeEditor() {
                const element = this.$refs.editor;

                await tinymce.init({
                    target: element,
                    license_key: 'gpl',
                    height: this.editorHeight(),
                    menubar: 'edit view insert format tools table',
                    plugins: 'lists link table code',
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | removeformat code',
                    browser_spellcheck: true,
                    promotion: false,
                    branding: false,
                    setup: editor => {
                        this.editor = editor;

                        editor.on('init', () => {
                            editor.setContent(this.content || '');

                            this.resizeHandler = () => {
                                editor.theme.resizeTo(null, this.editorHeight());
                            };

                            window.addEventListener('resize', this.resizeHandler);
                        });

                        editor.on('change input undo redo', () => {
                            this.content = editor.getContent();
                        });
                    },
                });
            },

            editorHeight() {
                if (this.height) return Number(this.height);

                return Math.max(window.innerHeight - 280, 500);
            },

            destroy() {
                if (this.resizeHandler) {
                    window.removeEventListener('resize', this.resizeHandler);
                }

                this.editor?.remove();
                this.editor = null;
            },

        }));
    </script>

@endscript
