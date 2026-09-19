<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * Trait: Listing
 * Purpose: Provides a standardized set of tools for tables, pagination, and sorting.
 * Features: URL state persistence, multi-record selection, and data sanitization.
 */
trait Listing
{
    // Search and Pagination State
    public string $search = '';

    public $per_page = 10;

    public $page = 1;

    protected int $paginate = 20;

    /**
     * URL Attributes:
     * Ensures sort state remains in the query string for shareable links.
     */
    #[Url]
    public string $sortColumn;

    #[Url]
    public string $sortDirection;

    /**
     * Record Selection Logic:
     * Essential for bulk actions (Delete, Export) and checkbox synchronization.
     */
    public array $recordIdsOnPage = []; // Stores the record IDs displayed on the current page.

    public array $selectedRecordIds = []; // Stores the IDs of the records selected by the user.

    // UI Theme: Integrated with AdminLTE / Bootstrap 4 standards
    protected string $paginationTheme = 'bootstrap';

    /**
     * Pipeline: Applies the current sort state to an Eloquent query.
     */
    public function applySort(Builder $query): Builder
    {
        $model = $query->getModel();
        $column = $this->sortColumn;
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)
            || !$model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $column)) {
            $column = $model->getKeyName();
        }
        return $query->orderBy($model->qualifyColumn($column), $this->sortDirection === 'asc' ? 'asc' : 'desc');
    }

    /**
     * Executes the pagination based on defined per_page limits.
     */
    public function applyPaginate(Builder $query): LengthAwarePaginator
    {
        return $query->paginate($this->per_page, '*', 'page', $this->page);
    }

    /**
     * Toggle logic for table headers. Switches between ASC/DESC or sets a new column.
     */
    public function sortBy(string $column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
            $this->sortColumn = $column;
        }
    }

    /**
     * Lifecycle Hook: Resets the page view whenever the search string is modified.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Utility: Converts strings to Sentence Case (e.g. "CINEMA" -> "Cinema").
     */
    public function capitalizeText($text)
    {
        return ucfirst(strtolower($text));
    }

    /**
     * Security/Sanitization:
     * Cleans input to prevent injection while allowing Spanish characters,
     *
     *   This method allows the following:
     *       Letters a-zA-Z
     *       Accented letters áéíóúÁÉÍÓÚüÜñÑ
     *       Numbers 0-9
     *       Hyphen -
     *       Underscore _
     *       Space
     *       at @
     *       dot .
     *       slash /
     */
    public function sanitizeValue($input)
    {
        return preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ@_\s.-]/u', '', $input);
    }
}
