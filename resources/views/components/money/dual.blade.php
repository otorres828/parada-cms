@props(['usd', 'bs' => null])

<span {{ $attributes->class(['text-nowrap']) }}>
    ${{ number_format((float) ($usd ?? 0), 2, '.', ',') }}
    <span class="text-body-secondary">/</span>
    @if ($bs !== null)
        Bs. {{ number_format((float) $bs, 2, ',', '.') }}
    @else
        <span class="text-body-secondary">Bs. —</span>
    @endif
</span>
