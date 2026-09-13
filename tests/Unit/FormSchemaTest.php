<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Warrior\SchemaBuilder\Form\Field;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Form\FormTab;

class FormSchemaTest extends TestCase
{
    public function test_flat_form_schema_serialization(): void
    {
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

        $array = $form->toArray();

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

    public function test_to_validation_rules_create_and_update(): void
    {
        $form = FormSchema::make('user-form')
            ->fields([
                Field::text('fullName')->required()->string(),
                Field::email('email')->required(),
                Field::number('score')->nullable()->numeric(),
            ]);

        // Creation mode (POST)
        $rules = $form->toValidationRules(isUpdate: false);
        $this->assertArrayHasKey('fullName', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('score', $rules);
        $this->assertSame(['required', 'string'], $rules['fullName']);
        $this->assertSame(['email', 'required'], $rules['email']);

        // Update mode (PATCH): required must become sometimes, required
        $updateRules = $form->toValidationRules(isUpdate: true);
        $this->assertSame(['sometimes', 'required', 'string'], $updateRules['fullName']);
        $this->assertSame(['sometimes', 'required', 'email'], $updateRules['email']);
        $this->assertContains('nullable', $updateRules['score']);
        $this->assertContains('numeric', $updateRules['score']);
    }

    public function test_tabbed_form_schema_and_composite_validation(): void
    {
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

        $this->assertTrue($form->hasTabs());
        $this->assertCount(4, $form->getFields());

        $rules = $form->toValidationRules(isUpdate: false);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('role', $rules);
        $this->assertContains('confirmed', $rules['password']);

        $array = $form->toArray();
        $this->assertNull($array['inputs']);
        $this->assertIsArray($array['tabs']);
        $this->assertCount(2, $array['tabs']);
        $this->assertSame('general', $array['tabs'][0]['id']);
        $this->assertSame('tabler-user', $array['tabs'][0]['icon']);
        $this->assertCount(2, $array['tabs'][0]['inputs']);
    }

    public function test_reactive_conditional_visibility(): void
    {
        $field = Field::text('company_name')
            ->label('Razón Social')
            ->visibleWhen('doc_type', 'ruc');

        $this->assertSame([
            'field' => 'doc_type',
            'is' => 'ruc',
            'operator' => '===',
        ], $field->getVisibleWhen());

        $array = $field->toArray();
        $this->assertSame('ruc', $array['visibleWhen']['is']);
        $this->assertSame('doc_type', $array['visibleWhen']['field']);
    }
}
