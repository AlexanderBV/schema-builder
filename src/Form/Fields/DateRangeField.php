<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class DateRangeField extends Field
{
    protected bool $range = true;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::DATE_RANGE;
    }

    public function range(bool $range = true): static
    {
        $this->range = $range;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'range' => $this->range,
        ]);
    }
}
