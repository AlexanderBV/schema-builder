<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

/**
 * Class SchemaBuilderServiceProvider
 *
 * Registra los servicios de SchemaBuilder en el contenedor de dependencias de Laravel
 * y proporciona la macro de enrutamiento Route::crud() para registrar rutas CRUD completas.
 */
class SchemaBuilderServiceProvider extends ServiceProvider
{
    /**
     * Registra el singleton de SchemaBuilder en el contenedor de Laravel.
     */
    public function register(): void
    {
        $this->app->singleton('schema-builder', function () {
            return new class
            {
                public function table(?string $id = null, ?string $title = null): Table\TableSchema
                {
                    return Table\TableSchema::make($id, $title);
                }

                public function form(?string $id = null, ?string $title = null): Form\FormSchema
                {
                    return Form\FormSchema::make($id, $title);
                }

                public function detail(?string $id = null, ?string $title = null): Detail\DetailSchema
                {
                    return Detail\DetailSchema::make($id, $title);
                }
            };
        });
    }

    /**
     * Inicializa los servicios del paquete tras el registro.
     */
    public function boot(): void
    {
        $this->registerRouteMacros();
    }

    /**
     * Registra la macro de conveniencia Route::crud(string $uri, string $controller) en el Router.
     *
     * Expande automáticamente las 8 rutas requeridas por el ecosistema Fullstack CRUD:
     * - GET    /{uri}/schema          -> controller@schema
     * - GET    /{uri}                 -> controller@index
     * - POST   /{uri}                 -> controller@store
     * - GET    /{uri}/{id}            -> controller@show
     * - PATCH  /{uri}/{id}            -> controller@update (soporta PUT, PATCH y POST spoofing)
     * - DELETE /{uri}/{id}            -> controller@destroy
     * - POST   /{uri}/{id}/restore    -> controller@restore
     * - DELETE /{uri}/{id}/force      -> controller@forceDelete
     */
    protected function registerRouteMacros(): void
    {
        Router::macro('crud', function (string $uri, string $controller) {
            /** @var Router $this */
            $name = str_replace('/', '.', trim($uri, '/'));

            $this->get("{$uri}/schema", [$controller, 'schema'])->name("{$name}.schema");
            $this->get("{$uri}", [$controller, 'index'])->name("{$name}.index");
            $this->post("{$uri}", [$controller, 'store'])->name("{$name}.store");
            $this->get("{$uri}/{id}", [$controller, 'show'])->name("{$name}.show");
            $this->match(['put', 'patch', 'post'], "{$uri}/{id}", [$controller, 'update'])->name("{$name}.update");
            $this->delete("{$uri}/{id}", [$controller, 'destroy'])->name("{$name}.destroy");
            $this->post("{$uri}/{id}/restore", [$controller, 'restore'])->name("{$name}.restore");
            $this->delete("{$uri}/{id}/force", [$controller, 'forceDelete'])->name("{$name}.forceDelete");
        });
    }
}
