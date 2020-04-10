<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('brybank:upgrade-currencies');
        $this->call(AccountsTableSeeder::class);
        $this->call(TransactionsTableSeeder::class);
    }
}
