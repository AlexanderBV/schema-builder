<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Warrior\SchemaBuilder\Detail\DetailField;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Detail\DetailTab;
use Warrior\SchemaBuilder\Enums\ColumnType;

class DetailSchemaTest extends TestCase
{
    /**
     * Escenario: se define una vista de detalle plana con campos formateados (avatar, badge, currency, datetime).
     * Expectativa: el array serializado contiene fields poblado y tabs en null, con tipos y formato de columnas correctos.
     */
    #[Test]
    public function it_serializes_flat_detail_schema_matching_json_spec(): void
    {
        // given
        $schema = DetailSchema::make('user-detail', 'Detalle de Usuario')
            ->description('Vista de solo lectura')
            ->fields([
                DetailField::make('fullName')->label('Nombre Completo')->avatar('avatar_url', 'email')->cols(12),
                DetailField::make('role')->badge(['admin' => 'primary'])->cols(6),
                DetailField::make('balance')->currency('USD')->cols(6),
                DetailField::make('created_at')->datetime()->cols(6),
            ]);

        // when
        $hasTabs = $schema->hasTabs();
        $fields = $schema->getFields();
        $array = $schema->toArray();

        // then
        $this->assertFalse($hasTabs);
        $this->assertCount(4, $fields);
        $this->assertSame('user-detail', $array['id']);
        $this->assertSame('Detalle de Usuario', $array['title']);
        $this->assertNull($array['tabs']);
        $this->assertIsArray($array['fields']);
        $this->assertCount(4, $array['fields']);

        $this->assertSame(ColumnType::AVATAR->value, $array['fields'][0]['type']);
        $this->assertSame(ColumnType::BADGE->value, $array['fields'][1]['type']);
        $this->assertSame(ColumnType::CURRENCY->value, $array['fields'][2]['type']);
        $this->assertSame(ColumnType::DATETIME->value, $array['fields'][3]['type']);
    }

    /**
     * Escenario: se define una vista de detalle estructurada por pestañas (DetailTab).
     * Expectativa: toArray produce tabs poblado con sus respectivos fields y fields en la raíz como null.
     */
    #[Test]
    public function it_serializes_tabbed_detail_schema_for_structured_inspection(): void
    {
        // given
        $schema = DetailSchema::make('user-detail')
            ->tabs([
                DetailTab::make('general', 'General')->icon('tabler-user')->fields([
                    DetailField::make('name')->cols(6),
                    DetailField::make('email')->cols(6),
                ]),
                DetailTab::make('finances', 'Finanzas')->icon('tabler-wallet')->fields([
                    DetailField::make('balance')->currency()->cols(12),
                ]),
            ]);

        // when
        $hasTabs = $schema->hasTabs();
        $fields = $schema->getFields();
        $array = $schema->toArray();

        // then
        $this->assertTrue($hasTabs);
        $this->assertCount(3, $fields);

        $this->assertNull($array['fields']);
        $this->assertIsArray($array['tabs']);
        $this->assertCount(2, $array['tabs']);
        $this->assertSame('general', $array['tabs'][0]['id']);
        $this->assertSame('tabler-user', $array['tabs'][0]['icon']);
        $this->assertCount(2, $array['tabs'][0]['fields']);
    }

    /**
     * Escenario: se configuran campos de detalle con tipos boolean, link y json.
     * Expectativa: los tipos se mapean con los valores exactos del Enum ColumnType.
     */
    #[Test]
    public function it_formats_detail_fields_with_specialized_types(): void
    {
        // given
        $boolField = DetailField::make('is_active')->boolean();
        $linkField = DetailField::make('website')->link();
        $jsonField = DetailField::make('metadata')->json();

        // when / then
        $this->assertSame(ColumnType::BOOLEAN, $boolField->getType());
        $this->assertSame(ColumnType::LINK, $linkField->getType());
        $this->assertSame(ColumnType::JSON, $jsonField->getType());
    }
}
