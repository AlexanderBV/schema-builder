<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class TextField extends Field
{
    protected ?int $maxLength = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::TEXT;
    }

    public function maxLength(int $length): static
    {
        $this->maxLength = $length;
        $this->max($length);

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'maxLength' => $this->maxLength,
        ]);
    }
}
