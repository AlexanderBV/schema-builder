<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SchemaBuilderServiceProvider extends ServiceProvider
{
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

    public function boot(): void
    {
        $this->registerRouteMacros();
    }

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
