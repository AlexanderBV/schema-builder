<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Concerns;

use BackedEnum;
use InvalidArgumentException;
use UnitEnum;

trait HasOptions
{
    /**
     * @var array<int, array{value: int|string, label: string}>
     */
    protected array $options = [];

    /**
     * Define las opciones clave-valor directamente.
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
     * Auto-extrae opciones desde un Enum de PHP 8.1+ (BackedEnum o UnitEnum).
     *
     * @param  class-string<UnitEnum>  $enumClass
     */
    public function optionsFromEnum(string $enumClass): static
    {
        if (! enum_exists($enumClass)) {
            throw new InvalidArgumentException("La clase {$enumClass} no es un Enum válido.");
        }

        $options = [];

        foreach ($enumClass::cases() as $case) {
            $value = $case instanceof BackedEnum ? $case->value : $case->name;
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
     * @return array<int, array{value: int|string, label: string}>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
