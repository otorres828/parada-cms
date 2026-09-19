@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 mt-4">
    <div>
        <h1 class="font-display-lg text-headline-lg text-primary tracking-tight">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="text-on-surface-variant mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if (isset($actions))
        <div>
            {{ $actions }}
        </div>
    @endif
</div>
