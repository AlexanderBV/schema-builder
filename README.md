# Laravel Schema Builder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/warrior/schema-builder.svg?style=flat-square)](https://packagist.org/packages/warrior/schema-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/AlexanderBV/schema-builder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/AlexanderBV/schema-builder/actions)
[![PHPStan Status](https://img.shields.io/github/actions/workflow/status/AlexanderBV/schema-builder/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/AlexanderBV/schema-builder/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/warrior/schema-builder.svg?style=flat-square)](https://packagist.org/packages/warrior/schema-builder)
[![License](https://img.shields.io/packagist/l/warrior/schema-builder.svg?style=flat-square)](https://github.com/AlexanderBV/schema-builder/blob/main/LICENSE.md)

Fluent, headless, and expressive schema builder for dynamic tables, forms, and CRUDs in Laravel.

---

## 🌟 Características Principales

- **Agnóstico al Frontend**: Genera contratos JSON estandarizados consumibles por Vuexy, Vue 3, React, Flutter o cualquier cliente HTTP.
- **Fluent Interface (DX First)**: Autocompletado del 100% en tu IDE con métodos encadenables legibles (`TableSchema::make()`, `FormSchema::make()`, `Field::text()`).
- **Motor de Tablas y Listas**: Soporta columnas formateadas (avatars, badges, monedas, fechas, booleanos), búsqueda, filtros en drawer, pestañas contextuales, cabeceras fijas, selección masiva y Soft Deletes.
- **Motor de Formularios Universal**: Úsalo para CRUDs o para pantallas autónomas (configuración, perfiles, wizards). Auto-extrae reglas de validación nativas para Laravel (`$form->toValidationRules()`).
- **Principios SOLID y Clean Code**: Construido sobre patrones **Builder, Composite y Strategy**, garantizando máxima extensibilidad sin acoplamiento.
- **Zero-Dependency Core**: No depende de ningún frontend ni de motores ajenos.

---

## 🚀 Instalación

```bash
composer require warrior/schema-builder
```

---

## ⚡ CRUD en 5 Minutos (Quickstart)

### 1. Define el Schema (`app/Schemas/ProductSchema.php`)
```php
namespace App\Schemas;

use Warrior\SchemaBuilder\Table\TableSchema;
use Warrior\SchemaBuilder\Table\Column;
use Warrior\SchemaBuilder\Table\Filter;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Form\FormTab;
use Warrior\SchemaBuilder\Form\Field;

class ProductSchema
{
    public static function table(): TableSchema
    {
        return TableSchema::make('products-table')
            ->title('Catálogo de Productos')
            ->endpoint('/api/v1/products')
            ->fixedHeader()
            ->selectable()
            ->columns([
                Column::make('name')->title('Producto')->sortable(),
                Column::make('price')->title('Precio')->currency('USD')->sortable(),
                Column::make('status')->title('Estado')->badge(['active' => 'success', 'out' => 'error']),
            ])
            ->filters([
                Filter::select('status', 'Estado')->options([
                    ['value' => 'active', 'label' => 'Disponible'],
                    ['value' => 'out', 'label' => 'Agotado'],
                ]),
            ]);
    }

    public static function form(): FormSchema
    {
        return FormSchema::make('product-form')
            ->tabs([
                FormTab::make('general', 'General')
                    ->fields([
                        Field::text('name')->label('Nombre')->required()->cols(12),
                        Field::number('price')->label('Precio')->required()->min(0)->cols(6),
                    ]),
            ]);
    }
}
```

### 2. Controlador en Laravel
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Schemas\ProductSchema;
use Warrior\SchemaBuilder\Concerns\HasDynamicCrudSchema;

class ProductController extends Controller
{
    use HasDynamicCrudSchema;

    protected string $model = Product::class;

    protected function tableSchema() { return ProductSchema::table(); }
    protected function formSchema() { return ProductSchema::form(); }
}
```

---

## 🧪 Pruebas y Control de Calidad

```bash
# Ejecutar tests unitarios
composer test

# Análisis estático (PHPStan nivel 8)
composer phpstan

# Formateo de código estricto
composer format
```

---

## 📖 Documentación de Arquitectura (SPEC)

Este proyecto se desarrolla bajo la metodología **SPEC**. Consulta la suite de especificaciones en [.documents/](.documents/):
- [SPEC.md](.documents/SPEC.md): Índice canónico y principios de arquitectura.
- [SPEC-002: Contrato JSON Schema](.documents/specs/SPEC-002-contrato-json-schema.md)
- [SPEC-008: Motor de Formularios Fluido](.documents/specs/SPEC-008-motor-formularios-fluent.md)
- [SPEC-010: Patrón Bridge Opcional con api-query-builder](.documents/specs/SPEC-010-bridge-api-query-builder.md)

---

## 📄 Licencia

Este paquete está licenciado bajo la [Licencia MIT](LICENSE.md).
