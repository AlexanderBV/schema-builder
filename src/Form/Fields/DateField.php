<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class DateField extends Field
{
    protected string $format = 'YYYY-MM-DD';

    protected ?string $minDate = null;

    protected ?string $maxDate = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::DATE;
    }

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function minDate(string $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    public function maxDate(string $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
        ]);
    }
}
