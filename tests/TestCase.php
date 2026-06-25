<?php

namespace CodeTech\EuPago\Tests;

use CodeTech\EuPago\Providers\EuPagoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            EuPagoServiceProvider::class,
        ];
    }
}
