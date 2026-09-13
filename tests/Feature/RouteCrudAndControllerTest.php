<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Feature;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Warrior\SchemaBuilder\Concerns\HasDynamicCrudSchema;
use Warrior\SchemaBuilder\Facades\SchemaBuilder;
use Warrior\SchemaBuilder\Form\Field;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Table\Column;
use Warrior\SchemaBuilder\Table\TableSchema;
use Warrior\SchemaBuilder\Tests\TestCase;

class DummyUserController extends Controller
{
    use HasDynamicCrudSchema;

    protected function tableSchema(): TableSchema
    {
        return TableSchema::make('users-table', 'Usuarios')
            ->columns([
                Column::make('id')->sortable(),
                Column::make('name')->sortable(),
                Column::make('email'),
            ]);
    }

    protected function formSchema(): FormSchema
    {
        return FormSchema::make('user-form', 'Usuario')
            ->fields([
                Field::text('name', 'Nombre')->required()->string(),
                Field::email('email', 'Correo')->required(),
                Field::number('age', 'Edad')->nullable()->integer(),
            ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateWithSchema($request);

        return response()->json([
            'message' => 'Created successfully',
            'data' => $validated,
        ], 201);
    }
}

class RouteCrudAndControllerTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::crud('api/v1/users', DummyUserController::class);
    }

    public function test_schema_endpoint_returns_table_form_and_detail(): void
    {
        $response = $this->getJson('/api/v1/users/schema');

        $response->assertOk();
        $response->assertJsonStructure([
            'table' => ['id', 'title', 'columns', 'pagination', 'search'],
            'form' => ['id', 'title', 'inputs', 'endpoint', 'httpMethod'],
            'detail' => ['id', 'title'],
        ]);

        $data = $response->json();
        $this->assertSame('users-table', $data['table']['id']);
        $this->assertSame('user-form', $data['form']['id']);
        $this->assertSame('user-form', $data['detail']['id']); // fallback to form
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/users', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Alejandro Pérez',
            'email' => 'alejandro@example.com',
            'age' => 30,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Created successfully',
            'data' => [
                'name' => 'Alejandro Pérez',
                'email' => 'alejandro@example.com',
                'age' => 30,
            ],
        ]);
    }

    public function test_facade_creates_schemas(): void
    {
        $table = SchemaBuilder::table('t1', 'Table 1');
        $this->assertSame('t1', $table->getId());
        $this->assertSame('Table 1', $table->getTitle());

        $form = SchemaBuilder::form('f1', 'Form 1');
        $this->assertSame('f1', $form->getId());
        $this->assertSame('Form 1', $form->getTitle());

        $detail = SchemaBuilder::detail('d1', 'Detail 1');
        $this->assertSame('d1', $detail->getId());
        $this->assertSame('Detail 1', $detail->getTitle());
    }
}
