<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

trait HasValidationRules
{
    /**
     * @var array<int, mixed>
     */
    protected array $rules = [];

    /**
     * Asigna reglas de validación (array o string delimitado por pipes '|').
     *
     * @param  array<int, mixed>|string  $rules
     */
    public function rules(array|string $rules): static
    {
        $newRules = is_string($rules) ? explode('|', $rules) : $rules;

        foreach ($newRules as $rule) {
            $this->rules[] = $rule;
        }

        return $this;
    }

    public function required(bool $condition = true): static
    {
        return $condition ? $this->addRule('required') : $this;
    }

    public function nullable(bool $condition = true): static
    {
        return $condition ? $this->addRule('nullable') : $this;
    }

    public function string(): static
    {
        return $this->addRule('string');
    }

    public function integer(): static
    {
        return $this->addRule('integer');
    }

    public function numeric(): static
    {
        return $this->addRule('numeric');
    }

    public function boolean(): static
    {
        return $this->addRule('boolean');
    }

    public function asEmail(): static
    {
        return $this->addRule('email');
    }

    public function min(int|float $min): static
    {
        return $this->addRule("min:{$min}");
    }

    public function max(int|float $max): static
    {
        return $this->addRule("max:{$max}");
    }

    public function confirmed(): static
    {
        return $this->addRule('confirmed');
    }

    public function regex(string $pattern): static
    {
        return $this->addRule("regex:{$pattern}");
    }

    public function unique(string $table, string $column = 'NULL', ?string $except = null, string $idColumn = 'id'): static
    {
        $rule = "unique:{$table},{$column}";

        if ($except !== null) {
            $rule .= ",{$except},{$idColumn}";
        }

        return $this->addRule($rule);
    }

    /**
     * Agrega una regla evitando duplicados directos si ya existe como string idéntico.
     */
    protected function addRule(mixed $rule): static
    {
        if (is_string($rule) && in_array($rule, $this->rules, true)) {
            return $this;
        }

        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Retorna las reglas compiladas para el validador.
     * En modo actualización ($isUpdate = true), reemplaza 'required' por 'sometimes|required'
     * para soportar mutaciones parciales de tipo PATCH (dirty tracking).
     *
     * @return array<int, mixed>
     */
    public function getValidationRules(bool $isUpdate = false): array
    {
        if (! $isUpdate) {
            return $this->rules;
        }

        $compiled = [];
        $hasRequired = false;

        foreach ($this->rules as $rule) {
            if ($rule === 'required') {
                $hasRequired = true;

                continue;
            }
            $compiled[] = $rule;
        }

        if ($hasRequired) {
            array_unshift($compiled, 'sometimes', 'required');
        }

        return $compiled;
    }
}
