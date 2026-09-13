<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

use BackedEnum;
use InvalidArgumentException;
use UnitEnum;

/**
 * Trait HasOptions
 *
 * Gestiona colecciones de opciones clave-valor para componentes de selección
 * (dropdowns, radios, selectores múltiples, chips), soportando arrays y Enums PHP 8.1+.
 */
trait HasOptions
{
    /**
     * Lista estandarizada de opciones con formato [{ value: ..., label: ... }].
     *
     * @var array<int, array{value: int|string, label: string}>
     */
    protected array $options = [];

    /**
     * Define las opciones clave-valor directamente desde un array.
     * Soporta arrays clave-valor simples ['admin' => 'Administrador']
     * y arrays estructurados con formato [['value' => 'admin', 'label' => 'Administrador']].
     *
     * @param  array<int, array{value: int|string, label: string}>|array<int|string, string>  $options
     */
    public function options(array $options): static
    {
        $formatted = [];

        foreach ($options as $key => $value) {
            if (is_array($value) && isset($value['value'], $value['label'])) {
                /** @var array{value: int|string, label: string} $value */
                $formatted[] = $value;
            } else {
                $formatted[] = [
                    'value' => $key,
                    'label' => (string) $value,
                ];
            }
        }

        $this->options = $formatted;

        return $this;
    }

    /**
     * Auto-extrae las opciones inspeccionando los casos de un Enum de PHP 8.1+ (BackedEnum o UnitEnum).
     * Si el Enum define un método `label()`, se utiliza como texto visible; de lo contrario, se formatea el nombre del case.
     *
     * @param  class-string<UnitEnum>  $enumClass  Clase del Enum a inspeccionar.
     *
     * @throws InvalidArgumentException Si la clase no es un Enum válido.
     */
    public function optionsFromEnum(string $enumClass): static
    {
        if (! enum_exists($enumClass)) {
            throw new InvalidArgumentException("La clase {$enumClass} no es un Enum válido.");
        }

        $options = [];

        // Itera sobre todos los casos declarados en el Enum
        foreach ($enumClass::cases() as $case) {
            $value = $case instanceof BackedEnum ? $case->value : $case->name;

            // Determina la etiqueta legible: método label() si existe, o nombre del case embellecido
            $label = method_exists($case, 'label')
                ? (string) $case->label()
                : ucwords(str_replace(['_', '-'], ' ', (string) $case->name));

            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Retorna el listado de opciones normalizadas para el frontend.
     *
     * @return array<int, array{value: int|string, label: string}>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
