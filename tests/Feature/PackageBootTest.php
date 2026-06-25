<?php

it('merges the eupago config when the package boots', function () {
    expect(config('eupago.env'))->toBe('test')
        ->and(config('eupago.channel'))->toBe('demo');
});

it('registers the eupago callback routes', function () {
    expect(app('router')->has('eupago.mb.callback'))->toBeTrue()
        ->and(app('router')->has('eupago.mbway.callback'))->toBeTrue();
});
