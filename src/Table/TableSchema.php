<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table;

use Illuminate\Support\Traits\Macroable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\FieldContract;
use Warrior\SchemaBuilder\Contracts\SchemaContract;
use Warrior\SchemaBuilder\Enums\TabsPosition;

/**
 * @phpstan-consistent-constructor
 */
class TableSchema implements SchemaContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected ?string $endpoint = null;

    protected bool $fixedHeader = true;

    protected ?string $height = null;

    protected ?string $maxHeight = '600px';

    protected bool $selectable = true;

    protected string $itemValue = 'id';

    protected TabsPosition $tabsPosition = TabsPosition::TOOLBAR;

    /**
     * @var array<int, TableTab>
     */
    protected array $tabs = [];

    protected bool $searchEnabled = true;

    protected string $searchPlaceholder = 'Buscar...';

    /**
     * @var array<int, string>
     */
    protected array $searchFields = [];

    protected PaginationConfig $pagination;

    protected SoftDeletesConfig $softDeletes;

    /**
     * @var array<int, Column>
     */
    protected array $columns = [];

    /**
     * @var array<int, FieldContract>
     */
    protected array $filters = [];

    protected bool $rowActionsEnabled = true;

    /**
     * @var array{enabled: bool, permission: ?string}
     */
    protected array $showAction = ['enabled' => true, 'permission' => null];

    /**
     * @var array{enabled: bool, permission: ?string}
     */
    protected array $updateAction = ['enabled' => true, 'permission' => null];

    /**
     * @var array{enabled: bool, permission: ?string}
     */
    protected array $deleteAction = ['enabled' => true, 'permission' => null];

    /**
     * @var array<int, RowAction>
     */
    protected array $customRowActions = [];

    /**
     * @var array<int, HeaderAction>
     */
    protected array $headerActions = [];

    protected bool $bulkActionsEnabled = true;

    /**
     * @var array<int, BulkAction>
     */
    protected array $bulkActions = [];

    /**
     * @var array{key: string, actionMap: array<string, string>}
     */
    protected array $acl = [
        'key' => 'acl',
        'actionMap' => [
            'show' => 'show',
            'update' => 'update',
            'delete' => 'delete',
        ],
    ];

    public function __construct(?string $id = null, ?string $title = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        if ($title !== null) {
            $this->title = $title;
        }

        $this->pagination = new PaginationConfig;
        $this->softDeletes = new SoftDeletesConfig;
    }

    public function endpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function fixedHeader(bool $fixed = true): static
    {
        $this->fixedHeader = $fixed;

        return $this;
    }

    public function height(?string $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function maxHeight(?string $maxHeight): static
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    public function selectable(bool $selectable = true, string $itemValue = 'id'): static
    {
        $this->selectable = $selectable;
        $this->itemValue = $itemValue;

        return $this;
    }

    public function itemValue(string $itemValue): static
    {
        $this->itemValue = $itemValue;

        return $this;
    }

    public function tabsPosition(TabsPosition|string $position): static
    {
        $this->tabsPosition = is_string($position) ? TabsPosition::from($position) : $position;

        return $this;
    }

    /**
     * @param  array<int, TableTab>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = array_values($tabs);

        return $this;
    }

    public function addTab(TableTab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    /**
     * @param  array<int, string>  $fields
     */
    public function search(bool $enabled = true, string $placeholder = 'Buscar...', array $fields = []): static
    {
        $this->searchEnabled = $enabled;
        $this->searchPlaceholder = $placeholder;
        if (! empty($fields)) {
            $this->searchFields = array_values($fields);
        }

        return $this;
    }

    /**
     * @param  array<int, string>  $fields
     */
    public function searchFields(array $fields): static
    {
        $this->searchFields = array_values($fields);

        return $this;
    }

    public function pagination(PaginationConfig|callable|null $config = null): static
    {
        if ($config instanceof PaginationConfig) {
            $this->pagination = $config;
        } elseif (is_callable($config)) {
            $config($this->pagination);
        }

        return $this;
    }

    public function getPagination(): PaginationConfig
    {
        return $this->pagination;
    }

    public function softDeletes(bool|SoftDeletesConfig|callable $config = true): static
    {
        if (is_bool($config)) {
            $this->softDeletes->enable($config);
        } elseif ($config instanceof SoftDeletesConfig) {
            $this->softDeletes = $config;
        } elseif (is_callable($config)) {
            $this->softDeletes->enable(true);
            $config($this->softDeletes);
        }

        return $this;
    }

    public function getSoftDeletes(): SoftDeletesConfig
    {
        return $this->softDeletes;
    }

    /**
     * @param  array<int, Column>  $columns
     */
    public function columns(array $columns): static
    {
        $this->columns = array_values($columns);

        return $this;
    }

    public function addColumn(Column $column): static
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param  array<int, FieldContract|Filter>  $filters
     */
    public function filters(array $filters): static
    {
        $resolved = [];
        foreach ($filters as $filter) {
            if ($filter instanceof Filter) {
                $resolved[] = $filter->getField();
            } else {
                $resolved[] = $filter;
            }
        }
        $this->filters = $resolved;

        return $this;
    }

    public function addFilter(FieldContract|Filter $filter): static
    {
        $this->filters[] = $filter instanceof Filter ? $filter->getField() : $filter;

        return $this;
    }

    public function rowActions(bool $enabled = true): static
    {
        $this->rowActionsEnabled = $enabled;

        return $this;
    }

    public function showAction(bool $enabled = true, ?string $permission = null): static
    {
        $this->showAction = ['enabled' => $enabled, 'permission' => $permission];

        return $this;
    }

    public function updateAction(bool $enabled = true, ?string $permission = null): static
    {
        $this->updateAction = ['enabled' => $enabled, 'permission' => $permission];

        return $this;
    }

    public function deleteAction(bool $enabled = true, ?string $permission = null): static
    {
        $this->deleteAction = ['enabled' => $enabled, 'permission' => $permission];

        return $this;
    }

    public function addRowAction(RowAction $action): static
    {
        $this->customRowActions[] = $action;

        return $this;
    }

    /**
     * @param  array<int, HeaderAction>  $actions
     */
    public function headerActions(array $actions): static
    {
        $this->headerActions = array_values($actions);

        return $this;
    }

    public function addHeaderAction(HeaderAction $action): static
    {
        $this->headerActions[] = $action;

        return $this;
    }

    /**
     * @param  array<int, BulkAction>  $actions
     */
    public function bulkActions(array $actions, bool $enabled = true): static
    {
        $this->bulkActions = array_values($actions);
        $this->bulkActionsEnabled = $enabled;

        return $this;
    }

    public function addBulkAction(BulkAction $action): static
    {
        $this->bulkActions[] = $action;

        return $this;
    }

    /**
     * @param  array<string, string>  $actionMap
     */
    public function acl(string $key = 'acl', array $actionMap = ['show' => 'show', 'update' => 'update', 'delete' => 'delete']): static
    {
        $this->acl = [
            'key' => $key,
            'actionMap' => $actionMap,
        ];

        return $this;
    }

    // --- Métodos de Introspección Puros (SPEC-004) ---

    /**
     * Retorna todas las claves de columnas configuradas con sortable === true.
     *
     * @return array<int, string>
     */
    public function getAllowedSorts(): array
    {
        $sorts = [];
        foreach ($this->columns as $col) {
            if ($col->isSortable()) {
                $sorts[] = $col->getKey();
            }
        }

        return $sorts;
    }

    /**
     * Retorna todas las claves definidas en los filtros del Drawer, tabs contextuales
     * y Soft Deletes ('trashed' si está habilitado).
     *
     * @return array<int, string>
     */
    public function getAllowedFilters(): array
    {
        $filters = [];

        // 1. Filtros del Drawer
        foreach ($this->filters as $filter) {
            $filters[] = $filter->getName();
        }

        // 2. Filtros de las pestañas contextuales
        foreach ($this->tabs as $tab) {
            if ($tab->hasFilter()) {
                foreach (array_keys($tab->getFilter()) as $key) {
                    $filters[] = (string) $key;
                }
            }
        }

        // 3. Soft Deletes
        if ($this->softDeletes->isEnabled()) {
            $filters[] = $this->softDeletes->getQueryParam();
        }

        return array_values(array_unique($filters));
    }

    /**
     * Retorna los campos permitidos para la búsqueda global.
     *
     * @return array<int, string>
     */
    public function getAllowedSearch(): array
    {
        return $this->searchFields;
    }

    /**
     * Serializa la tabla conforme al contrato SPEC-002.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'subtitle' => $this->getSubtitle(),
            'endpoint' => $this->endpoint,

            'fixedHeader' => $this->fixedHeader,
            'height' => $this->height,
            'maxHeight' => $this->maxHeight,

            'selectable' => $this->selectable,
            'itemValue' => $this->itemValue,

            'tabsPosition' => $this->tabsPosition->value,
            'tabs' => array_map(fn (TableTab $tab) => $tab->toArray(), $this->tabs),

            'search' => [
                'enabled' => $this->searchEnabled,
                'placeholder' => $this->searchPlaceholder,
            ],

            'pagination' => $this->pagination->toArray(),
            'softDeletes' => $this->softDeletes->toArray(),

            'columns' => array_map(fn (Column $col) => $col->toArray(), $this->columns),

            'filters' => [
                'enabled' => ! empty($this->filters),
                'filterForm' => [
                    'inputs' => array_map(fn (FieldContract $f) => $f->toArray(), $this->filters),
                ],
            ],

            'rowActions' => [
                'enabled' => $this->rowActionsEnabled,
                'show' => $this->showAction,
                'update' => $this->updateAction,
                'delete' => $this->deleteAction,
                'custom' => array_map(fn (RowAction $action) => $action->toArray(), $this->customRowActions),
            ],

            'headerActions' => array_map(fn (HeaderAction $action) => $action->toArray(), $this->headerActions),

            'bulkActions' => [
                'enabled' => $this->bulkActionsEnabled,
                'actions' => array_map(fn (BulkAction $action) => $action->toArray(), $this->bulkActions),
            ],

            'acl' => $this->acl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
