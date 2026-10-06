document.addEventListener('alpine:init', () => {
    Alpine.data('crmFecha', ({ value, withTime = false }) => {
        let picker;
        const internalFormat = withTime ? 'Y-m-d\\TH:i' : 'Y-m-d';
        const normalize = value => value ? (withTime ? value.slice(0, 16).replace(' ', 'T') : value) : '';

        return {
            value,
            init() {
                const input = this.$el;
                picker = flatpickr(input, {
                    locale: 'es',
                    dateFormat: withTime ? 'd/m/Y H:i' : 'd/m/Y',
                    enableTime: withTime,
                    time_24hr: true,
                    defaultDate: this.value ? flatpickr.parseDate(normalize(this.value), internalFormat) : null,
                    minDate: input.min ? flatpickr.parseDate(input.min, 'Y-m-d') : null,
                    maxDate: input.max ? flatpickr.parseDate(input.max, 'Y-m-d') : null,
                    disableMobile: true,
                    allowInput: true,
                    parseDate(text, format) {
                        if (typeof text !== 'string') return flatpickr.parseDate(text, format);
                        const date = flatpickr.parseDate(text, format);
                        return date && flatpickr.formatDate(date, format) === text ? date : undefined;
                    },
                    onChange: dates => {
                        this.value = dates.length ? flatpickr.formatDate(dates[0], internalFormat) : '';
                    },
                });
                input._crmRefreshFecha = () => {
                    picker.set('minDate', input.min ? flatpickr.parseDate(input.min, 'Y-m-d') : null);
                    picker.set('maxDate', input.max ? flatpickr.parseDate(input.max, 'Y-m-d') : null);
                    picker.setDate(normalize(this.value), false, internalFormat);
                };
                this.$watch('value', () => picker.setDate(normalize(this.value), false, internalFormat));
            },
            destroy() {
                delete this.$el._crmRefreshFecha;
                picker?.destroy();
            },
        };
    });
});

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el }) => el._crmRefreshFecha?.());
});
