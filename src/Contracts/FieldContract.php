<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Warrior\SchemaBuilder\Enums\FieldType;

/**
 * @extends Arrayable<string, mixed>
 */
interface FieldContract extends Arrayable, JsonSerializable, ValidationExtractableContract
{
    /**
     * Retorna el nombre del campo (key en payload y DB).
     */
    public function getName(): string;

    /**
     * Retorna el tipo de campo.
     */
    public function getType(): FieldType;

    /**
     * Retorna la etiqueta visible del campo.
     */
    public function getLabel(): string;

    /**
     * Retorna las reglas de validación asignadas al campo.
     *
     * @return array<int, mixed>
     */
    public function getValidationRules(bool $isUpdate = false): array;

    /**
     * Serializa el campo a array conforme al contrato SPEC-002.
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
