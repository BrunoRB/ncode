<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

class Transaction extends JsonResource
{
    public function toArray($request)
    {
        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return [
            'amount' => $moneyFormatter->format($this->amount),
            'to_amount' => $moneyFormatter->format($this->to_amount),
            'from' => $this->from()->pluck('name')->first(),
            'to' => $this->to()->pluck('name')->first(),
            'created_at' => $this->created_at,
            'id' => $this->id
        ];
    }
}
