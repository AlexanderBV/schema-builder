<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Contracts;

interface ValidationExtractableContract
{
    /**
     * Extrae las reglas de validación para Laravel Validator.
     *
     * @param  bool  $isUpdate  Si es true, adapta las reglas para mutaciones parciales (PATCH).
     * @return array<string, array<int, mixed>|string>
     */
    public function toValidationRules(bool $isUpdate = false): array;
}
