const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const runtime = { module: { exports: {} }, exports: {}, Date, console };
vm.runInNewContext(fs.readFileSync(path.join(root, 'public/js/flatpickr.js'), 'utf8'), runtime);
const real = runtime.module.exports;
const listeners = {};
let factory, hook, options, destroyed = false;
const updates = [];
const flatpickr = (input, config) => {
    options = config;
    return {
        set(key, value) { updates.push([key, value]); },
        setDate(value, trigger, format) { updates.push(['date', value, trigger, format]); },
        destroy() { destroyed = true; },
    };
};
flatpickr.parseDate = real.parseDate;
flatpickr.formatDate = real.formatDate;
vm.runInNewContext(fs.readFileSync(path.join(root, 'public/js/crm-fechas.js'), 'utf8'), {
    document: { addEventListener(name, callback) { listeners[name] = callback; } },
    Alpine: { data(name, callback) { assert.equal(name, 'crmFecha'); factory = callback; } },
    Livewire: { hook(name, callback) { assert.equal(name, 'morph.updated'); hook = callback; } },
    flatpickr,
});
listeners['alpine:init']();
listeners['livewire:init']();
let watch;
const input = { min: '2026-01-01', max: '2100-12-31', setAttribute() {}, addEventListener(name, callback) { this.inputHandler = callback; }, removeEventListener() {}, setSelectionRange() {} };
const field = factory({ value: '2026-10-06' });
field.$el = input;
field.$nextTick = callback => callback();
field.$watch = (name, callback) => { assert.equal(name, 'value'); watch = callback; };
field.init();
assert.equal(options.dateFormat, 'd/m/Y');
assert.equal(options.locale, 'es');
assert.equal(options.disableMobile, true);
assert.equal(real.formatDate(options.defaultDate, 'd/m/Y'), '06/10/2026');
assert.equal(real.formatDate(options.minDate, 'Y-m-d'), input.min);
assert.equal(real.formatDate(options.maxDate, 'Y-m-d'), input.max);
assert.equal(real.formatDate(options.parseDate('12/11/2026', 'd/m/Y'), 'Y-m-d'), '2026-11-12');
assert.equal(options.parseDate('31/02/2026', 'd/m/Y'), undefined);
options.onChange([real.parseDate('12/11/2026', 'd/m/Y')]);
assert.equal(field.value, '2026-11-12');
field.value = '2026-12-24';
watch();
assert.deepEqual(updates.at(-1), ['date', '2026-12-24', false, 'Y-m-d']);
input.min = '2026-12-01';
hook({ el: input });
assert.equal(real.formatDate(updates.at(-3)[1], 'Y-m-d'), input.min);
options.onChange([]);
assert.equal(field.value, '');
watch();
assert.deepEqual(updates.at(-1), ['date', '', false, 'Y-m-d']);
function typeDate(value, inputType = 'insertText') {
    input.value = value;
    input.selectionStart = value.length;
    input.inputHandler({ inputType });
}
typeDate('07');
assert.equal(input.value, '07/');
typeDate('0710');
assert.equal(input.value, '07/10/');
typeDate('ab07122026');
assert.equal(input.value, '07/12/2026');
assert.equal(field.value, '2026-12-07');
const previous = updates.length;
watch();
assert.equal(updates.length, previous);
typeDate('31/02/2026');
assert.equal(field.value, '');
typeDate('07122025');
assert.equal(field.value, '');
typeDate('07/', 'deleteContentBackward');
assert.equal(input.value, '07');
typeDate('');
assert.equal(field.value, '');
field.destroy();
assert.equal(destroyed, true);
assert.equal(input._crmRefreshFecha, undefined);
const timestamp = factory({ value: '2026-10-06T15:30', withTime: true });
timestamp.$el = { ...input, min: '', max: '' };
timestamp.$nextTick = callback => callback();
timestamp.$watch = () => {};
timestamp.init();
assert.equal(options.dateFormat, 'Y-m-d\\TH:i');
assert.equal(options.altInput, true);
assert.equal(options.altFormat, 'd/m/Y H:i');
assert.equal(real.formatDate(options.defaultDate, 'd/m/Y H:i'), '06/10/2026 15:30');
options.onChange([real.parseDate('07/10/2026 18:45', 'd/m/Y H:i')]);
assert.equal(timestamp.value, '2026-10-07T18:45');
timestamp.$el.value = '071020261845';
timestamp.$el.selectionStart = 12;
timestamp.$el.inputHandler({ inputType: 'insertFromPaste' });
assert.equal(timestamp.$el.value, '07/10/2026 18:45');
assert.equal(timestamp.value, '2026-10-07T18:45');
timestamp.$el.value = '071020262599';
timestamp.$el.selectionStart = 12;
timestamp.$el.inputHandler({ inputType: 'insertText' });
assert.equal(timestamp.value, '');
timestamp.destroy();
console.log('Fechas CRM: formato visual, ISO, límites, fechas inválidas, actualización, limpieza y destrucción OK');
