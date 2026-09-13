<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table\Formatters;

use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\ColumnFormatterContract;
use Warrior\SchemaBuilder\Enums\ColumnType;

/**
 * @phpstan-consistent-constructor
 */
class CurrencyFormatter implements ColumnFormatterContract
{
    use Makeable;

    protected string $currency;

    protected string $locale;

    protected int $decimals;

    public function __construct(string $currency = 'USD', string $locale = 'en-US', int $decimals = 2)
    {
        $this->currency = $currency;
        $this->locale = $locale;
        $this->decimals = $decimals;
    }

    public function getType(): ColumnType
    {
        return ColumnType::CURRENCY;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            'currency' => $this->currency,
            'locale' => $this->locale,
            'decimals' => $this->decimals,
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
