<div class="d-flex justify-content-between align-items-center mt-3 bg-light p-2 rounded border" x-show="meta.total > 0"
    x-cloak>
    <div class="small text-muted ps-2" x-text="metaText()"></div>

    <nav>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="page <= 1 ? 'disabled' : ''">
                <button class="page-link" @click="prevPage()" :disabled="page <= 1">Anterior</button>
            </li>

            <template x-for="(p, idx) in pageNumbers()" :key="'p-' + idx">
                <li class="page-item"
                    :class="{
                        'active': Number.isInteger(p) && p === page,
                        'disabled': p === '...'
                    }">
                    <template x-if="p === '...'">
                        <span class="page-link">&hellip;</span>
                    </template>
                    <template x-if="Number.isInteger(p)">
                        <button class="page-link" @click="goToPage(p)" x-text="p"></button>
                    </template>
                </li>
            </template>

            <li class="page-item" :class="page >= meta.last_page ? 'disabled' : ''">
                <button class="page-link" @click="nextPage()" :disabled="page >= meta.last_page">Siguiente</button>
            </li>
        </ul>
    </nav>
</div>

@once
    <script>
        window.paginationMixin = window.paginationMixin || (() => ({
            meta: {
                current_page: 1,
                last_page: 1,
                per_page: 0,
                total: 0,
                from: 0,
                to: 0,
            },
            page: 1,
            prevPage() {
                if (this.page > 1) {
                    if (this.page - 1 !== null) {
                        this.page = this.page - 1;
                    }
                    this.fetch()
                };
            },

            nextPage() {
                if (this.page < (this.meta?.last_page ?? 1)) {
                     if (this.page + 1 !== null) {
                        this.page = this.page + 1;
                    }
                    this.fetch()
                };
            },

            goToPage(p) {
                const total = this.meta?.last_page ?? 1;
                if (Number.isInteger(p) && p >= 1 && p <= total && p !== this.page) {
                    if (p !== null) {
                        this.page = p;
                    }
                    this.fetch(p);
                }
            },

            pageNumbers() {
                const total = this.meta?.last_page ?? 1;
                const current = this.page ?? 1;
                const pages = [];

                if (total <= 7) {
                    for (let i = 1; i <= total; i++) pages.push(i);
                    return pages;
                }

                pages.push(1);
                let start = Math.max(2, current - 2);
                let end = Math.min(total - 1, current + 2);

                if (start > 2) pages.push('...');
                for (let i = start; i <= end; i++) pages.push(i);
                if (end < total - 1) pages.push('...');

                pages.push(total);
                return pages;
            },

            metaText() {
                if (!this.meta || this.meta.total === 0) return 'No hay registros';
                return `Mostrando desde ${this.meta.from} hasta ${this.meta.to} de ${this.meta.total} registros`;
            }
        }));
    </script>
@endonce
