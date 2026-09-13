<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Detail;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\ColumnFormatterContract;
use Warrior\SchemaBuilder\Enums\ColumnType;
use Warrior\SchemaBuilder\Table\Formatters\AvatarFormatter;
use Warrior\SchemaBuilder\Table\Formatters\BadgeFormatter;
use Warrior\SchemaBuilder\Table\Formatters\CurrencyFormatter;
use Warrior\SchemaBuilder\Table\Formatters\DateFormatter;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class DetailField implements Arrayable, JsonSerializable
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected string $name;

    protected ColumnType $type = ColumnType::TEXT;

    protected int $cols = 12;

    protected ?int $sm = null;

    protected ?int $md = null;

    protected ?int $lg = null;

    protected ?ColumnFormatterContract $formatter = null;

    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucwords(str_replace(['_', '.'], ' ', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ColumnType
    {
        return $this->type;
    }

    public function cols(int $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    public function sm(int $cols): static
    {
        $this->sm = $cols;

        return $this;
    }

    public function md(int $cols): static
    {
        $this->md = $cols;

        return $this;
    }

    public function lg(int $cols): static
    {
        $this->lg = $cols;

        return $this;
    }

    public function avatar(?string $avatarKey = null, ?string $subtitleKey = null): static
    {
        $this->type = ColumnType::AVATAR;
        $this->formatter = new AvatarFormatter(
            $avatarKey ?? $this->name,
            $this->name,
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
            'name' => $this->name,
            'label' => $this->getLabel(),
            'type' => $this->type->value,
            'cols' => $this->cols,
            'sm' => $this->sm,
            'md' => $this->md,
            'lg' => $this->lg,
            'formatOptions' => $this->formatter?->getOptions(),
            'permissions' => $this->permissions,
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
