<?php

namespace CodeTech\EuPago\Tests\Feature;

use CodeTech\EuPago\Tests\TestCase;

/**
 * A class-based test (instead of a Pest closure) because the config must be
 * set before the service provider boots.
 */
class RoutesDisabledTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('eupago.routes', false);
    }

    public function test_no_callback_routes_are_registered_when_routes_are_disabled(): void
    {
        $this->assertFalse(app('router')->has('eupago.mb.callback'));
        $this->assertFalse(app('router')->has('eupago.mbway.callback'));
        $this->assertFalse(app('router')->has('eupago.payshop.callback'));

        $this->getJson('/eupago/mb/callback')->assertNotFound();
    }
}
