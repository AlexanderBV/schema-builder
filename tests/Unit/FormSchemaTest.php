<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Warrior\SchemaBuilder\Enums\FieldType;
use Warrior\SchemaBuilder\Form\Field;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Form\FormSection;
use Warrior\SchemaBuilder\Form\FormTab;

class FormSchemaTest extends TestCase
{
    /**
     * Escenario: se construye un formulario plano con campos de texto, email, número, selector y archivo.
     * Expectativa: el array serializado coincide rigurosamente con el contrato JSON Schema SPEC-002 (tabs === null, inputs poblado).
     */
    #[Test]
    public function it_serializes_flat_form_schema_matching_json_spec(): void
    {
        // given
        $form = FormSchema::make('user-form', 'Formulario de Usuario')
            ->description('Complete los datos solicitados')
            ->endpoint('/api/v1/users', 'POST')
            ->submitLabel('Guardar')
            ->cancelLabel('Cancelar')
            ->fields([
                Field::text('name', 'Nombre')->required()->string()->maxLength(100),
                Field::email('email', 'Correo')->required(),
                Field::number('age', 'Edad')->nullable()->min(18)->max(99),
                Field::select('role', 'Rol')->options([
                    'admin' => 'Administrador',
                    'editor' => 'Editor',
                ])->required(),
                Field::file('avatar', 'Foto')->accept('image/*')->maxSize(2048),
            ]);

        // when
        $array = $form->toArray();

        // then
        $this->assertSame('user-form', $array['id']);
        $this->assertSame('Formulario de Usuario', $array['title']);
        $this->assertSame('/api/v1/users', $array['endpoint']);
        $this->assertSame('POST', $array['httpMethod']);
        $this->assertNull($array['tabs']);
        $this->assertIsArray($array['inputs']);
        $this->assertCount(5, $array['inputs']);

        $nameInput = $array['inputs'][0];
        $this->assertSame('name', $nameInput['fieldName']);
        $this->assertSame('text', $nameInput['type']);
        $this->assertContains('required', $nameInput['rules']);
        $this->assertContains('string', $nameInput['rules']);
        $this->assertSame(100, $nameInput['maxLength']);
    }

    /**
     * Escenario: se extraen las reglas de validación en creación (POST) y en actualización (PATCH).
     * Expectativa: en modo creación se exige 'required', en actualización PATCH se sustituye por 'sometimes|required'.
     */
    #[Test]
    public function it_compiles_validation_rules_for_creation_and_partial_update_patch(): void
    {
        // given
        $form = FormSchema::make('user-form')
            ->fields([
                Field::text('fullName')->required()->string(),
                Field::email('email')->required(),
                Field::number('score')->nullable()->numeric(),
            ]);

        // when - creación
        $rules = $form->toValidationRules(isUpdate: false);

        // then
        $this->assertArrayHasKey('fullName', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('score', $rules);
        $this->assertSame(['required', 'string'], $rules['fullName']);
        $this->assertSame(['email', 'required'], $rules['email']);

        // when - actualización parcial (PATCH con dirty tracking)
        $updateRules = $form->toValidationRules(isUpdate: true);

        // then
        $this->assertSame(['sometimes', 'required', 'string'], $updateRules['fullName']);
        $this->assertSame(['sometimes', 'required', 'email'], $updateRules['email']);
        $this->assertContains('nullable', $updateRules['score']);
        $this->assertContains('numeric', $updateRules['score']);
    }

    /**
     * Escenario: se define un formulario estructurado en múltiples pestañas (FormTab) mediante el patrón Composite.
     * Expectativa: toValidationRules compila recursivamente las reglas de todos los campos contenidos en cada pestaña.
     */
    #[Test]
    public function it_compiles_composite_validation_rules_across_multiple_tabs(): void
    {
        // given
        $form = FormSchema::make('tabbed-user-form')
            ->tabs([
                FormTab::make('general', 'Información Básica')
                    ->icon('tabler-user')
                    ->fields([
                        Field::text('name')->required(),
                        Field::email('email')->required(),
                    ]),
                FormTab::make('security', 'Seguridad')
                    ->icon('tabler-lock')
                    ->fields([
                        Field::password('password')->required()->confirmed(),
                        Field::select('role')->options(['admin' => 'Admin', 'user' => 'User'])->required(),
                    ]),
            ]);

        // when
        $this->assertTrue($form->hasTabs());
        $fields = $form->getFields();
        $rules = $form->toValidationRules(isUpdate: false);
        $array = $form->toArray();

        // then
        $this->assertCount(4, $fields);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('role', $rules);
        $this->assertContains('confirmed', $rules['password']);

        // Verifica serialización jerárquica
        $this->assertNull($array['inputs']);
        $this->assertIsArray($array['tabs']);
        $this->assertCount(2, $array['tabs']);
        $this->assertSame('general', $array['tabs'][0]['id']);
        $this->assertSame('tabler-user', $array['tabs'][0]['icon']);
        $this->assertCount(2, $array['tabs'][0]['inputs']);
    }

    /**
     * Escenario: se define un formulario compuesto por secciones agrupadas (FormSection).
     * Expectativa: toValidationRules compila los campos de cada sección y toArray produce la clave sections.
     */
    #[Test]
    public function it_compiles_composite_validation_rules_across_form_sections(): void
    {
        // given
        $form = FormSchema::make('sectioned-form')
            ->sections([
                FormSection::make('Identificación')
                    ->description('Datos personales')
                    ->fields([
                        Field::text('first_name')->required(),
                        Field::text('last_name')->required(),
                    ]),
                FormSection::make('Contacto')
                    ->fields([
                        Field::email('personal_email')->required(),
                    ]),
            ]);

        // when
        $this->assertTrue($form->hasSections());
        $fields = $form->getFields();
        $rules = $form->toValidationRules(isUpdate: false);
        $array = $form->toArray();

        // then
        $this->assertCount(3, $fields);
        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('last_name', $rules);
        $this->assertArrayHasKey('personal_email', $rules);

        $this->assertIsArray($array['sections']);
        $this->assertCount(2, $array['sections']);
        $this->assertSame('Identificación', $array['sections'][0]['title']);
    }

    /**
     * Escenario: se configura una regla de visibilidad reactiva frontend (visibleWhen).
     * Expectativa: se emite el objeto visibleWhen con campo observado, valor esperado y operador de comparación.
     */
    #[Test]
    public function it_serializes_reactive_conditional_visibility_rules(): void
    {
        // given
        $field = Field::text('company_name')
            ->label('Razón Social')
            ->visibleWhen('doc_type', 'ruc');

        // when
        $rule = $field->getVisibleWhen();
        $array = $field->toArray();

        // then
        $this->assertSame([
            'field' => 'doc_type',
            'is' => 'ruc',
            'operator' => '===',
        ], $rule);
        $this->assertSame('ruc', $array['visibleWhen']['is']);
        $this->assertSame('doc_type', $array['visibleWhen']['field']);
    }

    /**
     * Escenario: se instancian campos especializados (textarea, switch, select autocomplete, dateRange, hidden).
     * Expectativa: sus atributos específicos (rows, autoGrow, trueValue, autocomplete, defaultValue) se configuran correctamente.
     */
    #[Test]
    public function it_handles_specialized_field_types_and_their_attributes(): void
    {
        // given
        $textarea = Field::textarea('bio')->rows(5)->autoGrow(true);
        $switch = Field::switch('is_active')->trueValue(1)->falseValue(0);
        $select = Field::select('tags')->autocomplete(true)->chips(true)->multiple(true);
        $hidden = Field::hidden('tenant_id', 99);
        $dateRange = Field::dateRange('period');

        // when
        $textareaArray = $textarea->toArray();
        $switchArray = $switch->toArray();
        $selectArray = $select->toArray();
        $hiddenArray = $hidden->toArray();
        $dateRangeArray = $dateRange->toArray();

        // then
        $this->assertSame(FieldType::TEXTAREA->value, $textareaArray['type']);
        $this->assertSame(5, $textareaArray['rows']);
        $this->assertTrue($textareaArray['autoGrow']);

        $this->assertSame(FieldType::SWITCH->value, $switchArray['type']);
        $this->assertSame(1, $switchArray['trueValue']);
        $this->assertSame(0, $switchArray['falseValue']);

        $this->assertSame(FieldType::SELECT->value, $selectArray['type']);
        $this->assertTrue($selectArray['autocomplete']);
        $this->assertTrue($selectArray['chips']);
        $this->assertTrue($selectArray['multiple']);

        $this->assertSame(FieldType::HIDDEN->value, $hiddenArray['type']);
        $this->assertSame(99, $hiddenArray['defaultValue']);

        $this->assertSame(FieldType::DATE_RANGE->value, $dateRangeArray['type']);
        $this->assertTrue($dateRangeArray['range']);
    }

    /**
     * Escenario: se configura un campo select dinámico con endpoint remoto y dependencias en cascada.
     * Expectativa: optionsSource se serializa con la URL, dependencias y parámetros esperados.
     */
    #[Test]
    public function it_handles_dynamic_select_with_endpoint_and_cascading_dependencies(): void
    {
        // given
        $departmentSelect = Field::dynamicSelect('department_id', 'Departamento', '/api/v1/departments');
        $citySelect = Field::select('city_id', 'Ciudad')
            ->fromEndpoint('/api/v1/cities')
            ->dependsOn('department_id', 'dept_id')
            ->queryParams(['status' => 'active']);

        // when
        $deptArray = $departmentSelect->toArray();
        $cityArray = $citySelect->toArray();

        // then
        $this->assertNotNull($deptArray['optionsSource']);
        $this->assertSame('/api/v1/departments', $deptArray['optionsSource']['endpoint']);
        $this->assertSame('api', $deptArray['optionsSource']['type']);

        $this->assertNotNull($cityArray['optionsSource']);
        $this->assertSame('/api/v1/cities', $cityArray['optionsSource']['endpoint']);
        $this->assertSame('department_id', $cityArray['optionsSource']['dependOnField']);
        $this->assertSame('dept_id', $cityArray['optionsSource']['paramKey']);
        $this->assertSame(['status' => 'active'], $cityArray['optionsSource']['queryParams']);
    }

    /**
     * Escenario: se inyectan reglas de validación custom o adicionales directamente al FormSchema.
     * Expectativa: toValidationRules combina las reglas de los campos con las reglas custom inyectadas.
     */
    #[Test]
    public function it_allows_custom_validation_rules_and_overrides(): void
    {
        // given
        $schema = FormSchema::make()
            ->fields([
                Field::text('email')->required()->asEmail(),
            ])
            ->addValidationRule('email', ['max:100'])
            ->addValidationRule('avatar', ['required', 'image']);

        // when
        $rules = $schema->toValidationRules();

        // then
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('avatar', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('max:100', $rules['email']);
        $this->assertSame(['required', 'image'], $rules['avatar']);
    }
}
