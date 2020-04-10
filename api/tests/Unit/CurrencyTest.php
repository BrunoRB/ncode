<?php

namespace Tests\Unit;

use App\Models\Currency;
use Tests\TestCase;
use Money\Money;
use Money\Currency as MoneyCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function testConvertAmountToItself()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 2,
            'iso_code' => 'EUR'
        ]);

        $money = new Money(
            Currency::toCents(rand(5, 500000)),
            new MoneyCurrency($currency1->iso_code)
        );

        $convertedMoney = $currency1->convertAmountTo($money, $currency1);
        $this->assertEquals($money->getAmount(), $convertedMoney->getAmount());
    }

    public function testConvertAmountToBetweenDifferentExchangeRates()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 2,
            'iso_code' => 'EUR'
        ]);
        $currency2 = factory(Currency::class)->create([
            'exchange_rate' => 4,
            'iso_code' => 'BRL'
        ]);

        $originalValue = Currency::toCents(rand(5, 500000));

        $money = new Money(
            $originalValue,
            new MoneyCurrency($currency1->iso_code)
        );

        // should have half the value in the new coin
        $convertedMoney = $currency1->convertAmountTo($money, $currency2);
        // we are ok with 1 cent of discrepancy on currency conversions
        $this->assertEqualsWithDelta(floor($money->getAmount() / 2), $convertedMoney->getAmount(), 1);

        // now reverse the process, we should get the original value back
        $invertedConvertedMoney = $currency2->convertAmountTo($convertedMoney, $currency1);
        $this->assertEqualsWithDelta(floor($originalValue), $invertedConvertedMoney->getAmount(), 1);
    }
}
