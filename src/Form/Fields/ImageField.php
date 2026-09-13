<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;

class ImageField extends FileField
{
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::IMAGE;
        $this->accept = 'image/*';
        $this->addRule('image');
    }
}
