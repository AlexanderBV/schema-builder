<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Warrior\SchemaBuilder\Detail\DetailField;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Detail\DetailTab;
use Warrior\SchemaBuilder\Enums\ColumnType;

class DetailSchemaTest extends TestCase
{
    public function test_flat_detail_schema(): void
    {
        $schema = DetailSchema::make('user-detail', 'Detalle de Usuario')
            ->description('Vista de solo lectura')
            ->fields([
                DetailField::make('fullName')->label('Nombre Completo')->avatar('avatar_url', 'email')->cols(12),
                DetailField::make('role')->badge(['admin' => 'primary'])->cols(6),
                DetailField::make('balance')->currency('USD')->cols(6),
                DetailField::make('created_at')->datetime()->cols(6),
            ]);

        $this->assertFalse($schema->hasTabs());
        $this->assertCount(4, $schema->getFields());

        $array = $schema->toArray();
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

    public function test_tabbed_detail_schema(): void
    {
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

        $this->assertTrue($schema->hasTabs());
        $this->assertCount(3, $schema->getFields());

        $array = $schema->toArray();
        $this->assertNull($array['fields']);
        $this->assertIsArray($array['tabs']);
        $this->assertCount(2, $array['tabs']);
        $this->assertSame('general', $array['tabs'][0]['id']);
        $this->assertSame('tabler-user', $array['tabs'][0]['icon']);
        $this->assertCount(2, $array['tabs'][0]['fields']);
    }
}
