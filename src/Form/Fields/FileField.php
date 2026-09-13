<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class FileField extends Field
{
    protected ?string $accept = null;

    protected ?int $maxSize = null;

    protected bool $multiple = false;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::FILE;
    }

    public function accept(string $mime): static
    {
        $this->accept = $mime;

        return $this;
    }

    public function maxSize(int $kb): static
    {
        $this->maxSize = $kb;
        $this->addRule("max:{$kb}");

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'accept' => $this->accept,
            'maxSize' => $this->maxSize,
            'multiple' => $this->multiple,
        ]);
    }
}
