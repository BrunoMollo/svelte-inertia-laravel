<?php

use Illuminate\Support\Facades\Artisan;

test('database seeder runs fresh without errors', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');

    expect(true)->toBeTrue(); // si llega acá, pasó
});
