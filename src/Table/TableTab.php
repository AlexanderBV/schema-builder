<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class TableTab implements Arrayable, JsonSerializable
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected ?string $icon = null;

    protected ?int $badge = null;

    protected ?string $badgeColor = null;

    /**
     * @var array<string, mixed>
     */
    protected array $filter = [];

    public function __construct(string $id, ?string $label = null)
    {
        $this->id = $id;
        $this->title = $label ?? ucwords(str_replace(['_', '-'], ' ', $id));
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function badge(?int $badge, ?string $color = null): static
    {
        $this->badge = $badge;
        $this->badgeColor = $color;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    public function filter(array $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function hasFilter(): bool
    {
        return ! empty($this->filter);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'icon' => $this->icon,
            'badge' => $this->badge,
            'badgeColor' => $this->badgeColor,
            'filter' => (object) $this->filter,
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
