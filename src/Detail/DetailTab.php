<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Detail;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class DetailTab implements Arrayable, JsonSerializable
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected ?string $icon = null;

    /**
     * @var array<int, DetailField>
     */
    protected array $fields = [];

    public function __construct(string $id, ?string $title = null)
    {
        $this->id = $id;
        $this->title = $title ?? ucwords(str_replace(['_', '-'], ' ', $id));
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
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
     * @return array<int, DetailField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getTitle() ?? '',
            'icon' => $this->icon,
            'fields' => array_map(fn (DetailField $field) => $field->toArray(), $this->fields),
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
