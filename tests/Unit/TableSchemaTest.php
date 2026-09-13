<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
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
    /**
     * Escenario: se configuran columnas con formateadores de tipo estrategia (Avatar, Badge, Currency, Datetime).
     * Expectativa: cada columna asigna su ColumnType correspondiente y encapsula sus formatOptions específicas.
     */
    #[Test]
    public function it_defines_columns_with_strategy_formatters(): void
    {
        // given
        $avatarCol = Column::make('user')->avatar('avatar_url', 'fullName', 'email');
        $badgeCol = Column::make('role')->badge(['admin' => 'primary', 'editor' => 'info']);
        $currencyCol = Column::make('price')->currency('EUR', 'de-DE', 2);
        $dateCol = Column::make('created_at')->datetime('DD/MM/YYYY HH:mm');

        // when
        $avatarArray = $avatarCol->toArray();
        $badgeArray = $badgeCol->toArray();
        $currencyArray = $currencyCol->toArray();
        $dateArray = $dateCol->toArray();

        // then
        $this->assertSame(ColumnType::AVATAR, $avatarCol->getType());
        $this->assertSame([
            'avatarKey' => 'avatar_url',
            'titleKey' => 'fullName',
            'subtitleKey' => 'email',
        ], $avatarArray['formatOptions']);

        $this->assertSame(ColumnType::BADGE, $badgeCol->getType());
        $this->assertSame(['admin' => 'primary', 'editor' => 'info'], $badgeArray['formatOptions']['colorMap']);

        $this->assertSame(ColumnType::CURRENCY, $currencyCol->getType());
        $this->assertSame(Alignment::END, $currencyCol->getAlign());
        $this->assertSame('EUR', $currencyArray['formatOptions']['currency']);

        $this->assertSame(ColumnType::DATETIME, $dateCol->getType());
        $this->assertSame('DD/MM/YYYY HH:mm', $dateArray['formatOptions']['format']);
    }

    /**
     * Escenario: se construye un TableSchema completo con tabs, búsqueda, paginación, soft deletes, columnas, filtros y acciones.
     * Expectativa: el array serializado cumple rigurosamente con la estructura formal de SPEC-002.
     */
    /**
     * Escenario: se instancia un TableSchema sin invocar fixedHeader explícitamente.
     * Expectativa: fixedHeader es true por defecto en la serialización y permite desactivarlo pasando false.
     */
    #[Test]
    public function it_defaults_fixed_header_to_true_and_allows_disabling_it(): void
    {
        // given
        $defaultTable = TableSchema::make('default-table');
        $disabledTable = TableSchema::make('disabled-table')->fixedHeader(false);

        // when & then
        $this->assertTrue($defaultTable->toArray()['fixedHeader']);
        $this->assertFalse($disabledTable->toArray()['fixedHeader']);
    }

    #[Test]
    public function it_serializes_table_schema_matching_json_spec(): void
    {
        // given
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

        // when
        $json = $table->toArray();

        // then
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

    /**
     * Escenario: se consultan los métodos de introspección pura (getAllowedSorts, getAllowedFilters, getAllowedSearch).
     * Expectativa: retornan arrays limpios de cadenas para alimentar directamente cualquier query builder desacoplado.
     */
    #[Test]
    public function it_provides_pure_introspection_methods_for_sorts_filters_and_search(): void
    {
        // given
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

        // when
        $sorts = $table->getAllowedSorts();
        $search = $table->getAllowedSearch();
        $filters = $table->getAllowedFilters();

        // then - 1. Allowed Sorts (únicamente sortable === true)
        $this->assertSame(['fullName', 'email', 'created_at'], $sorts);

        // then - 2. Allowed Search
        $this->assertSame(['fullName', 'email', 'document_id'], $search);

        // then - 3. Allowed Filters (Drawer filters + Tab filters + SoftDeletes 'trashed')
        $this->assertContains('role', $filters);
        $this->assertContains('country_id', $filters);
        $this->assertContains('status', $filters);
        $this->assertContains('is_verified', $filters);
        $this->assertContains('trashed', $filters);
    }

    /**
     * Escenario: soft deletes se encuentra deshabilitado en TableSchema.
     * Expectativa: el queryParam de soft deletes ('trashed') NO se incluye en getAllowedFilters.
     */
    #[Test]
    public function it_excludes_soft_deletes_from_allowed_filters_when_disabled(): void
    {
        // given
        $table = TableSchema::make('simple-table')
            ->filters([Field::text('category')])
            ->softDeletes(false);

        // when
        $filters = $table->getAllowedFilters();

        // then
        $this->assertSame(['category'], $filters);
        $this->assertNotContains('trashed', $filters);
    }
}
