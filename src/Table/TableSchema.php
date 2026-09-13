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
 * Class TableSchema
 *
 * Builder principal y orquestador del esquema de tablas dinámicas.
 * Produce especificaciones JSON estandarizadas (SPEC-002) para componentes
 * frontend tipo <DynamicDataTable /> o <CrudComponent /> y provee métodos de
 * introspección puros (getAllowedSorts, getAllowedFilters, getAllowedSearch).
 *
 * @phpstan-consistent-constructor
 */
class TableSchema implements SchemaContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    /**
     * Endpoint API de origen de datos (ej. '/api/v1/users').
     */
    protected ?string $endpoint = null;

    /**
     * Indica si el encabezado de la tabla permanece fijo durante el scroll vertical.
     */
    protected bool $fixedHeader = true;

    /**
     * Altura exacta del viewport de la tabla (ej. '400px').
     */
    protected ?string $height = null;

    /**
     * Altura máxima sugerida para la tabla (ej. '600px').
     */
    protected ?string $maxHeight = '600px';

    /**
     * Habilita la selección de filas con checkboxes en el frontend.
     */
    protected bool $selectable = true;

    /**
     * Clave del identificador único de cada registro (ej. 'id', 'uuid').
     */
    protected string $itemValue = 'id';

    /**
     * Ubicación visual de las pestañas contextuales: en la toolbar o arriba de la tabla.
     */
    protected TabsPosition $tabsPosition = TabsPosition::TOOLBAR;

    /**
     * Colección de pestañas contextuales de segmentación de datos.
     *
     * @var array<int, TableTab>
     */
    protected array $tabs = [];

    /**
     * Habilita o deshabilita la caja de búsqueda en la toolbar.
     */
    protected bool $searchEnabled = true;

    /**
     * Texto de ayuda (placeholder) en el input de búsqueda.
     */
    protected string $searchPlaceholder = 'Buscar...';

    /**
     * Columnas o atributos sobre los que opera la búsqueda en el backend.
     *
     * @var array<int, string>
     */
    protected array $searchFields = [];

    /**
     * Configuración de paginación del servidor.
     */
    protected PaginationConfig $pagination;

    /**
     * Configuración de papelera lógica (Soft Deletes).
     */
    protected SoftDeletesConfig $softDeletes;

    /**
     * Columnas visuales de la tabla.
     *
     * @var array<int, Column>
     */
    protected array $columns = [];

    /**
     * Filtros avanzados disponibles en el panel lateral (Drawer).
     *
     * @var array<int, FieldContract>
     */
    protected array $filters = [];

    /**
     * Determina si la columna de acciones por fila está habilitada.
     */
    protected bool $rowActionsEnabled = true;

    /**
     * Configuración de la acción de inspección 'show'.
     *
     * @var array{enabled: bool, permission: ?string}
     */
    protected array $showAction = ['enabled' => true, 'permission' => null];

    /**
     * Configuración de la acción de edición 'update'.
     *
     * @var array{enabled: bool, permission: ?string}
     */
    protected array $updateAction = ['enabled' => true, 'permission' => null];

    /**
     * Configuración de la acción de eliminación 'delete'.
     *
     * @var array{enabled: bool, permission: ?string}
     */
    protected array $deleteAction = ['enabled' => true, 'permission' => null];

    /**
     * Acciones adicionales personalizadas por fila.
     *
     * @var array<int, RowAction>
     */
    protected array $customRowActions = [];

    /**
     * Acciones superiores de la barra de herramientas (ej. botón "Nuevo", "Exportar").
     *
     * @var array<int, HeaderAction>
     */
    protected array $headerActions = [];

    /**
     * Determina si las acciones masivas sobre filas seleccionadas están activas.
     */
    protected bool $bulkActionsEnabled = true;

    /**
     * Listado de acciones masivas disponibles para elementos seleccionados.
     *
     * @var array<int, BulkAction>
     */
    protected array $bulkActions = [];

    /**
     * Mapeo de control de acceso a nivel de fila (ACL contextual).
     *
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

    /**
     * Constructor del esquema de tabla.
     *
     * @param  string|null  $id  Identificador del esquema.
     * @param  string|null  $title  Título principal.
     */
    public function __construct(?string $id = null, ?string $title = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        if ($title !== null) {
            $this->title = $title;
        }

        // Inicializa configuraciones con Smart Defaults
        $this->pagination = new PaginationConfig;
        $this->softDeletes = new SoftDeletesConfig;
    }

    /**
     * Asigna la URL del endpoint API para consultar registros.
     */
    public function endpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Retorna la URL del endpoint API o null si no se especificó.
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * Activa o desactiva el encabezado fijo durante el scroll.
     */
    public function fixedHeader(bool $fixed = true): static
    {
        $this->fixedHeader = $fixed;

        return $this;
    }

    /**
     * Asigna una altura explícita para la tabla.
     */
    public function height(?string $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Asigna una altura máxima para evitar que la tabla rompa el diseño.
     */
    public function maxHeight(?string $maxHeight): static
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    /**
     * Habilita o deshabilita la selección de múltiples filas.
     *
     * @param  string  $itemValue  Campo clave primaria (por defecto 'id').
     */
    public function selectable(bool $selectable = true, string $itemValue = 'id'): static
    {
        $this->selectable = $selectable;
        $this->itemValue = $itemValue;

        return $this;
    }

    /**
     * Define la propiedad que actúa como valor único en la selección.
     */
    public function itemValue(string $itemValue): static
    {
        $this->itemValue = $itemValue;

        return $this;
    }

    /**
     * Define la posición de las pestañas contextuales (toolbar o top).
     */
    public function tabsPosition(TabsPosition|string $position): static
    {
        $this->tabsPosition = is_string($position) ? TabsPosition::from($position) : $position;

        return $this;
    }

    /**
     * Asigna el listado de pestañas contextuales.
     *
     * @param  array<int, TableTab>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = array_values($tabs);

        return $this;
    }

    /**
     * Agrega una pestaña contextual a la tabla.
     */
    public function addTab(TableTab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    /**
     * Configura la barra de búsqueda global.
     *
     * @param  bool  $enabled  Habilitar buscador.
     * @param  string  $placeholder  Texto de ayuda.
     * @param  array<int, string>  $fields  Campos habilitados para búsqueda en el backend.
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
     * Asigna los campos de búsqueda global en el backend.
     *
     * @param  array<int, string>  $fields
     */
    public function searchFields(array $fields): static
    {
        $this->searchFields = array_values($fields);

        return $this;
    }

    /**
     * Configura la paginación de la tabla mediante callback o instancia.
     *
     * @param  PaginationConfig|(callable(PaginationConfig): void)|null  $config
     */
    public function pagination(PaginationConfig|callable|null $config = null): static
    {
        if ($config instanceof PaginationConfig) {
            $this->pagination = $config;
        } elseif (is_callable($config)) {
            $config($this->pagination);
        }

        return $this;
    }

    /**
     * Obtiene el objeto de configuración de paginación.
     */
    public function getPagination(): PaginationConfig
    {
        return $this->pagination;
    }

    /**
     * Configura la gestión de Soft Deletes (papelera, restauración y purga).
     *
     * @param  bool|SoftDeletesConfig|(callable(SoftDeletesConfig): void)  $config
     */
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

    /**
     * Obtiene el objeto de configuración de Soft Deletes.
     */
    public function getSoftDeletes(): SoftDeletesConfig
    {
        return $this->softDeletes;
    }

    /**
     * Asigna la lista de columnas de la tabla.
     *
     * @param  array<int, Column>  $columns
     */
    public function columns(array $columns): static
    {
        $this->columns = array_values($columns);

        return $this;
    }

    /**
     * Agrega una columna a la tabla.
     */
    public function addColumn(Column $column): static
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * Obtiene las columnas configuradas.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Asigna los campos de filtro avanzado para el Drawer lateral.
     *
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

    /**
     * Agrega un filtro individual al Drawer.
     */
    public function addFilter(FieldContract|Filter $filter): static
    {
        $this->filters[] = $filter instanceof Filter ? $filter->getField() : $filter;

        return $this;
    }

    /**
     * Habilita o deshabilita la columna de acciones por fila.
     */
    public function rowActions(bool $enabled = true): static
    {
        $this->rowActionsEnabled = $enabled;

        return $this;
    }

    /**
     * Configura la acción estándar de inspección ('show').
     *
     * @param  bool  $enabled  Habilitar botón de ver detalle.
     * @param  string|null  $permission  Permiso RBAC requerido.
     */
    public function showAction(bool $enabled = true, ?string $permission = null): static
    {
        $this->showAction = ['enabled' => $enabled, 'permission' => $permission];

        return $this;
    }

    /**
     * Configura la acción estándar de edición ('update').
     *
     * @param  bool  $enabled  Habilitar botón de editar.
     * @param  string|null  $permission  Permiso RBAC requerido.
     */
    public function updateAction(bool $enabled = true, ?string $permission = null): static
    {
        $this->updateAction = ['enabled' => $enabled, 'permission' => $permission];

        return $this;
    }

    /**
     * Configura la acción estándar de eliminación ('delete').
     *
     * @param  bool  $enabled  Habilitar botón de eliminar.
     * @param  string|null  $permission  Permiso RBAC requerido.
     */
    public function deleteAction(bool $enabled = true, ?string $permission = null): static
    {
        $this->deleteAction = ['enabled' => $enabled, 'permission' => $permission];

        return $this;
    }

    /**
     * Agrega una acción personalizada a las filas de la tabla.
     */
    public function addRowAction(RowAction $action): static
    {
        $this->customRowActions[] = $action;

        return $this;
    }

    /**
     * Asigna los botones de acción del encabezado de la toolbar.
     *
     * @param  array<int, HeaderAction>  $actions
     */
    public function headerActions(array $actions): static
    {
        $this->headerActions = array_values($actions);

        return $this;
    }

    /**
     * Agrega una acción a la toolbar superior.
     */
    public function addHeaderAction(HeaderAction $action): static
    {
        $this->headerActions[] = $action;

        return $this;
    }

    /**
     * Configura las acciones masivas aplicables sobre múltiples filas seleccionadas.
     *
     * @param  array<int, BulkAction>  $actions
     */
    public function bulkActions(array $actions, bool $enabled = true): static
    {
        $this->bulkActions = array_values($actions);
        $this->bulkActionsEnabled = $enabled;

        return $this;
    }

    /**
     * Agrega una acción masiva a la tabla.
     */
    public function addBulkAction(BulkAction $action): static
    {
        $this->bulkActions[] = $action;

        return $this;
    }

    /**
     * Configura el mapa de permisos ACL a nivel de fila para control granular.
     *
     * @param  string  $key  Clave en el objeto JSON de fila que contiene las banderas (ej. 'acl').
     * @param  array<string, string>  $actionMap  Mapeo de acciones a propiedades del objeto ACL.
     */
    public function acl(string $key = 'acl', array $actionMap = ['show' => 'show', 'update' => 'update', 'delete' => 'delete']): static
    {
        $this->acl = [
            'key' => $key,
            'actionMap' => $actionMap,
        ];

        return $this;
    }

    // =========================================================================
    // Métodos de Introspección Puros (SPEC-004 - Zero Coupling)
    // Permiten alimentar cualquier Query Builder (api-query-builder, spatie, eloquent)
    // sin crear dependencias entre paquetes.
    // =========================================================================

    /**
     * Retorna todas las claves de columnas configuradas con sortable === true.
     *
     * @return array<int, string>
     */
    public function getAllowedSorts(): array
    {
        $sorts = [];
        foreach ($this->columns as $col) {
            // Incluye únicamente columnas que fueron marcadas explícitamente como ordenables
            if ($col->isSortable()) {
                $sorts[] = $col->getKey();
            }
        }

        return $sorts;
    }

    /**
     * Retorna todas las claves autorizadas para filtrado en el backend,
     * unificando:
     * 1. Campos definidos en el Drawer lateral de filtros.
     * 2. Parámetros de filtrado procedentes de pestañas contextuales (tabs).
     * 3. Parámetro de Soft Deletes ('trashed') si está habilitado.
     *
     * @return array<int, string>
     */
    public function getAllowedFilters(): array
    {
        $filters = [];

        // 1. Filtros del Drawer lateral
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

        // 3. Parámetro de Soft Deletes si la papelera está activa
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
     * Serializa la tabla completa conforme al contrato formal JSON Schema (SPEC-002).
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
     * Serializa el objeto para json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
