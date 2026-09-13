<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class CheckboxField extends Field
{
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::CHECKBOX;
    }
}
