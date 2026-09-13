<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Contracts;

interface FieldContainerContract extends ValidationExtractableContract
{
    /**
     * Agrega un campo al contenedor.
     */
    public function addField(FieldContract $field): static;

    /**
     * Retorna todos los campos contenidos (plano o resuelto).
     *
     * @return array<int, FieldContract>
     */
    public function getFields(): array;
}
