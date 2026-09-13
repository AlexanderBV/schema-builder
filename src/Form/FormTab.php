<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\FieldContainerContract;
use Warrior\SchemaBuilder\Contracts\FieldContract;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class FormTab implements Arrayable, FieldContainerContract, JsonSerializable
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected ?string $icon = null;

    protected ?int $badge = null;

    protected ?string $badgeColor = null;

    /**
     * @var array<int, FieldContract>
     */
    protected array $inputs = [];

    public function __construct(string $id, ?string $name = null)
    {
        $this->id = $id;
        $this->title = $name ?? ucwords(str_replace(['_', '-'], ' ', $id));
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function badge(?int $badge, ?string $color = null): static
    {
        $this->badge = $badge;
        $this->badgeColor = $color;

        return $this;
    }

    /**
     * Asigna el conjunto de campos a la pestaña.
     *
     * @param  array<int, FieldContract>  $fields
     */
    public function fields(array $fields): static
    {
        $this->inputs = array_values($fields);

        return $this;
    }

    public function addField(FieldContract $field): static
    {
        $this->inputs[] = $field;

        return $this;
    }

    /**
     * @return array<int, FieldContract>
     */
    public function getFields(): array
    {
        return $this->inputs;
    }

    /**
     * Compila las reglas de validación de los campos de esta pestaña.
     *
     * @return array<string, array<int, mixed>>
     */
    public function toValidationRules(bool $isUpdate = false): array
    {
        $rules = [];

        foreach ($this->inputs as $field) {
            $fieldRules = $field->getValidationRules($isUpdate);
            if (! empty($fieldRules)) {
                $rules[$field->getName()] = $fieldRules;
            }
        }

        return $rules;
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
            'badge' => $this->badge,
            'badgeColor' => $this->badgeColor,
            'inputs' => array_map(fn (FieldContract $field) => $field->toArray(), $this->inputs),
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
