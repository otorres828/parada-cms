@props(['withTime' => false])

@php
    $model = $attributes->wire('model');
    $property = $model->value() ?? preg_replace('/^\$wire\./', '', $attributes->get('x-model', ''));
    $binding = '$wire.entangle('.json_encode($property).')'.($model->hasModifier('live') ? '.live' : '');
@endphp

<input
    type="text"
    placeholder="{{ $withTime ? 'dd/mm/aaaa hh:mm' : 'dd/mm/aaaa' }}"
    autocomplete="off"
    x-data="crmFecha({ value: {{ $binding }}, withTime: @js($withTime) })"
    {{ $attributes->whereDoesntStartWith('wire:model')->except(['type', 'value', 'x-model'])->merge(['class' => 'form-control']) }}
/>
