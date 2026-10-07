const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const blade = fs.readFileSync('resources/views/components/empresas/reservas/pasajero.blade.php', 'utf8');
let factory, alerts = 0, calls = 0;
vm.runInNewContext(blade.match(/<script>([\s\S]*?)<\/script>/)[1], {
    Alpine: { data(name, callback) { factory = callback; } },
    Swal: { async fire(options) { assert.equal(options.icon, 'warning'); alerts++; } },
    JustValidate: class { addField() {} destroy() {} async revalidate() { return true; } },
});
(async () => {
    const form = factory({ tipo: 'adulto' });
    form.$refs = { pasajeroForm: { querySelectorAll() { return []; } } };
    form.$wire = {
        pasajero: { tipo_documento: '1', documento_identidad: 'v-12 345' },
        pasajeros: [{ tipo_documento: 1, documento_identidad: 'V12345' }],
        async agregarPasajero() { calls++; },
    };
    await form.enviar();
    assert.equal(alerts, 1);
    assert.equal(calls, 0);
    form.$wire.pasajero.tipo_documento = '3';
    await form.enviar();
    assert.equal(calls, 1);
    form.$wire.pasajero.documento_identidad = '';
    await form.enviar();
    assert.equal(calls, 2);
    assert.equal(form.saving, false);
    console.log('Pasajero Alpine: alerta de duplicado normalizado, tipo distinto y sin documento OK');
})().catch(error => { console.error(error); process.exitCode = 1; });
