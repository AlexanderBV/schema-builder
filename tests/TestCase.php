<?php

declare(strict_types=1);

namespace Warrior\SchemaBuilder\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Warrior\SchemaBuilder\SchemaBuilderServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            SchemaBuilderServiceProvider::class,
        ];
    }
}
