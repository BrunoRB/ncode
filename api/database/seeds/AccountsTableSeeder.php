<?php

use App\Models\Account;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 10) as $_) {
            $currencyId = Currency::take(100)->inRandomOrder()->pluck('id')->first();
            factory(Account::class)->create([
                'currency_id' => $currencyId ?: null
            ]);
        }
    }
}
