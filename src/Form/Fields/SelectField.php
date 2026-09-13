<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Concerns\HasOptions;
use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class SelectField extends Field
{
    use HasOptions;

    protected bool $multiple = false;

    protected bool $chips = false;

    protected bool $autocomplete = false;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::SELECT;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function chips(bool $chips = true): static
    {
        $this->chips = $chips;

        return $this;
    }

    public function autocomplete(bool $autocomplete = true): static
    {
        $this->autocomplete = $autocomplete;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->getOptions(),
            'multiple' => $this->multiple,
            'chips' => $this->chips,
            'autocomplete' => $this->autocomplete,
        ]);
    }
}
