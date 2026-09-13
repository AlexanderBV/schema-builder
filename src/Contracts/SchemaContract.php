<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @extends Arrayable<string, mixed>
 */
interface SchemaContract extends Arrayable, JsonSerializable
{
    /**
     * Retorna el identificador único del esquema.
     */
    public function getId(): string;

    /**
     * Retorna el título descriptivo del esquema.
     */
    public function getTitle(): ?string;

    /**
     * Serializa el esquema a un array asociativo conforme al contrato SPEC-002.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Serializa para json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;
}
