@php
    $model = $attributes->wire('model');
    $property = $model->value() ?: preg_replace('/^\$wire\./', '', $attributes->get('x-model', ''));
    $binding = '$wire.entangle('.json_encode($property).')'.($model->hasModifier('live') ? '.live' : '');
@endphp

<div wire:ignore class="flex-grow-1" x-data="crmFecha({ value: {{ $binding }}, withTime: true })">

    <input
        x-ref="input"
        type="text"
        placeholder="dd/mm/aaaa hh:mm"
        autocomplete="off"
        {{ $attributes->whereDoesntStartWith('wire:model')->except(['type', 'value', 'x-model'])->merge(['class' => 'form-control']) }}
    />

</div>
