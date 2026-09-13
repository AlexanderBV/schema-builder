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
     * @var array<int, DetailField>
     */
    protected array $fields = [];

    /**
     * @var array<int, DetailTab>
     */
    protected array $tabs = [];

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
     * @param  array<int, DetailField>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = array_values($fields);

        return $this;
    }

    public function addField(DetailField $field): static
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param  array<int, DetailTab>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = array_values($tabs);

        return $this;
    }

    public function addTab(DetailTab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    public function hasTabs(): bool
    {
        return ! empty($this->tabs);
    }

    /**
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
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
