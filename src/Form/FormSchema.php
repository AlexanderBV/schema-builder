<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Form;

use Illuminate\Support\Traits\Macroable;
use Warrior\SchemaBuilder\Concerns\HasIdAndTitle;
use Warrior\SchemaBuilder\Concerns\HasPermissions;
use Warrior\SchemaBuilder\Concerns\HasVisibility;
use Warrior\SchemaBuilder\Concerns\Makeable;
use Warrior\SchemaBuilder\Contracts\FieldContainerContract;
use Warrior\SchemaBuilder\Contracts\FieldContract;
use Warrior\SchemaBuilder\Contracts\SchemaContract;

/**
 * Class FormSchema
 *
 * Builder principal de formularios (Composite Root).
 * Permite definir tanto formularios planos (lista de campos) como estructuras
 * jerárquicas complejas organizadas en pestañas (FormTab) o secciones (FormSection).
 * Provee compilación recursiva de reglas de validación (toValidationRules)
 * compatibles con Laravel Validator.
 *
 * @phpstan-consistent-constructor
 */
class FormSchema implements FieldContainerContract, SchemaContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    /**
     * Endpoint API al que se enviará la petición (ej. '/api/v1/users').
     */
    protected ?string $endpoint = null;

    /**
     * Método HTTP para el envío del formulario (POST, PUT, PATCH).
     */
    protected string $httpMethod = 'POST';

    /**
     * Texto del botón principal de envío / guardado.
     */
    protected string $submitLabel = 'Guardar Registro';

    /**
     * Texto del botón de cancelar / cerrar modal.
     */
    protected string $cancelLabel = 'Cancelar';

    /**
     * Lista plana de campos para formularios sin pestañas ni secciones.
     *
     * @var array<int, FieldContract>
     */
    protected array $inputs = [];

    /**
     * Pestañas del formulario (patrón Composite).
     *
     * @var array<int, FormTab>
     */
    protected array $tabs = [];

    /**
     * Secciones agrupadas dentro del formulario.
     *
     * @var array<int, FormSection>
     */
    protected array $sections = [];

    /**
     * Reglas de validación personalizadas o adicionales registradas directamente en el esquema.
     *
     * @var array<string, array<int, mixed>>
     */
    protected array $customValidationRules = [];

    /**
     * Constructor del esquema de formulario.
     *
     * @param  string|null  $id  Identificador único del formulario.
     * @param  string|null  $title  Título del encabezado o modal.
     */
    public function __construct(?string $id = null, ?string $title = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        if ($title !== null) {
            $this->title = $title;
        }
    }

    /**
     * Define la URL del endpoint y opcionalmente el método HTTP.
     *
     * @param  string  $endpoint  URL de destino.
     * @param  string  $httpMethod  Método HTTP ('POST', 'PUT', 'PATCH').
     */
    public function endpoint(string $endpoint, string $httpMethod = 'POST'): static
    {
        $this->endpoint = $endpoint;
        $this->httpMethod = strtoupper($httpMethod);

        return $this;
    }

    /**
     * Define explícitamente el método HTTP del formulario.
     */
    public function httpMethod(string $method): static
    {
        $this->httpMethod = strtoupper($method);

        return $this;
    }

    /**
     * Asigna la etiqueta del botón de submit.
     */
    public function submitLabel(string $label): static
    {
        $this->submitLabel = $label;

        return $this;
    }

    /**
     * Asigna la etiqueta del botón de cancelar.
     */
    public function cancelLabel(string $label): static
    {
        $this->cancelLabel = $label;

        return $this;
    }

    /**
     * Configura el formulario como plano mediante una lista de campos.
     *
     * @param  array<int, FieldContract>  $fields
     */
    public function fields(array $fields): static
    {
        $this->inputs = array_values($fields);

        return $this;
    }

    /**
     * Agrega un campo individual a la lista de inputs.
     */
    public function addField(FieldContract $field): static
    {
        $this->inputs[] = $field;

        return $this;
    }

    /**
     * Configura el formulario estructurado en múltiples pestañas (tabs).
     *
     * @param  array<int, FormTab>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = array_values($tabs);

        return $this;
    }

    /**
     * Agrega una pestaña al formulario.
     */
    public function addTab(FormTab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    /**
     * Configura el formulario organizado en secciones agrupadas.
     *
     * @param  array<int, FormSection>  $sections
     */
    public function sections(array $sections): static
    {
        $this->sections = array_values($sections);

        return $this;
    }

    /**
     * Agrega una sección agrupada al formulario.
     */
    public function addSection(FormSection $section): static
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Comprueba si el formulario cuenta con pestañas.
     */
    public function hasTabs(): bool
    {
        return ! empty($this->tabs);
    }

    /**
     * Comprueba si el formulario cuenta con secciones.
     */
    public function hasSections(): bool
    {
        return ! empty($this->sections);
    }

    /**
     * Retorna todos los campos contenidos recursivamente en el formulario,
     * resolviendo tanto pestañas como secciones o campos planos.
     *
     * @return array<int, FieldContract>
     */
    public function getFields(): array
    {
        // 1. Si contiene pestañas, recopila los campos de cada una de ellas
        if ($this->hasTabs()) {
            $fields = [];
            foreach ($this->tabs as $tab) {
                $fields = array_merge($fields, $tab->getFields());
            }

            return $fields;
        }

        // 2. Si contiene secciones agrupadas, recopila los campos de cada sección
        if ($this->hasSections()) {
            $fields = [];
            foreach ($this->sections as $section) {
                $fields = array_merge($fields, $section->getFields());
            }

            return $fields;
        }

        // 3. De lo contrario, retorna la lista plana de inputs
        return $this->inputs;
    }

    /**
     * Compila recursivamente todas las reglas de validación de los campos contenidos.
     *
     * @param  bool  $isUpdate  Si es true, activa modo PATCH con adaptación dirty tracking.
     * @return array<string, array<int, mixed>>
     */
    /**
     * Agrega o sobreescribe una regla de validación para un campo específico.
     *
     * @param  string  $field  Nombre del campo en el formulario.
     * @param  array<int, mixed>|string  $rules  Reglas de validación (array o string delimitado por pipes '|').
     */
    public function addValidationRule(string $field, array|string $rules): static
    {
        $newRules = is_string($rules) ? explode('|', $rules) : $rules;
        $this->customValidationRules[$field] = array_merge(
            $this->customValidationRules[$field] ?? [],
            $newRules
        );

        return $this;
    }

    /**
     * Fusiona múltiples reglas de validación personalizadas al esquema.
     *
     * @param  array<string, array<int, mixed>|string>  $rules
     */
    public function mergeValidationRules(array $rules): static
    {
        foreach ($rules as $field => $fieldRules) {
            $this->addValidationRule($field, $fieldRules);
        }

        return $this;
    }

    /**
     * Compila recursivamente todas las reglas de validación de los campos contenidos
     * combinándolas con las reglas personalizadas inyectadas.
     *
     * @param  bool  $isUpdate  Si es true, activa modo PATCH con adaptación dirty tracking.
     * @return array<string, array<int, mixed>>
     */
    public function toValidationRules(bool $isUpdate = false): array
    {
        $rules = [];

        // Recorre todos los campos resueltos de forma transparente
        foreach ($this->getFields() as $field) {
            $fieldRules = $field->getValidationRules($isUpdate);
            if (! empty($fieldRules)) {
                $rules[$field->getName()] = $fieldRules;
            }
        }

        // Fusiona las reglas personalizadas inyectadas
        foreach ($this->customValidationRules as $customField => $customRules) {
            $rules[$customField] = array_merge($rules[$customField] ?? [], $customRules);
        }

        return $rules;
    }

    /**
     * Serializa el formulario al formato JSON Schema (SPEC-002).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'endpoint' => $this->endpoint,
            'httpMethod' => $this->httpMethod,
            'submitLabel' => $this->submitLabel,
            'cancelLabel' => $this->cancelLabel,
        ];

        // Serializa tabs, secciones o inputs asegurando exclusividad mutua según el contrato
        if ($this->hasTabs()) {
            $data['tabs'] = array_map(fn (FormTab $tab) => $tab->toArray(), $this->tabs);
            $data['inputs'] = null;
        } elseif ($this->hasSections()) {
            $data['sections'] = array_map(fn (FormSection $section) => $section->toArray(), $this->sections);
            $data['tabs'] = null;
            $data['inputs'] = null;
        } else {
            $data['tabs'] = null;
            $data['inputs'] = array_map(fn (FieldContract $field) => $field->toArray(), $this->inputs);
        }

        return $data;
    }

    /**
     * Serializa para json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
