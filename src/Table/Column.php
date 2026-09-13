<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table;

use Illuminate\Support\Traits\Macroable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\ColumnContract;
use Warrior\SchemaBuilder\Contracts\ColumnFormatterContract;
use Warrior\SchemaBuilder\Enums\Alignment;
use Warrior\SchemaBuilder\Enums\ColumnType;
use Warrior\SchemaBuilder\Table\Formatters\AvatarFormatter;
use Warrior\SchemaBuilder\Table\Formatters\BadgeFormatter;
use Warrior\SchemaBuilder\Table\Formatters\CurrencyFormatter;
use Warrior\SchemaBuilder\Table\Formatters\DateFormatter;

/**
 * @phpstan-consistent-constructor
 */
class Column implements ColumnContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected string $key;

    protected ColumnType $type = ColumnType::TEXT;

    protected bool $sortable = false;

    protected Alignment $align = Alignment::START;

    protected ?string $width = null;

    protected ?ColumnFormatterContract $formatter = null;

    public function __construct(string $key, ?string $title = null)
    {
        $this->key = $key;
        $this->title = $title ?? ucwords(str_replace(['_', '.'], ' ', $key));
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTitle(): string
    {
        return $this->title ?? ucwords(str_replace(['_', '.'], ' ', $this->key));
    }

    public function getType(): ColumnType
    {
        return $this->type;
    }

    public function type(ColumnType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function getAlign(): Alignment
    {
        return $this->align;
    }

    public function align(Alignment|string $align): static
    {
        $this->align = is_string($align) ? Alignment::from($align) : $align;

        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function formatter(ColumnFormatterContract $formatter): static
    {
        $this->formatter = $formatter;
        $this->type = $formatter->getType();

        return $this;
    }

    public function avatar(?string $avatarKey = null, ?string $titleKey = null, ?string $subtitleKey = null): static
    {
        $this->type = ColumnType::AVATAR;
        $this->formatter = new AvatarFormatter(
            $avatarKey ?? $this->key,
            $titleKey ?? $this->key,
            $subtitleKey
        );

        return $this;
    }

    /**
     * @param  array<string, string>  $colorMap
     * @param  array<string, string>  $labelMap
     */
    public function badge(array $colorMap = [], array $labelMap = []): static
    {
        $this->type = ColumnType::BADGE;
        $this->formatter = new BadgeFormatter($colorMap, $labelMap);

        return $this;
    }

    public function currency(string $currency = 'USD', string $locale = 'en-US', int $decimals = 2): static
    {
        $this->type = ColumnType::CURRENCY;
        $this->align = Alignment::END;
        $this->formatter = new CurrencyFormatter($currency, $locale, $decimals);

        return $this;
    }

    public function date(string $format = 'DD/MM/YYYY'): static
    {
        $this->type = ColumnType::DATE;
        $this->formatter = new DateFormatter($format, ColumnType::DATE);

        return $this;
    }

    public function datetime(string $format = 'DD/MM/YYYY HH:mm'): static
    {
        $this->type = ColumnType::DATETIME;
        $this->formatter = new DateFormatter($format, ColumnType::DATETIME);

        return $this;
    }

    public function boolean(): static
    {
        $this->type = ColumnType::BOOLEAN;
        $this->align = Alignment::CENTER;

        return $this;
    }

    public function link(): static
    {
        $this->type = ColumnType::LINK;

        return $this;
    }

    public function json(): static
    {
        $this->type = ColumnType::JSON;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->getTitle(),
            'type' => $this->type->value,
            'sortable' => $this->sortable,
            'visible' => $this->isVisible(),
            'align' => $this->align->value,
            'width' => $this->width,
            'formatOptions' => $this->formatter?->getOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
