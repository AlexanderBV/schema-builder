<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table\Formatters;

use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\ColumnFormatterContract;
use Warrior\SchemaBuilder\Enums\ColumnType;

/**
 * @phpstan-consistent-constructor
 */
class BadgeFormatter implements ColumnFormatterContract
{
    use Makeable;

    /**
     * @var array<string, string>
     */
    protected array $colorMap;

    /**
     * @var array<string, string>
     */
    protected array $labelMap;

    /**
     * @param  array<string, string>  $colorMap
     * @param  array<string, string>  $labelMap
     */
    public function __construct(array $colorMap = [], array $labelMap = [])
    {
        $this->colorMap = $colorMap;
        $this->labelMap = $labelMap;
    }

    public function getType(): ColumnType
    {
        return ColumnType::BADGE;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            'colorMap' => $this->colorMap,
            'labelMap' => $this->labelMap,
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
