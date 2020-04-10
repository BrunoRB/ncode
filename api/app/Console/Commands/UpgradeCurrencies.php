<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;

class UpgradeCurrencies extends Command
{
    protected $signature = 'brybank:upgrade-currencies';

    protected $description = 'Add currencies (if necessary) and upgrade their exchange rates';


    public function handle()
    {
        /**
         * Imagine this is coming from an external API ;)
         */
        $currencies = collect([
            ['United States dollar', '$', 'USD', 1],
            ['Euro', '€', 'EUR', 1.08],
            ['Brazillian Real', 'R$', 'BRL', 0.25],
            ['Japanese Yen', '¥', 'JPY', 0.50],
            ['Pound sterling', '£', 'GBP', 1.5]
        ]);

        $existing = Currency::whereIn('iso_code', $currencies->pluck(2))
            ->get()
            ->keyBy('iso_code');
        $currencies->each(function ($data) use ($existing) {
            if ($existing->has($data[2])) {
                // only upgrade the exchange rate
                $c = $existing[$data[2]];
                $c->exchange_rate = $data[3];
                $c->save();
            } else {
                // create the currency
                $c = new Currency();
                $c->name = $data[0];
                $c->symbol = $data[1];
                $c->iso_code = $data[2];
                $c->exchange_rate = $data[3];
                $c->save();
            }
        });
    }
}
