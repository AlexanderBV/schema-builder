<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Feature;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
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
        $validated = $this->validateWithSchema($request, isUpdate: false);

        return response()->json([
            'message' => 'Created successfully',
            'data' => $validated,
        ], 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        // En update, dirty tracking permite omitir campos requeridos no modificados
        $validated = $this->validateWithSchema($request, isUpdate: true);

        return response()->json([
            'message' => 'Updated successfully',
            'id' => $id,
            'data' => $validated,
        ]);
    }
}

class RouteCrudAndControllerTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::crud('api/v1/users', DummyUserController::class);
    }

    /**
     * Escenario: un cliente HTTP solicita la metadata de esquemas en GET /api/v1/users/schema.
     * Expectativa: responde 200 OK con el árbol unificado { table, form, detail } en cascada.
     */
    #[Test]
    public function it_returns_unified_schema_metadata_at_schema_endpoint(): void
    {
        // given
        $endpoint = '/api/v1/users/schema';

        // when
        $response = $this->getJson($endpoint);

        // then
        $response->assertOk()
            ->assertJsonStructure([
                'table' => [
                    'id',
                    'title',
                    'columns' => [
                        '*' => ['key', 'title', 'type', 'sortable'],
                    ],
                    'pagination' => ['defaultPerPage', 'position'],
                    'search' => ['enabled'],
                ],
                'form' => [
                    'id',
                    'title',
                    'inputs' => [
                        '*' => ['fieldName', 'label', 'type', 'rules'],
                    ],
                    'endpoint',
                    'httpMethod',
                ],
                'detail' => [
                    'id',
                    'title',
                ],
            ]);

        $data = $response->json();
        $this->assertSame('users-table', $data['table']['id']);
        $this->assertSame('user-form', $data['form']['id']);
        $this->assertSame('user-form', $data['detail']['id']); // fallback a form
    }

    /**
     * Escenario: un cliente envía una petición POST de creación con campos requeridos ausentes.
     * Expectativa: responde 422 Unprocessable Content con errores de validación en los campos requeridos.
     */
    #[Test]
    public function it_responds_with_422_when_validation_fails_on_store(): void
    {
        // given
        $endpoint = '/api/v1/users';
        $invalidPayload = [];

        // when
        $response = $this->postJson($endpoint, $invalidPayload);

        // then
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /**
     * Escenario: un cliente envía una petición POST de creación con datos válidos según el FormSchema.
     * Expectativa: responde 201 Created y retorna el payload validado.
     */
    #[Test]
    public function it_responds_with_201_when_validation_passes_on_store(): void
    {
        // given
        $endpoint = '/api/v1/users';
        $validPayload = [
            'name' => 'Alejandro Pérez',
            'email' => 'alejandro@example.com',
            'age' => 30,
        ];

        // when
        $response = $this->postJson($endpoint, $validPayload);

        // then
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Created successfully',
                'data' => [
                    'name' => 'Alejandro Pérez',
                    'email' => 'alejandro@example.com',
                    'age' => 30,
                ],
            ]);
    }

    /**
     * Escenario: se realiza una mutación parcial (PATCH / dirty tracking) omitiendo un campo required ('name').
     * Expectativa: la validación tiene éxito porque required se convirtió en sometimes|required en modo actualización.
     */
    #[Test]
    public function it_validates_partial_update_patch_allowing_omitted_required_fields(): void
    {
        // given
        $endpoint = '/api/v1/users/42';
        $partialPayload = [
            'email' => 'nuevo_email@example.com',
        ];

        // when - llamada PATCH
        $response = $this->patchJson($endpoint, $partialPayload);

        // then - no debe fallar por 'name' ausente
        $response->assertOk()
            ->assertJson([
                'message' => 'Updated successfully',
                'id' => 42,
                'data' => [
                    'email' => 'nuevo_email@example.com',
                ],
            ]);
    }

    /**
     * Escenario: se invocan los métodos estáticos del Facade SchemaBuilder.
     * Expectativa: retornan instancias de TableSchema, FormSchema y DetailSchema con sus identificadores.
     */
    #[Test]
    public function it_provides_expressive_facade_methods(): void
    {
        // given / when
        $table = SchemaBuilder::table('t1', 'Table 1');
        $form = SchemaBuilder::form('f1', 'Form 1');
        $detail = SchemaBuilder::detail('d1', 'Detail 1');

        // then
        $this->assertSame('t1', $table->getId());
        $this->assertSame('Table 1', $table->getTitle());

        $this->assertSame('f1', $form->getId());
        $this->assertSame('Form 1', $form->getTitle());

        $this->assertSame('d1', $detail->getId());
        $this->assertSame('Detail 1', $detail->getTitle());
    }
}
