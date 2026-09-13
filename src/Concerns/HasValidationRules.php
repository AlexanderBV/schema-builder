<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

/**
 * Trait HasValidationRules
 *
 * Proporciona métodos fluidos y tipados para construir reglas de validación nativas de Laravel.
 * Incorpora adaptación transparente para mutaciones parciales de tipo PATCH (dirty tracking),
 * convirtiendo reglas `required` en `sometimes|required`.
 */
trait HasValidationRules
{
    /**
     * Reglas de validación registradas en el campo.
     *
     * @var array<int, mixed>
     */
    protected array $rules = [];

    /**
     * Asigna reglas de validación arbitrarias (array o string delimitado por pipes '|').
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

    /**
     * Marca el campo como obligatorio ('required').
     *
     * @param  bool  $condition  Condición booleana para aplicar la regla.
     */
    public function required(bool $condition = true): static
    {
        return $condition ? $this->addRule('required') : $this;
    }

    /**
     * Marca el campo como opcional o nullable ('nullable').
     *
     * @param  bool  $condition  Condición booleana para aplicar la regla.
     */
    public function nullable(bool $condition = true): static
    {
        return $condition ? $this->addRule('nullable') : $this;
    }

    /**
     * Exige que el valor sea una cadena de texto ('string').
     */
    public function string(): static
    {
        return $this->addRule('string');
    }

    /**
     * Exige que el valor sea un número entero ('integer').
     */
    public function integer(): static
    {
        return $this->addRule('integer');
    }

    /**
     * Exige que el valor sea numérico ('numeric').
     */
    public function numeric(): static
    {
        return $this->addRule('numeric');
    }

    /**
     * Exige que el valor sea un booleano ('boolean').
     */
    public function boolean(): static
    {
        return $this->addRule('boolean');
    }

    /**
     * Exige que el valor tenga formato de correo electrónico ('email').
     * Nota: En EmailField esta regla se inyecta automáticamente por defecto.
     */
    public function asEmail(): static
    {
        return $this->addRule('email');
    }

    /**
     * Define el valor o longitud mínima permitida ('min:N').
     */
    public function min(int|float $min): static
    {
        return $this->addRule("min:{$min}");
    }

    /**
     * Define el valor o longitud máxima permitida ('max:N').
     */
    public function max(int|float $max): static
    {
        return $this->addRule("max:{$max}");
    }

    /**
     * Exige confirmación mediante campo '_confirmation' correspondiente ('confirmed').
     */
    public function confirmed(): static
    {
        return $this->addRule('confirmed');
    }

    /**
     * Exige coincidencia con una expresión regular dada ('regex:pattern').
     */
    public function regex(string $pattern): static
    {
        return $this->addRule("regex:{$pattern}");
    }

    /**
     * Aplica la regla de unicidad en base de datos ('unique:table,column,except,idColumn').
     *
     * @param  string  $table  Nombre de la tabla en base de datos.
     * @param  string  $column  Columna a verificar (default: misma clave del campo).
     * @param  string|null  $except  ID a ignorar (útil en operaciones de actualización).
     * @param  string  $idColumn  Nombre de la columna clave primaria (default: 'id').
     */
    public function unique(string $table, string $column = 'NULL', ?string $except = null, string $idColumn = 'id'): static
    {
        $rule = "unique:{$table},{$column}";

        if ($except !== null) {
            $rule .= ",{$except},{$idColumn}";
        }

        return $this->addRule($rule);
    }

    /**
     * Agrega una regla al array interno evitando duplicados idénticos si es un string.
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
     * Retorna las reglas compiladas listas para `Validator::make()`.
     *
     * Comportamiento en Modo Actualización Parcial ($isUpdate = true):
     * Cuando se edita un recurso mediante PATCH con dirty tracking, el cliente únicamente
     * envía los campos que el usuario modificó. Si una regla es 'required', el validador
     * fallaría si el campo no se envía. Esta lógica detecta la regla 'required' y la convierte
     * en ['sometimes', 'required'], validando el campo sólo si viene presente en el payload.
     *
     * @param  bool  $isUpdate  Si es true, activa adaptación inteligente para PATCH.
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

        // Si tenía required, inyecta 'sometimes' antes de 'required'
        if ($hasRequired) {
            array_unshift($compiled, 'sometimes', 'required');
        }

        return $compiled;
    }
}
