# Fechas en formularios

Los campos de fecha con hora muestran `dd/mm/aaaa hh:mm`, en formato de 24 horas. `x-form.text-input` con `type="datetime-local"` también utiliza el calendario, conservando el valor interno `Y-m-dTH:i` de esos formularios.

Los campos del CRM muestran `dd/mm/aaaa`, independientemente del idioma del navegador o del dispositivo. Livewire, las URLs de filtros, las validaciones PHP y la base de datos conservan `Y-m-d`. Los valores iniciales en `mount()` mantienen ese formato interno; no se cambian a `d/m/Y`.

`x-form.date-input` utiliza Flatpickr con idioma español y se vincula a la propiedad Livewire mediante `entangle`. Respeta el modificador `.live`; los campos sin él conservan el envío diferido. `x-form.text-input` utiliza este mismo componente cuando recibe `type="date"`.

La lógica compartida vive en `public/js/crm-fechas.js`, cargada después de Flatpickr en los scripts del layout. Conserva los atributos del campo, incluidos identificador, formulario, obligatoriedad y límites. Al actualizar Livewire sincroniza la fecha y los límites; al restablecer la propiedad limpia el campo y al retirar el componente destruye el calendario.

El calendario muestra el formato español también en móviles. La entrada manual exige una fecha válida en ese formato; por ejemplo `12/11/2026` corresponde al 12 de noviembre y se envía como `2026-11-12`. Las validaciones del servidor continúan siendo obligatorias.
