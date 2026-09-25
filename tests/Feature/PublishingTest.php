<?php

use CodeTech\EuPago\Providers\EuPagoServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

it('publishes each group under a package-prefixed tag and its legacy tag', function (string $prefixed, string $legacy) {
    $paths = ServiceProvider::pathsToPublish(EuPagoServiceProvider::class, $prefixed);

    expect($paths)->not->toBeEmpty()
        ->and(ServiceProvider::pathsToPublish(EuPagoServiceProvider::class, $legacy))->toBe($paths);
})->with([
    ['eupago-config', 'config'],
    ['eupago-migrations', 'migrations'],
    ['eupago-translations', 'translations'],
]);

it('publishes the translations into the app lang path', function () {
    $paths = ServiceProvider::pathsToPublish(EuPagoServiceProvider::class, 'eupago-translations');

    expect(array_values($paths))->toBe([lang_path('vendor/eupago')]);
});

it('keeps every index name within the MySQL 64-character limit', function () {
    $names = collect(DB::select("select name from sqlite_master where type = 'index' and name not like 'sqlite_%'"))
        ->pluck('name');

    expect($names)->not->toBeEmpty()
        ->and($names->filter(fn (string $name) => strlen($name) > 64)->all())->toBe([]);
});
