<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Transaction;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'details' => rand(0, 1) ? $faker->text() : null,
        'amount' => rand(1, 99999),
        'to_amount' => rand(1, 99999),
    ];
});
