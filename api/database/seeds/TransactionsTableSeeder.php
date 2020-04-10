<?php

use App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Money\Money;
use Money\Currency as MoneyCurrency;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $accounts = Account::take(100)->get()->keyBy('id');
        $accountsIds = $accounts->pluck('id', 'id')->toArray();
        if ($accounts->count() > 1) {
            foreach ($accounts as $id => $account) {
                $copy = $accountsIds;
                unset($copy[$id]);
                $targetAccount = $accounts[array_rand($copy)];

                foreach (range(1, rand(2, 30)) as $_) {
                    $money = new Money(
                        rand(1, $account->balance->getAmount()),
                        new MoneyCurrency($account->currency->iso_code)
                    );
                    Transaction::makeTransaction(
                        $money,
                        $account,
                        $targetAccount,
                        rand(0, 1) ? $faker->text() : null
                    );
                }
            }
        } else {
            factory(Transaction::class, 30)->create();
        }
    }
}
