<?php

namespace Tests\Feature;

use App\Models\Currency;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Money\Currencies\ISOCurrencies;

class CurrencyTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testList()
    {
        foreach ((new ISOCurrencies()) as $i => $currency) {
            factory(Currency::class)->create(['iso_code' => $currency]);
            if ($i > 2) {
                break;
            }
        }

        $response = $this->json('GET', "/api/currencies");
        $this->assertEquals(200, $response->status(), substr($response->getContent(), 0, 250));
        $res = json_decode($response->getContent(), true);
        $this->assertEquals(count($res), Currency::count());
        $this->assertArrayHasKey('name', $res[0]);
        $this->assertArrayHasKey('iso_code', $res[0]);
        $this->assertArrayHasKey('symbol', $res[0]);
        $this->assertArrayHasKey('exchange_rate', $res[0]);
    }
}
