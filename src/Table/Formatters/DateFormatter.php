<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table\Formatters;

use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\ColumnFormatterContract;
use Warrior\SchemaBuilder\Enums\ColumnType;

/**
 * @phpstan-consistent-constructor
 */
class DateFormatter implements ColumnFormatterContract
{
    use Makeable;

    protected string $format;

    protected ColumnType $columnType;

    public function __construct(string $format = 'DD/MM/YYYY', ColumnType $columnType = ColumnType::DATE)
    {
        $this->format = $format;
        $this->columnType = $columnType;
    }

    public function getType(): ColumnType
    {
        return $this->columnType;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            'format' => $this->format,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->getOptions();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
