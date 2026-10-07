document.addEventListener('alpine:init', () => {
    Alpine.data('crmFecha', ({ value, withTime = false }) => {
        let picker;
        let input;
        let visibleInput;
        let inputListener;
        let manualValue = null;
        const internalFormat = withTime ? 'Y-m-d\\TH:i' : 'Y-m-d';
        const normalize = value => value ? (withTime ? value.slice(0, 16).replace(' ', 'T') : value) : '';

        return {
            value,
            init() {
                this.$nextTick(() => this.inicializar());
            },
            inicializar() {
                input = this.$refs?.input || this.$el;
                picker = flatpickr(input, {
                    locale: 'es',
                    dateFormat: withTime ? internalFormat : 'd/m/Y',
                    altInput: withTime,
                    altFormat: 'd/m/Y H:i',
                    enableTime: withTime,
                    time_24hr: true,
                    defaultDate: this.value ? flatpickr.parseDate(normalize(this.value), internalFormat) : null,
                    minDate: input.min ? flatpickr.parseDate(input.min, 'Y-m-d') : null,
                    maxDate: input.max ? flatpickr.parseDate(input.max, 'Y-m-d') : null,
                    disableMobile: true,
                    allowInput: true,
                    onReady: (dates, text, instance) => {
                        if (!instance.altInput) return;
                        for (const attribute of ['form', 'aria-label', 'aria-describedby']) {
                            if (input.hasAttribute(attribute)) instance.altInput.setAttribute(attribute, input.getAttribute(attribute));
                        }
                    },
                    parseDate(text, format) {
                        if (typeof text !== 'string') return flatpickr.parseDate(text, format);
                        const date = flatpickr.parseDate(text, format);
                        return date && flatpickr.formatDate(date, format) === text ? date : undefined;
                    },
                    onChange: dates => {
                        manualValue = null;
                        this.value = dates.length ? flatpickr.formatDate(dates[0], internalFormat) : '';
                    },
                });
                visibleInput = picker.altInput || input;
                visibleInput.setAttribute('inputmode', withTime ? 'text' : 'numeric');
                visibleInput.setAttribute('maxlength', withTime ? '16' : '10');
                inputListener = event => {
                    const raw = visibleInput.value;
                    const cursor = visibleInput.selectionStart ?? raw.length;
                    const digitPosition = raw.slice(0, cursor).replace(/\D/g, '').length;
                    const digits = raw.replace(/\D/g, '').slice(0, withTime ? 12 : 8);
                    const deleting = event.inputType?.startsWith('delete');
                    let text = digits.slice(0, 2);
                    if (digits.length > 2 || (digits.length === 2 && !deleting)) text += '/' + digits.slice(2, 4);
                    if (digits.length > 4 || (digits.length === 4 && !deleting)) text += '/' + digits.slice(4, 8);
                    if (withTime && digits.length > 8) text += ' ' + digits.slice(8, 10);
                    if (withTime && (digits.length > 10 || (digits.length === 10 && !deleting))) text += ':' + digits.slice(10, 12);
                    visibleInput.value = text;
                    let position = 0, count = 0;
                    while (position < text.length && count < digitPosition) {
                        if (/\d/.test(text[position])) count++;
                        position++;
                    }
                    if (!deleting) while (position < text.length && /[/: ]/.test(text[position])) position++;
                    visibleInput.setSelectionRange(position, position);
                    const format = withTime ? 'd/m/Y H:i' : 'd/m/Y';
                    const date = text.length === (withTime ? 16 : 10) ? optionsDate(text, format) : null;
                    const min = input.min ? flatpickr.parseDate(input.min, 'Y-m-d') : null;
                    const max = input.max ? flatpickr.parseDate(input.max, 'Y-m-d') : null;
                    const day = date ? new Date(date.getFullYear(), date.getMonth(), date.getDate()) : null;
                    const valid = date && (!min || day >= min) && (!max || day <= max);
                    manualValue = valid ? flatpickr.formatDate(date, internalFormat) : '';
                    // Un valor parcial o inválido no conserva silenciosamente la fecha anterior.
                    this.value = manualValue;
                    if (valid || !text) picker.setDate(valid ? date : '', false);
                };
                const optionsDate = (text, format) => {
                    const date = flatpickr.parseDate(text, format);
                    return date && flatpickr.formatDate(date, format) === text ? date : null;
                };
                visibleInput.addEventListener('input', inputListener);
                input._crmRefreshFecha = () => {
                    picker.set('minDate', input.min ? flatpickr.parseDate(input.min, 'Y-m-d') : null);
                    picker.set('maxDate', input.max ? flatpickr.parseDate(input.max, 'Y-m-d') : null);
                    if (manualValue === null || normalize(this.value) !== manualValue) {
                        picker.setDate(normalize(this.value), false, internalFormat);
                    }
                };
                this.$watch('value', () => {
                    if (manualValue !== null && normalize(this.value) === manualValue) return;
                    manualValue = null;
                    picker.setDate(normalize(this.value), false, internalFormat);
                });
            },
            destroy() {
                if (input) delete input._crmRefreshFecha;
                visibleInput?.removeEventListener('input', inputListener);
                picker?.destroy();
            },
        };
    });
});

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el }) => el._crmRefreshFecha?.());
});
