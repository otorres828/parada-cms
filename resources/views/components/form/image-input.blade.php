@props([
    'name',
    'label' => null,
    'preview' => null,
    'refName' => null,
    'placeholder' => 'Selecciona una imagen',
    'maxWidth' => '200px',
    'placeholderWidth' => '200px',
    'placeholderHeight' => '120px',
])

@php
    $refName = $refName ?? $name;
@endphp

<div>
    @if (!empty($label))
        <label class="form-label">{{ $label }}</label> <br>
    @endif

    <div style="cursor:pointer; display:inline-block;" @click="$refs.{{ $refName }}.click()">
        <template x-if="!!{{ $preview }}">
            <img :src="{{ $preview }}" alt="Imagen Home" class="img-thumbnail mb-2"
                style="max-width: {{ $maxWidth }};">
        </template>
        <template x-if="!{{ $preview }}">
            <div 
                class="img-thumbnail mb-2 d-flex align-items-center justify-content-center bg-light text-secondary border-dashed"
                style="width: {{ $placeholderWidth }}; height: {{ $placeholderHeight }};">

                <div class="text-center">
                    <i class="bi bi-cloud-arrow-up fs-2 d-block mb-0"></i>
                    <span class="fs-7">{{ $placeholder }}</span>
                </div>
            
            </div>
        </template>

        <template x-if="!!{{ $preview }}">

            <a 
            :href="typeof {{ $preview }} === 'string' ? {{ $preview }} : URL.createObjectURL({{ $preview }})" 
            target="_blank" 
            @click.stop
            class="mt-2 fs-7"> 
            Ver imagen </a>
        </template>

    </div>


    <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/*"
        @change="onFileChange($event, '{{ $name }}')" class="form-control" x-ref="{{ $refName }}"
        style="display:none;">
</div>
