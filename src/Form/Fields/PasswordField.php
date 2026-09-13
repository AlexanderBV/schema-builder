<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class PasswordField extends Field
{
    protected bool $toggleVisibility = true;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::PASSWORD;
    }

    public function toggleVisibility(bool $toggle = true): static
    {
        $this->toggleVisibility = $toggle;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'toggleVisibility' => $this->toggleVisibility,
        ]);
    }
}
