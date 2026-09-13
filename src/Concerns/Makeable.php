<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

/**
 * Trait Makeable
 *
 * Implementa el patrón Factory Method estático (::make) para instanciación fluida.
 */
trait Makeable
{
    /**
     * Crea una nueva instancia de la clase de manera fluida sin requerir el operador `new`.
     *
     * @param  mixed  ...$arguments  Argumentos pasados al constructor de la clase.
     */
    public static function make(mixed ...$arguments): static
    {
        return new static(...$arguments);
    }
}
