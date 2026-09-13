<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

trait Makeable
{
    /**
     * Crea una nueva instancia de la clase de manera fluida.
     */
    public static function make(mixed ...$arguments): static
    {
        return new static(...$arguments);
    }
}
