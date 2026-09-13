<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class SwitchField extends Field
{
    protected mixed $trueValue = true;

    protected mixed $falseValue = false;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::SWITCH;
    }

    public function trueValue(mixed $value): static
    {
        $this->trueValue = $value;

        return $this;
    }

    public function falseValue(mixed $value): static
    {
        $this->falseValue = $value;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueValue' => $this->trueValue,
            'falseValue' => $this->falseValue,
        ]);
    }
}
