# Laravel Schema Builder ⚡

[![Latest Version on Packagist](https://img.shields.io/packagist/v/warrior/schema-builder.svg?style=flat-square)](https://packagist.org/packages/warrior/schema-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/AlexanderBV/schema-builder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/AlexanderBV/schema-builder/actions)
[![PHPStan Status](https://img.shields.io/github/actions/workflow/status/AlexanderBV/schema-builder/phpstan.yml?branch=main&label=phpstan%20lvl%208&style=flat-square)](https://github.com/AlexanderBV/schema-builder/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/warrior/schema-builder.svg?style=flat-square)](https://packagist.org/packages/warrior/schema-builder)
[![PHP Version](https://img.shields.io/packagist/dependency-v/warrior/schema-builder/php.svg?style=flat-square)](https://packagist.org/packages/warrior/schema-builder)
[![Documentation](https://img.shields.io/badge/docs-online-brightgreen.svg?style=flat-square)](https://alexanderbv.github.io/schema-builder-docs/)
[![AI Skills](https://img.shields.io/badge/AI%20Skills-Cursor%20%7C%20Claude%20%7C%20Gemini-orange.svg?style=flat-square)](https://github.com/AlexanderBV/schema-builder-skills)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](LICENSE.md)

Construye formularios interactivos, tablas dinámicas y validaciones seguras en 5 minutos sin duplicar código en **Laravel 10, 11 y 12**, listo para alimentar cualquier interfaz en el frontend (BootstrapVue, Vue 3, React, Svelte o Blade) mediante contratos JSON estandarizados.

> 📚 **Documentación Oficial y Guías Completas:**  
> **[https://alexanderbv.github.io/schema-builder-docs/](https://alexanderbv.github.io/schema-builder-docs/)**  
> 🤖 **Skills y Reglas para Agentes de IA (Cursor, Claude, Gemini, Antigravity):**  
> **[https://github.com/AlexanderBV/schema-builder-skills](https://github.com/AlexanderBV/schema-builder-skills)**

---

## 🌟 Filosofía y Principios de Diseño

- **100% Agnóstico al Frontend:** Produce estructuras JSON estándar y predecibles consumibles por cualquier cliente HTTP o framework de UI (BootstrapVue, Vue 3, React, Svelte o Blade con Alpine.js).
- **Developer Experience (DX First):** Autocompletado del 100% en tu IDE con métodos encadenables legibles y autoexplicativos (`TableSchema::make()`, `FormSchema::make()`, `Field::text()`).
- **Principios SOLID & Patrones GoF:**
  - **Fluent Builder:** Construcción ergonómica paso a paso.
  - **Composite Pattern:** Jerarquías modulares en formularios y vistas de detalle (`FormSchema`, `FormTab`, `FormSection`).
  - **Strategy Pattern:** Estrategias de formateo visual de celdas (`AvatarFormatter`, `BadgeFormatter`, `CurrencyFormatter`, `DateFormatter`).
  - **Value Objects:** Enums respaldados tipados de PHP 8.2 (`ColumnType`, `FieldType`, `Alignment`, `PaginationPosition`, `TabsPosition`).
  - **Open/Closed (Macroable):** Extensible en tiempo de ejecución mediante macros de Laravel sin modificar el código fuente.
- **Zero-Code Coupling:** Cero acoplamiento obligatorio con ORMs o Query Builders. Expone métodos de introspección puros (`getAllowedSorts()`, `getAllowedFilters()`, `getAllowedSearch()`).
- **Eliminación de Código Repetitivo (DRY):** Tus esquemas visuales actúan como la única fuente de la verdad para compilar reglas de validación en Laravel (`$form->toValidationRules()`) y configurar filtros del servidor.

---

## 📦 Instalación

Instala el paquete vía Composer:

```bash
composer require warrior/schema-builder
```

El paquete registra automáticamente su `SchemaBuilderServiceProvider` y el alias `SchemaBuilder` mediante el package discovery de Laravel.

---

## ⚡ CRUD en 5 Minutos (Guía Rápida)

### 1. Define tu Schema Unificado (`app/Schemas/UserSchema.php`)

```php
namespace App\Schemas;

use Warrior\SchemaBuilder\Table\TableSchema;
use Warrior\SchemaBuilder\Table\Column;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Form\FormTab;
use Warrior\SchemaBuilder\Form\Field;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Detail\DetailField;

class UserSchema
{
    public static function table(): TableSchema
    {
        return TableSchema::make('users-table', 'Gestión de Usuarios')
            ->subtitle('Listado general de cuentas y roles')
            ->endpoint('/api/v1/users')
            ->fixedHeader()
            ->selectable(true, 'id')
            ->columns([
                Column::make('fullName', 'Usuario')->avatar('avatar', 'fullName', 'email')->sortable(),
                Column::make('role', 'Rol')->badge(['admin' => 'primary', 'editor' => 'info'])->sortable(),
                Column::make('balance', 'Saldo')->currency('USD')->sortable(),
                Column::make('created_at', 'Registro')->date()->sortable(),
            ])
            ->filters([
                Field::select('role', 'Filtrar por Rol')->options([
                    'admin' => 'Administrador',
                    'editor' => 'Editor',
                ]),
            ]);
    }

    public static function form(): FormSchema
    {
        return FormSchema::make('user-form', 'Expediente del Usuario')
            ->tabs([
                FormTab::make('general', 'Información Básica')
                    ->icon('tabler-user')
                    ->fields([
                        Field::text('fullName', 'Nombre Completo')->required()->string()->maxLength(100)->cols(12),
                        Field::email('email', 'Correo Electrónico')->required()->cols(12),
                    ]),
                FormTab::make('security', 'Seguridad y Acceso')
                    ->icon('tabler-lock')
                    ->fields([
                        Field::password('password', 'Contraseña')->required()->confirmed()->cols(12),
                        Field::select('role', 'Rol Asignado')->options([
                            'admin' => 'Administrador',
                            'editor' => 'Editor',
                        ])->required()->cols(6),
                    ]),
            ]);
    }
}
```

### 2. Integra el Controlador (`app/Http/Controllers/Api/UserController.php`)

Usa el trait `HasDynamicCrudSchema` para obtener automáticamente el endpoint `/schema` y validaciones:

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Schemas\UserSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Warrior\SchemaBuilder\Concerns\HasDynamicCrudSchema;

class UserController extends Controller
{
    use HasDynamicCrudSchema;

    protected function tableSchema() { return UserSchema::table(); }
    protected function formSchema() { return UserSchema::form(); }

    public function store(Request $request): JsonResponse
    {
        // Valida automáticamente contra las reglas de UserSchema::form()
        $validated = $this->validateWithSchema($request, isUpdate: false);
        $user = User::create($validated);

        return response()->json(['message' => 'Usuario creado', 'data' => $user], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // En PATCH, dirty tracking permite omitir campos no modificados
        $validated = $this->validateWithSchema($request, isUpdate: true);
        $user = User::findOrFail($id);
        $user->update($validated);

        return response()->json(['message' => 'Usuario actualizado', 'data' => $user]);
    }
}
```

### 3. Registra las Rutas en 1 Línea (`routes/api.php`)

```php
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::crud('users', UserController::class);
});
```

El macro `Route::crud()` registra automáticamente los 8 endpoints necesarios:
```
GET    /api/v1/users/schema           users.schema
GET    /api/v1/users                  users.index
POST   /api/v1/users                  users.store
GET    /api/v1/users/{id}             users.show
PATCH  /api/v1/users/{id}             users.update (soporta PUT, PATCH y POST spoofing)
DELETE /api/v1/users/{id}             users.destroy
POST   /api/v1/users/{id}/restore     users.restore
DELETE /api/v1/users/{id}/force       users.forceDelete
```

---

## 📊 Motor de Tablas (`TableSchema`)

### Estrategias de Formateo de Columnas (Formatters)

Cada columna soporta formateadores visuales para renderizado automático en el frontend:

```php
// Avatar con iniciales, imagen, título y subtítulo
Column::make('user')->avatar(avatarKey: 'avatar', titleKey: 'name', subtitleKey: 'email');

// Badge con mapeo de colores semánticos (Vuetify / Bootstrap / Tailwind)
Column::make('status')->badge([
    'active'   => 'success',
    'pending'  => 'warning',
    'banned'   => 'error',
]);

// Moneda con formato localizado
Column::make('amount')->currency(currency: 'USD', locale: 'en-US', decimals: 2);

// Fechas y Horas localizadas
Column::make('created_at')->datetime('DD/MM/YYYY HH:mm');
Column::make('birth_date')->date('YYYY-MM-DD');

// Tipos auxiliares
Column::make('is_verified')->boolean();
Column::make('website')->link();
Column::make('payload')->json();
```

### Pestañas Contextuales y Soft Deletes

```php
$table
    ->tabs([
        TableTab::make('all', 'Todos')->icon('tabler-users'),
        TableTab::make('active', 'Activos')
            ->icon('tabler-circle-check')
            ->badge(15, 'success')
            ->filter(['status' => 'active']),
    ])
    ->softDeletes(fn ($sd) => $sd
        ->queryParam('trashed')
        ->endpoints(
            restore: '/api/v1/users/{id}/restore',
            forceDelete: '/api/v1/users/{id}/force'
        )
    );
```

### Acciones de Fila, Toolbar y Masivas

```php
$table
    ->showAction(enabled: true, permission: 'users.view')
    ->updateAction(enabled: true, permission: 'users.edit')
    ->deleteAction(enabled: true, permission: 'users.delete')
    ->addRowAction(RowAction::make('reset_password', 'Restablecer Clave')
        ->icon('tabler-key')
        ->color('warning')
        ->permission('users.security'))
    ->addHeaderAction(HeaderAction::make('create', 'Nuevo')
        ->icon('tabler-plus')
        ->permission('users.create'))
    ->bulkActions([
        BulkAction::make('delete', 'Eliminar Seleccionados')
            ->icon('tabler-trash')
            ->color('error')
            ->permission('users.delete'),
    ]);
```

### Introspección Pura (Zero-Coupling)

Extrae arrays estándar de PHP (`array<string>`) para alimentar cualquier query builder:

```php
$schema = UserSchema::table();

$allowedSorts   = $schema->getAllowedSorts();   // Columnas con sortable === true
$allowedFilters = $schema->getAllowedFilters(); // Filtros del drawer + pestañas + trashed
$allowedSearch  = $schema->getAllowedSearch();  // Columnas habilitadas para búsqueda
```

---

## 📝 Motor de Formularios (`FormSchema`)

### Catálogo de Campos Soportados

| Campo | Método de Fábrica | Opciones Clave |
| :--- | :--- | :--- |
| **Texto** | `Field::text('name')` | `maxLength()`, `prefix()`, `suffix()` |
| **Email** | `Field::email('email')` | Auto-inyecta regla `email` |
| **Password** | `Field::password('password')` | `toggleVisibility()`, `confirmed()` |
| **Número** | `Field::number('age')` | `min()`, `max()`, `step()` |
| **Textarea** | `Field::textarea('bio')` | `rows()`, `autoGrow()` |
| **Select** | `Field::select('role')` | `options()`, `optionsFromEnum()`, `multiple()`, `chips()`, `autocomplete()` |
| **Radio** | `Field::radio('gender')` | `options()`, `inline()` |
| **Checkbox** | `Field::checkbox('terms')` | `label()` |
| **Switch** | `Field::switch('active')` | `trueValue()`, `falseValue()` |
| **Fecha** | `Field::date('birthday')` | `format()`, `minDate()`, `maxDate()` |
| **Fecha y Hora** | `Field::datetime('event_at')` | `format()`, `enableTime()` |
| **Rango de Fechas** | `Field::dateRange('period')` | `range()` |
| **Archivo** | `Field::file('document')` | `accept()`, `maxSize()`, `multiple()` |
| **Imagen** | `Field::image('avatar')` | Preconfigura `image/*` y regla `image` |
| **Oculto** | `Field::hidden('account_id')` | `value()` |

### Compilación de Validaciones y Soporte Dirty Tracking (PATCH)

```php
$form = FormSchema::make('user-form')
    ->fields([
        Field::text('name')->required()->string()->max(100),
        Field::email('email')->required()->unique('users', 'email'),
        Field::number('age')->nullable()->min(18),
    ]);

// 1. Modo Creación (POST):
$rules = $form->toValidationRules(isUpdate: false);
// Resultado: ['name' => ['required', 'string', 'max:100'], 'email' => ['email', 'required', 'unique:...'], ...]

// 2. Modo Edición (PATCH - Dirty Tracking):
$patchRules = $form->toValidationRules(isUpdate: true);
// Resultado: ['name' => ['sometimes', 'required', 'string', 'max:100'], ...]
// Si el cliente no envía 'name' porque no lo modificó, ¡la validación pasa exitosamente!
```

### Grid Responsive y Visibilidad Condicional Reactiva

```php
// Grid responsive de 12 columnas (Vuetify / Bootstrap):
Field::text('dni')->cols(12)->sm(6)->md(4);

// Visibilidad reactiva en frontend evaluando otro campo:
Field::text('company_name')
    ->label('Razón Social')
    ->visibleWhen('document_type', 'ruc');
```

---

## 🤝 Sinergia con `warrior/api-query-builder` (Receta Opcional)

Ambas librerías están **100% desacopladas**. Para usarlas combinadas con la máxima elegancia, puedes registrar una macro de usuario en el `AppServiceProvider` de tu proyecto:

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Warrior\ApiQueryBuilder\ApiQueryBuilder;
use Warrior\SchemaBuilder\Table\TableSchema;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Macro de conveniencia en tu aplicación
        ApiQueryBuilder::macro('applySchema', function (TableSchema $schema) {
            /** @var ApiQueryBuilder $this */
            return $this
                ->allowedSorts($schema->getAllowedSorts())
                ->allowedFilters($schema->getAllowedFilters())
                ->allowedSearch($schema->getAllowedSearch());
        });
    }
}
```

### Uso en tus controladores:

```php
public function index(): JsonResponse
{
    return User::apiQuery()
        ->applySchema(UserSchema::table())
        ->response();
}
```

---

## 🧪 Pruebas y Calidad de Código

El paquete cuenta con una cobertura exhaustiva de pruebas unitarias y de integración:

```bash
# Ejecutar tests con PHPUnit
composer test

# Análisis estático de tipos en Nivel 8 (Larastan / PHPStan)
composer phpstan

# Verificación de estilo PSR-12 / Laravel Pint
composer lint

# Formateo automático de código
composer format
```

---

## 📄 Licencia

Este paquete es software de código abierto licenciado bajo la [Licencia MIT](LICENSE.md).
