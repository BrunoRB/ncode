<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Money\Converter;
use Money\Money;
use Money\Currency as MoneyCurrency;
use Money\Exchange\FixedExchange;
use Money\Currencies\ISOCurrencies;
use Money\Exchange\ReversedCurrenciesExchange;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    /**
     * Our exchange model considers that everything is indexed by the US dollar.
     */
    const INDEX_CURRENCY = 'USD';

    protected $fillable = [
        'name', 'iso_code', 'symbol', 'exchange_rate'
    ];

    public static function toCents(string $money): string
    {
        if (preg_match('/^0+$/', $money)) {
            return '0';
        } elseif (preg_match('/^([1-9]\d*)(?:\.(\d{1,2}))?$/', $money, $matches)) {
            if (count($matches) == 2) {
                return $matches[1] . '00';
            } elseif (strlen($matches[2]) === 2) {
                return $matches[1] . $matches[2];
            } else {
                return $matches[1] . $matches[2] . '0';
            }
        } else {
            throw new \Exception("Invalid");
        }
    }

    public static function fromCents($money): float
    {
        return $money / 100;
    }

    /**
     * Given a $transferAmount, convert it to a value in $to currency
     */
    public function convertAmountTo(Money $transferAmount, Currency $to): Money
    {
        $from = $this;
        if ($this->id === $to->id) {
            // identity
            return $transferAmount;
        } else {
            $isoCurrencies = new ISOCurrencies();

            // convert from our currency to USD
            $exchange = new FixedExchange([
                $from->iso_code => [
                    self::INDEX_CURRENCY => $from->exchange_rate
                ]
            ]);

            $converter = new Converter($isoCurrencies, $exchange);
            $usd = $converter->convert($transferAmount, new MoneyCurrency(self::INDEX_CURRENCY));

            // them from USD we convert to the target currency
            $targetExchange = new ReversedCurrenciesExchange(new FixedExchange([
                $to->iso_code => [
                    self::INDEX_CURRENCY => $to->exchange_rate
                ]
            ]));
            $targetConverter = new Converter($isoCurrencies, $targetExchange);
            $target = $targetConverter->convert($usd, new MoneyCurrency($to->iso_code));

            return $target;
        }
    }
}
