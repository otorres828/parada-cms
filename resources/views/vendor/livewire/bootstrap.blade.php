@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
        (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages() || true)
        <nav class="d-flex justify-items-center justify-content-between align-items-center border-top pt-3">

            {{-- Vista Móvil (Solo botones simplificados) --}}
            <div class="d-flex justify-content-between flex-fill d-sm-none">
                <ul class="pagination m-0">
                    {{-- Botón Anterior --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">Anterior</span>
                        </li>
                    @else
                        <li class="page-item">
                            <button type="button" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled">Anterior</button>
                        </li>
                    @endif

                    {{-- Botón Siguiente --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <button type="button" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled">Siguiente</button>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-hidden="true">Siguiente</span>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Vista Escritorio (Estructura completa con controles) --}}
            <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between w-100">

                {{-- Controles Izquierdos: Selector de Registros e Información --}}
                <div class="d-flex align-items-center gap-3">

                    {{-- Selector Per Page --}}
                    <div class="d-flex align-items-center small text-muted">
                        <span>Mostrar</span>
                        <select wire:model.live="per_page" class="form-select form-select-sm mx-2" style="width: auto;">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>registros</span>
                    </div>

                    {{-- Info de los Resultados --}}
                    <p class="small text-muted m-0">
                        Mostrando
                        <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
                        al
                        <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
                        de
                        <span class="fw-semibold">{{ $paginator->total() }}</span>
                        resultados
                    </p>
                </div>

                {{-- Controles Derechos: Botonera numérica de páginas --}}
                <div>
                    @if ($paginator->hasPages())
                        <ul class="pagination m-0">
                            {{-- Link Página Anterior --}}
                            @if ($paginator->onFirstPage())
                                <li class="page-item disabled" aria-disabled="true" aria-label="Anterior">
                                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <button type="button" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="Anterior">&lsaquo;</button>
                                </li>
                            @endif

                            {{-- Elementos de la Paginación --}}
                            @foreach ($elements as $element)
                                {{-- Separador de Puntos Suspensivos --}}
                                @if (is_string($element))
                                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                                @endif

                                {{-- Enlaces de Páginas Disponibles --}}
                                @if (is_array($element))
                                    @foreach ($element as $page => $url)
                                        @if ($page == $paginator->currentPage())
                                            <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"><button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}">{{ $page }}</button></li>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach

                            {{-- Link Página Siguiente --}}
                            @if ($paginator->hasMorePages())
                                <li class="page-item">
                                    <button type="button" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="Siguiente">&rsaquo;</button>
                                </li>
                            @else
                                <li class="page-item disabled" aria-disabled="true" aria-label="Siguiente">
                                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </nav>
    @endif
</div>
