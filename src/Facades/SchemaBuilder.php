<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\Table\TableSchema;

/**
 * @method static TableSchema table(?string $id = null, ?string $title = null)
 * @method static FormSchema form(?string $id = null, ?string $title = null)
 * @method static DetailSchema detail(?string $id = null, ?string $title = null)
 */
class SchemaBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'schema-builder';
    }

    public static function table(?string $id = null, ?string $title = null): TableSchema
    {
        return TableSchema::make($id, $title);
    }

    public static function form(?string $id = null, ?string $title = null): FormSchema
    {
        return FormSchema::make($id, $title);
    }

    public static function detail(?string $id = null, ?string $title = null): DetailSchema
    {
        return DetailSchema::make($id, $title);
    }
}
