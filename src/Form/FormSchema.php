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

/** @phpstan-consistent-constructor */
class FormSchema implements FieldContainerContract, SchemaContract
{
    use HasIdAndTitle;
    use HasPermissions;
    use HasVisibility;
    use Macroable;
    use Makeable;

    protected ?string $endpoint = null;

    protected string $httpMethod = 'POST';

    protected string $submitLabel = 'Guardar Registro';

    protected string $cancelLabel = 'Cancelar';

    /**
     * @var array<int, FieldContract>
     */
    protected array $inputs = [];

    /**
     * @var array<int, FormTab>
     */
    protected array $tabs = [];

    /**
     * @var array<int, FormSection>
     */
    protected array $sections = [];

    public function __construct(?string $id = null, ?string $title = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        if ($title !== null) {
            $this->title = $title;
        }
    }

    public function endpoint(string $endpoint, string $httpMethod = 'POST'): static
    {
        $this->endpoint = $endpoint;
        $this->httpMethod = strtoupper($httpMethod);

        return $this;
    }

    public function httpMethod(string $method): static
    {
        $this->httpMethod = strtoupper($method);

        return $this;
    }

    public function submitLabel(string $label): static
    {
        $this->submitLabel = $label;

        return $this;
    }

    public function cancelLabel(string $label): static
    {
        $this->cancelLabel = $label;

        return $this;
    }

    /**
     * Configura un formulario plano con una lista de campos.
     *
     * @param  array<int, FieldContract>  $fields
     */
    public function fields(array $fields): static
    {
        $this->inputs = array_values($fields);

        return $this;
    }

    public function addField(FieldContract $field): static
    {
        $this->inputs[] = $field;

        return $this;
    }

    /**
     * Configura el formulario dividido en pestañas.
     *
     * @param  array<int, FormTab>  $tabs
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = array_values($tabs);

        return $this;
    }

    public function addTab(FormTab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    /**
     * Configura secciones agrupadas.
     *
     * @param  array<int, FormSection>  $sections
     */
    public function sections(array $sections): static
    {
        $this->sections = array_values($sections);

        return $this;
    }

    public function addSection(FormSection $section): static
    {
        $this->sections[] = $section;

        return $this;
    }

    public function hasTabs(): bool
    {
        return ! empty($this->tabs);
    }

    public function hasSections(): bool
    {
        return ! empty($this->sections);
    }

    /**
     * Retorna todos los campos contenidos recursivamente.
     *
     * @return array<int, FieldContract>
     */
    public function getFields(): array
    {
        if ($this->hasTabs()) {
            $fields = [];
            foreach ($this->tabs as $tab) {
                $fields = array_merge($fields, $tab->getFields());
            }

            return $fields;
        }

        if ($this->hasSections()) {
            $fields = [];
            foreach ($this->sections as $section) {
                $fields = array_merge($fields, $section->getFields());
            }

            return $fields;
        }

        return $this->inputs;
    }

    /**
     * Compila las reglas de validación para Laravel Validator.
     *
     * @param  bool  $isUpdate  Si es true, activa modo PATCH (dirty tracking: required -> sometimes, required).
     * @return array<string, array<int, mixed>>
     */
    public function toValidationRules(bool $isUpdate = false): array
    {
        $rules = [];

        foreach ($this->getFields() as $field) {
            $fieldRules = $field->getValidationRules($isUpdate);
            if (! empty($fieldRules)) {
                $rules[$field->getName()] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Serializa el formulario al contrato JSON Schema (SPEC-002).
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
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
