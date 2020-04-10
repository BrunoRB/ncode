<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

class Account extends JsonResource
{
    public function toArray($request)
    {
        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'balance' => $moneyFormatter->format($this->balance),
            'currency' => $this->balance->getCurrency()->getCode()
        ];
    }
}
