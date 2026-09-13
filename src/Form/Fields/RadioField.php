<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Concerns\HasOptions;
use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class RadioField extends Field
{
    use HasOptions;

    protected bool $inline = false;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::RADIO;
    }

    public function inline(bool $inline = true): static
    {
        $this->inline = $inline;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->getOptions(),
            'inline' => $this->inline,
        ]);
    }
}
