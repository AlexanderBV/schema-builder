<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form\Fields;

use Warrior\SchemaBuilder\Concerns\HasOptions;
use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;

class SelectField extends Field
{
    use HasOptions;

    protected bool $multiple = false;

    protected bool $chips = false;

    protected bool $autocomplete = false;

    /**
     * Configuración de fuente de datos remota (API / DB) para selects dinámicos.
     *
     * @var array{type: string, endpoint: string, method: string, dependOnField: ?string, paramKey: ?string, queryParams: array<string, mixed>, dataKey: ?string}|null
     */
    protected ?array $optionsSource = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = FieldType::SELECT;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function chips(bool $chips = true): static
    {
        $this->chips = $chips;

        return $this;
    }

    public function autocomplete(bool $autocomplete = true): static
    {
        $this->autocomplete = $autocomplete;

        return $this;
    }

    /**
     * Configura el endpoint remoto desde el cual el frontend cargará las opciones dinámicamente.
     *
     * @param  string  $endpoint  URL relativa o absoluta del catálogo (ej. '/api/v1/departments').
     * @param  string|null  $dependOnField  Campo padre del cual depende (para selects en cascada).
     * @param  string|null  $paramKey  Parámetro query enviado al endpoint con el valor del padre.
     * @param  array<string, mixed>  $queryParams  Parámetros fijos adicionales a enviar en la consulta.
     * @param  string  $method  Método HTTP ('GET' o 'POST').
     * @param  string|null  $dataKey  Propiedad en la respuesta JSON que contiene la lista (ej. 'data').
     */
    public function endpoint(
        string $endpoint,
        ?string $dependOnField = null,
        ?string $paramKey = null,
        array $queryParams = [],
        string $method = 'GET',
        ?string $dataKey = null
    ): static {
        $this->optionsSource = [
            'type' => 'api',
            'endpoint' => $endpoint,
            'method' => $method,
            'dependOnField' => $dependOnField,
            'paramKey' => $paramKey ?? $dependOnField,
            'queryParams' => $queryParams,
            'dataKey' => $dataKey,
        ];

        return $this;
    }

    /**
     * Alias intuitivo de endpoint().
     */
    public function fromEndpoint(string $endpoint, ?string $dependOnField = null, ?string $paramKey = null): static
    {
        return $this->endpoint($endpoint, $dependOnField, $paramKey);
    }

    /**
     * Define una dependencia con otro campo del formulario (Select en Cascada).
     * Ejemplo: Ciudad depende de Departamento.
     *
     * @param  string  $parentField  Nombre del campo padre en el formulario.
     * @param  string|null  $paramKey  Nombre del parámetro enviado en la petición HTTP al endpoint.
     */
    public function dependsOn(string $parentField, ?string $paramKey = null): static
    {
        if ($this->optionsSource !== null) {
            $this->optionsSource['dependOnField'] = $parentField;
            $this->optionsSource['paramKey'] = $paramKey ?? $parentField;
        } else {
            $this->optionsSource = [
                'type' => 'api',
                'endpoint' => '',
                'method' => 'GET',
                'dependOnField' => $parentField,
                'paramKey' => $paramKey ?? $parentField,
                'queryParams' => [],
                'dataKey' => null,
            ];
        }

        return $this;
    }

    /**
     * Asigna parámetros adicionales de consulta (queryParams) a la petición del endpoint.
     *
     * @param  array<string, mixed>  $params
     */
    public function queryParams(array $params): static
    {
        if ($this->optionsSource !== null) {
            $this->optionsSource['queryParams'] = array_merge($this->optionsSource['queryParams'], $params);
        }

        return $this;
    }

    /**
     * Retorna la configuración de la fuente remota de opciones o null si son estáticas.
     *
     * @return array{type: string, endpoint: string, method: string, dependOnField: ?string, paramKey: ?string, queryParams: array<string, mixed>, dataKey: ?string}|null
     */
    public function getOptionsSource(): ?array
    {
        return $this->optionsSource;
    }

    /**
     * Serializa el campo a especificación JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->getOptions(),
            'optionsSource' => $this->optionsSource,
            'multiple' => $this->multiple,
            'chips' => $this->chips,
            'autocomplete' => $this->autocomplete,
        ]);
    }
}
