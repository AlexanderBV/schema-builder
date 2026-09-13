<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Warrior\SchemaBuilder\Enums\Alignment;
use Warrior\SchemaBuilder\Enums\ColumnType;
use Warrior\SchemaBuilder\Enums\PaginationPosition;
use Warrior\SchemaBuilder\Form\Field;
use Warrior\SchemaBuilder\Table\BulkAction;
use Warrior\SchemaBuilder\Table\Column;
use Warrior\SchemaBuilder\Table\HeaderAction;
use Warrior\SchemaBuilder\Table\RowAction;
use Warrior\SchemaBuilder\Table\TableSchema;
use Warrior\SchemaBuilder\Table\TableTab;

class TableSchemaTest extends TestCase
{
    public function test_column_definition_and_formatters(): void
    {
        $avatarCol = Column::make('user')->avatar('avatar_url', 'fullName', 'email');
        $this->assertSame(ColumnType::AVATAR, $avatarCol->getType());
        $this->assertSame([
            'avatarKey' => 'avatar_url',
            'titleKey' => 'fullName',
            'subtitleKey' => 'email',
        ], $avatarCol->toArray()['formatOptions']);

        $badgeCol = Column::make('role')->badge(['admin' => 'primary', 'editor' => 'info']);
        $this->assertSame(ColumnType::BADGE, $badgeCol->getType());
        $this->assertSame(['admin' => 'primary', 'editor' => 'info'], $badgeCol->toArray()['formatOptions']['colorMap']);

        $currencyCol = Column::make('price')->currency('EUR', 'de-DE', 2);
        $this->assertSame(ColumnType::CURRENCY, $currencyCol->getType());
        $this->assertSame(Alignment::END, $currencyCol->getAlign());
        $this->assertSame('EUR', $currencyCol->toArray()['formatOptions']['currency']);

        $dateCol = Column::make('created_at')->datetime('DD/MM/YYYY HH:mm');
        $this->assertSame(ColumnType::DATETIME, $dateCol->getType());
        $this->assertSame('DD/MM/YYYY HH:mm', $dateCol->toArray()['formatOptions']['format']);
    }

    public function test_table_schema_serialization_matches_spec(): void
    {
        $table = TableSchema::make('users-table', 'Gestión de Usuarios')
            ->subtitle('Listado general de cuentas')
            ->endpoint('/api/v1/users')
            ->fixedHeader(true)
            ->maxHeight('600px')
            ->selectable(true, 'id')
            ->tabs([
                TableTab::make('all', 'Todos')->icon('tabler-users'),
                TableTab::make('active', 'Activos')
                    ->icon('tabler-circle-check')
                    ->badge(12, 'success')
                    ->filter(['status' => 'active']),
            ])
            ->search(true, 'Buscar usuario...', ['name', 'email'])
            ->pagination(fn ($p) => $p->defaultPerPage(25)->position(PaginationPosition::BOTH))
            ->softDeletes(fn ($sd) => $sd->queryParam('trashed')->endpoints('/api/v1/users/{id}/restore'))
            ->columns([
                Column::make('fullName', 'Usuario')->avatar()->sortable(),
                Column::make('role', 'Rol')->badge()->sortable(),
                Column::make('balance', 'Saldo')->currency()->sortable(),
                Column::make('created_at', 'Registro')->date()->sortable(),
            ])
            ->filters([
                Field::select('role')->options(['admin' => 'Admin', 'user' => 'User']),
                Field::select('status')->options(['active' => 'Activo', 'inactive' => 'Inactivo']),
            ])
            ->showAction(true, 'users.show')
            ->updateAction(true, 'users.update')
            ->deleteAction(true, 'users.delete')
            ->addRowAction(RowAction::make('reset_password', 'Reset Pass')->icon('tabler-key')->color('warning')->permission('users.security'))
            ->addHeaderAction(HeaderAction::make('create', 'Nuevo')->icon('tabler-plus')->permission('users.create'))
            ->bulkActions([
                BulkAction::make('delete', 'Eliminar Seleccionados')->icon('tabler-trash')->color('error')->permission('users.delete'),
            ]);

        $json = $table->toArray();

        $this->assertSame('users-table', $json['id']);
        $this->assertSame('Gestión de Usuarios', $json['title']);
        $this->assertSame('/api/v1/users', $json['endpoint']);
        $this->assertTrue($json['fixedHeader']);
        $this->assertSame('600px', $json['maxHeight']);
        $this->assertTrue($json['selectable']);
        $this->assertCount(2, $json['tabs']);
        $this->assertSame('active', $json['tabs'][1]['id']);
        $this->assertSame(25, $json['pagination']['defaultPerPage']);
        $this->assertTrue($json['softDeletes']['enabled']);
        $this->assertSame('trashed', $json['softDeletes']['queryParam']);
        $this->assertCount(4, $json['columns']);
        $this->assertTrue($json['filters']['enabled']);
        $this->assertCount(2, $json['filters']['filterForm']['inputs']);
        $this->assertCount(1, $json['rowActions']['custom']);
        $this->assertSame('reset_password', $json['rowActions']['custom'][0]['id']);
        $this->assertCount(1, $json['headerActions']);
        $this->assertSame('create', $json['headerActions'][0]['id']);
        $this->assertCount(1, $json['bulkActions']['actions']);
    }

    public function test_pure_introspection_methods(): void
    {
        $table = TableSchema::make('users-table')
            ->columns([
                Column::make('id')->sortable(false),
                Column::make('fullName')->sortable(true),
                Column::make('email')->sortable(true),
                Column::make('role')->sortable(false),
                Column::make('created_at')->sortable(true),
            ])
            ->searchFields(['fullName', 'email', 'document_id'])
            ->filters([
                Field::select('role'),
                Field::select('country_id'),
            ])
            ->tabs([
                TableTab::make('all'),
                TableTab::make('active')->filter(['status' => 'active', 'is_verified' => 1]),
            ])
            ->softDeletes(true);

        // 1. Allowed Sorts (only sortable === true)
        $this->assertSame(['fullName', 'email', 'created_at'], $table->getAllowedSorts());

        // 2. Allowed Search
        $this->assertSame(['fullName', 'email', 'document_id'], $table->getAllowedSearch());

        // 3. Allowed Filters (Drawer filters + Tab filters + SoftDeletes)
        $allowedFilters = $table->getAllowedFilters();
        $this->assertContains('role', $allowedFilters);
        $this->assertContains('country_id', $allowedFilters);
        $this->assertContains('status', $allowedFilters);
        $this->assertContains('is_verified', $allowedFilters);
        $this->assertContains('trashed', $allowedFilters);
    }
}
