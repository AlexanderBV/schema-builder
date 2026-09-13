<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class DateTimeField extends Field
{
    protected string $format = 'YYYY-MM-DD HH:mm';

    protected bool $enableTime = true;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::DATETIME;
    }

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function enableTime(bool $enable = true): static
    {
        $this->enableTime = $enable;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
            'enableTime' => $this->enableTime,
        ]);
    }
}
