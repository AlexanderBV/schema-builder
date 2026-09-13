<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Enums\PaginationPosition;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class PaginationConfig implements Arrayable, JsonSerializable
{
    use Makeable;

    protected int $defaultPerPage = 10;

    /**
     * @var array<int, int>
     */
    protected array $perPageOptions = [5, 10, 25, 50, 100];

    protected ?string $defaultSortBy = 'created_at';

    protected string $defaultSortOrder = 'desc';

    protected PaginationPosition $position = PaginationPosition::BOTH;

    public function defaultPerPage(int $perPage): static
    {
        $this->defaultPerPage = $perPage;

        return $this;
    }

    /**
     * @param  array<int, int>  $options
     */
    public function perPageOptions(array $options): static
    {
        $this->perPageOptions = array_values($options);

        return $this;
    }

    public function defaultSort(string $sortBy, string $order = 'desc'): static
    {
        $this->defaultSortBy = $sortBy;
        $this->defaultSortOrder = strtolower($order);

        return $this;
    }

    public function position(PaginationPosition|string $position): static
    {
        $this->position = is_string($position) ? PaginationPosition::from($position) : $position;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'defaultPerPage' => $this->defaultPerPage,
            'perPageOptions' => $this->perPageOptions,
            'defaultSortBy' => $this->defaultSortBy,
            'defaultSortOrder' => $this->defaultSortOrder,
            'position' => $this->position->value,
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
