<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

use Closure;

/**
 * Trait HasVisibility
 *
 * Controla tanto la visibilidad en servidor (renderizado condicional via when/unless)
 * como la visibilidad reactiva en el cliente frontend (visibleWhen).
 */
trait HasVisibility
{
    /**
     * Determina si el componente se incluye y visualiza en la salida del esquema.
     */
    protected bool $visible = true;

    /**
     * Configuración de visibilidad reactiva evaluada por el cliente frontend (Vue/React).
     * Formato: ['field' => 'tipo_documento', 'is' => 'ruc', 'operator' => '===']
     *
     * @var array{field: string, is?: mixed, not?: mixed, in?: array<int, mixed>, operator?: string}|null
     */
    protected ?array $visibleWhen = null;

    /**
     * Define si el elemento es visible. Admite un booleano o un Closure evaluado al vuelo.
     *
     * @param  bool|Closure(): bool  $condition
     */
    public function visible(bool|Closure $condition = true): static
    {
        $this->visible = is_callable($condition) ? (bool) $condition() : $condition;

        return $this;
    }

    /**
     * Oculta el elemento en base a una condición o Closure.
     *
     * @param  bool|Closure(): bool  $condition
     */
    public function hidden(bool|Closure $condition = true): static
    {
        $this->visible = is_callable($condition) ? ! (bool) $condition() : ! $condition;

        return $this;
    }

    /**
     * Comprueba si el elemento se encuentra visible en el backend.
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Ejecuta el callback suministrado únicamente cuando la condición dada se evalúe como verdadera.
     * Si se pasa un default callback y la condición es falsa, ejecuta el default.
     *
     * @param  mixed  $condition  Condición booleana o Closure.
     * @param  callable($this, mixed): mixed  $callback  Callback a invocar si es verdadero.
     * @param  (callable($this, mixed): mixed)|null  $default  Callback a invocar si es falso.
     */
    public function when(mixed $condition, callable $callback, ?callable $default = null): static
    {
        // Resuelve la condición si es un closure
        $value = is_callable($condition) ? $condition($this) : $condition;

        if ($value) {
            $callback($this, $value);
        } elseif ($default) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Ejecuta el callback suministrado únicamente cuando la condición dada se evalúe como falsa.
     *
     * @param  mixed  $condition  Condición booleana o Closure.
     * @param  callable($this, mixed): mixed  $callback  Callback a invocar si es falso.
     * @param  (callable($this, mixed): mixed)|null  $default  Callback a invocar si es verdadero.
     */
    public function unless(mixed $condition, callable $callback, ?callable $default = null): static
    {
        // Resuelve la condición si es un closure
        $value = is_callable($condition) ? $condition($this) : $condition;

        if (! $value) {
            $callback($this, $value);
        } elseif ($default) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Configura la regla de visibilidad condicional evaluada en el frontend.
     * Por ejemplo, mostrar un campo "Razón Social" únicamente cuando "tipo_documento" sea "RUC".
     *
     * @param  string  $field  Nombre del campo observado en el modelo del formulario.
     * @param  mixed  $value  Valor esperado para activar la visibilidad.
     * @param  string  $operator  Operador de comparación JS ('===', '!==', 'in', etc.).
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
     * Retorna la regla de visibilidad frontend o null si no aplica.
     *
     * @return array{field: string, is?: mixed, not?: mixed, in?: array<int, mixed>, operator?: string}|null
     */
    public function getVisibleWhen(): ?array
    {
        return $this->visibleWhen;
    }
}
