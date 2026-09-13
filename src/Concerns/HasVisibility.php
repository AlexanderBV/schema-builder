<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

use Closure;

trait HasVisibility
{
    protected bool $visible = true;

    /**
     * @var array{field: string, is?: mixed, not?: mixed, in?: array<int, mixed>, operator?: string}|null
     */
    protected ?array $visibleWhen = null;

    public function visible(bool|Closure $condition = true): static
    {
        $this->visible = is_callable($condition) ? (bool) $condition() : $condition;

        return $this;
    }

    public function hidden(bool|Closure $condition = true): static
    {
        $this->visible = is_callable($condition) ? ! (bool) $condition() : ! $condition;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Aplica un callback sólo si la condición es verdadera.
     *
     * @param  callable($this, mixed): mixed  $callback
     * @param  (callable($this, mixed): mixed)|null  $default
     */
    public function when(mixed $condition, callable $callback, ?callable $default = null): static
    {
        $value = is_callable($condition) ? $condition($this) : $condition;

        if ($value) {
            $callback($this, $value);
        } elseif ($default) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Aplica un callback sólo si la condición es falsa.
     *
     * @param  callable($this, mixed): mixed  $callback
     * @param  (callable($this, mixed): mixed)|null  $default
     */
    public function unless(mixed $condition, callable $callback, ?callable $default = null): static
    {
        $value = is_callable($condition) ? $condition($this) : $condition;

        if (! $value) {
            $callback($this, $value);
        } elseif ($default) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Configura la visibilidad reactiva en el frontend evaluando el valor de otro campo.
     *
     * @param  string  $field  Nombre del campo observado.
     * @param  mixed  $value  Valor esperado.
     * @param  string  $operator  Operador de comparación ('===', '!==', 'in', etc.)
     */
    public function visibleWhen(string $field, mixed $value, string $operator = '==='): static
    {
        $this->visibleWhen = [
            'field' => $field,
            'is' => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * @return array{field: string, is?: mixed, not?: mixed, in?: array<int, mixed>, operator?: string}|null
     */
    public function getVisibleWhen(): ?array
    {
        return $this->visibleWhen;
    }
}
