<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class NumberField extends Field
{
    protected ?float $minVal = null;

    protected ?float $maxVal = null;

    protected ?float $stepVal = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::NUMBER;
        $this->numeric();
    }

    public function min(int|float $min): static
    {
        $this->minVal = (float) $min;

        return parent::min($min);
    }

    public function max(int|float $max): static
    {
        $this->maxVal = (float) $max;

        return parent::max($max);
    }

    public function step(int|float $step): static
    {
        $this->stepVal = (float) $step;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'min' => $this->minVal,
            'max' => $this->maxVal,
            'step' => $this->stepVal,
        ]);
    }
}
