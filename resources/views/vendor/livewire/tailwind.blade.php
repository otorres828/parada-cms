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
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navegación de paginación" class="mb-3 flex items-center justify-between px-4 py-3 bg-surface-container-high border-t border-white/5 sm:px-6">

            {{-- Vista Móvil --}}
            <div class="flex justify-between flex-1 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-surface-container-high border border-white/5 cursor-default leading-5 rounded-md">
                            Anterior
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-surface-container-high border border-white/5 leading-5 rounded-md hover:text-gray-100 hover:bg-white/5 focus:outline-none focus:ring ring-blue-500/30 transition ease-in-out duration-150">
                            Anterior
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-300 bg-surface-container-high border border-white/5 leading-5 rounded-md hover:text-gray-100 hover:bg-white/5 focus:outline-none focus:ring ring-blue-500/30 transition ease-in-out duration-150">
                            Siguiente
                        </button>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-surface-container-high border border-white/5 cursor-default leading-5 rounded-md">
                            Siguiente
                        </span>
                    @endif
                </span>
            </div>

            {{-- Vista Escritorio --}}
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div class="flex items-center space-x-4">
                    {{-- Selector Per Page --}}
                    <div class="flex items-center space-x-2 text-sm text-on-surface-variant">
                        <span>Mostrar</span>
                        <select wire:model.live="per_page" class="mx-1 block pl-2 pr-8 py-1 text-sm bg-surface-container border border-white/5 text-on-surface focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary rounded-md outline-none">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>registros</span>
                    </div>

                    {{-- Info de Resultados --}}
                    <p class="text-sm text-on-surface-variant leading-5">
                        <span>Mostrando</span>
                        <span class="font-medium text-on-surface">{{ $paginator->firstItem() ?? 0 }}</span>
                        <span>al</span>
                        <span class="font-medium text-on-surface">{{ $paginator->lastItem() ?? 0 }}</span>
                        <span>de</span>
                        <span class="font-medium text-on-surface">{{ $paginator->total() }}</span>
                        <span>resultados</span>
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex rounded-md shadow-sm">
                        <span>
                            {{-- Link Página Anterior --}}
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="Anterior">
                                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-surface-container-high border border-white/5 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-on-surface-variant bg-surface-container-high border border-white/5 rounded-l-md leading-5 hover:text-on-surface hover:bg-white/5 focus:z-10 focus:outline-none focus:border-primary focus:ring ring-primary/30 active:bg-white/10 transition ease-in-out duration-150" aria-label="Anterior">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </span>

                        {{-- Elementos de la Paginación --}}
                        @foreach ($elements as $element)
                            {{-- Separador de Puntos --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-surface-container-high border border-white/5 cursor-default leading-5">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Enlaces de Páginas --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-bold text-primary bg-primary/10 border border-primary/30 cursor-default leading-5">{{ $page }}</span>
                                            </span>
                                        @else
                                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-on-surface-variant bg-surface-container-high border border-white/5 leading-5 hover:text-on-surface hover:bg-white/5 focus:z-10 focus:outline-none focus:border-primary focus:ring ring-primary/30 active:bg-white/10 transition ease-in-out duration-150" aria-label="Ir a página {{ $page }}">
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach

                        <span>
                            {{-- Link Página Siguiente --}}
                            @if ($paginator->hasMorePages())
                                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-on-surface-variant bg-surface-container-high border border-white/5 rounded-r-md leading-5 hover:text-on-surface hover:bg-white/5 focus:z-10 focus:outline-none focus:border-primary focus:ring ring-primary/30 active:bg-white/10 transition ease-in-out duration-150" aria-label="Siguiente">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="Siguiente">
                                    <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-surface-container-high border border-white/5 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
