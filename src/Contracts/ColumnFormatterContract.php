<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Warrior\SchemaBuilder\Enums\ColumnType;

/**
 * @extends Arrayable<string, mixed>
 */
interface ColumnFormatterContract extends Arrayable, JsonSerializable
{
    /**
     * Retorna el tipo de columna asociado.
     */
    public function getType(): ColumnType;

    /**
     * Retorna las opciones de formateo para el frontend.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Serializa las opciones a un array.
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
