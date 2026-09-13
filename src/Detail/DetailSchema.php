<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Detail;

use Illuminate\Support\Traits\Macroable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\SchemaContract;

/**
 * Class DetailSchema
 *
 * Builder de especificaciones de vistas de inspección de sólo lectura (Detail View).
 * Utilizado por <CrudComponent /> al hacer clic en el botón 'show' [👁] o en vistas
 * completas de consulta (/recurso/{id}/detail).
 *
 * @phpstan-consistent-constructor
 */
class DetailSchema implements SchemaContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    /**
     * Lista de celdas de inspección en vista plana.
     *
     * @var array<int, DetailField>
     */
    protected array $fields = [];

    /**
     * Pestañas de inspección para vistas complejas estructuradas.
     *
     * @var array<int, DetailTab>
     */
    protected array $tabs = [];

    /**
     * Constructor de la vista de detalle.
     *
     * @param  string|null  $id  Identificador único del detalle.
     * @param  string|null  $title  Título del panel de inspección.
     */
    public function __construct(?string $id = null, ?string $title = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        if ($title !== null) {
            $this->title = $title;
        }
    }

    /**
     * Asigna el listado de campos para una vista de detalle plana.
     *
     * @param  array<int, DetailField>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = array_values($fields);

        return $this;
    }

    /**
     * Agrega un campo de detalle a la lista plana.
     */
    public function addField(DetailField $field): static
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Asigna pestañas de inspección para organizar datos complejos.
     *
     * @param  array<int, DetailTab>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = array_values($tabs);

        return $this;
    }

    /**
     * Agrega una pestaña de inspección al esquema de detalle.
     */
    public function addTab(DetailTab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    /**
     * Determina si la vista de detalle está dividida en pestañas.
     */
    public function hasTabs(): bool
    {
        return ! empty($this->tabs);
    }

    /**
     * Retorna todos los campos contenidos en el detalle.
     *
     * @return array<int, DetailField>
     */
    public function getFields(): array
    {
        if ($this->hasTabs()) {
            $fields = [];
            foreach ($this->tabs as $tab) {
                $fields = array_merge($fields, $tab->getFields());
            }

            return $fields;
        }

        return $this->fields;
    }

    /**
     * Serializa el esquema de detalle al formato JSON Schema (SPEC-009).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ];

        if ($this->hasTabs()) {
            $data['tabs'] = array_map(fn (DetailTab $tab) => $tab->toArray(), $this->tabs);
            $data['fields'] = null;
        } else {
            $data['tabs'] = null;
            $data['fields'] = array_map(fn (DetailField $field) => $field->toArray(), $this->fields);
        }

        return $data;
    }

    /**
     * Serializa para json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
