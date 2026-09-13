<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Table;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Warrior\SchemaBuilder\Concerns\Makeable;

/**
 * @phpstan-consistent-constructor
 *
 * @implements Arrayable<string, mixed>
 */
class SoftDeletesConfig implements Arrayable, JsonSerializable
{
    use Makeable;

    protected bool $enabled = false;

    protected string $queryParam = 'trashed';

    protected ?string $restoreEndpoint = null;

    protected ?string $forceDeleteEndpoint = null;

    public function enable(bool $enabled = true): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function queryParam(string $queryParam): static
    {
        $this->queryParam = $queryParam;

        return $this;
    }

    public function getQueryParam(): string
    {
        return $this->queryParam;
    }

    public function endpoints(?string $restore = null, ?string $forceDelete = null): static
    {
        $this->restoreEndpoint = $restore;
        $this->forceDeleteEndpoint = $forceDelete;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'queryParam' => $this->queryParam,
            'restoreEndpoint' => $this->restoreEndpoint,
            'forceDeleteEndpoint' => $this->forceDeleteEndpoint,
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
