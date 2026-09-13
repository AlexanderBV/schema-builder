<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\FieldContract;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class Filter implements Arrayable, JsonSerializable
{
    use Makeable;

    protected FieldContract $field;

    public function __construct(FieldContract $field)
    {
        $this->field = $field;
    }

    public static function fromField(FieldContract $field): static
    {
        return new static($field);
    }

    public function getField(): FieldContract
    {
        return $this->field;
    }

    public function getFieldName(): string
    {
        return $this->field->getName();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->field->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
