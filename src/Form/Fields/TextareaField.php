<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class TextareaField extends Field
{
    protected int $rows = 3;

    protected bool $autoGrow = true;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::TEXTAREA;
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function autoGrow(bool $autoGrow = true): static
    {
        $this->autoGrow = $autoGrow;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'rows' => $this->rows,
            'autoGrow' => $this->autoGrow,
        ]);
    }
}
