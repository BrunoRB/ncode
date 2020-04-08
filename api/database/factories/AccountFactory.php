<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Account;
use App\Models\Currency;
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

$factory->define(Account::class, function (Faker $faker, array $data = []) {
    if (!isset($data['currency_id'])) {
        $data['currency_id'] = factory(Currency::class)->create()->id;
    }

    return [
        'name' => $faker->name,
        'balance' => rand(100, 90000) * 1000
    ] + $data;
});
