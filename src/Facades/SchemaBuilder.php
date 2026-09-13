<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use Warrior\SchemaBuilder\Detail\DetailSchema;
use Warrior\SchemaBuilder\Form\FormSchema;
use Warrior\SchemaBuilder\SchemaBuilderServiceProvider;
use Warrior\SchemaBuilder\Table\TableSchema;

/**
 * Class SchemaBuilder
 *
 * Facade de conveniencia para la creación rápida de esquemas de tablas, formularios y vistas de detalle.
 *
 * @method static TableSchema table(?string $id = null, ?string $title = null)
 * @method static FormSchema form(?string $id = null, ?string $title = null)
 * @method static DetailSchema detail(?string $id = null, ?string $title = null)
 *
 * @see SchemaBuilderServiceProvider
 */
class SchemaBuilder extends Facade
{
    /**
     * Obtiene el nombre registrado del componente en el contenedor IoC.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'schema-builder';
    }

    /**
     * Crea una nueva instancia de TableSchema de manera estática.
     */
    public static function table(?string $id = null, ?string $title = null): TableSchema
    {
        return TableSchema::make($id, $title);
    }

    /**
     * Crea una nueva instancia de FormSchema de manera estática.
     */
    public static function form(?string $id = null, ?string $title = null): FormSchema
    {
        return FormSchema::make($id, $title);
    }

    /**
     * Crea una nueva instancia de DetailSchema de manera estática.
     */
    public static function detail(?string $id = null, ?string $title = null): DetailSchema
    {
        return DetailSchema::make($id, $title);
    }
}
