<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Warrior\SchemaBuilder\Enums\Alignment;
use Warrior\SchemaBuilder\Enums\ColumnType;

/**
 * @extends Arrayable<string, mixed>
 */
interface ColumnContract extends Arrayable, JsonSerializable
{
    /**
     * Retorna la clave del campo de datos.
     */
    public function getKey(): string;

    /**
     * Retorna el título visible del encabezado.
     */
    public function getTitle(): string;

    /**
     * Retorna el tipo de columna para presentación.
     */
    public function getType(): ColumnType;

    /**
     * Determina si la columna es ordenable en el servidor.
     */
    public function isSortable(): bool;

    /**
     * Determina si la columna es visible por defecto.
     */
    public function isVisible(): bool;

    /**
     * Retorna la alineación horizontal de la celda.
     */
    public function getAlign(): Alignment;

    /**
     * Retorna el ancho sugerido de la columna (ej. '140px', '20%').
     */
    public function getWidth(): ?string;

    /**
     * Serializa la columna conforme al contrato SPEC-002.
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
