@props([
    'label' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if($label || $aside ?? false)
        <div class="flex items-center justify-between">
            @if($label)
                <label class="font-label-md text-label-md text-on-surface-variant">
                    {{ $label }}
                    @if($required)
                        <span class="text-danger">*</span>
                    @endif
                </label>
            @endif

            @if(isset($aside))
                {{ $aside }}
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>
</div>
