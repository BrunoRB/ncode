<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use Tests\TestCase;
use Money\Money;
use Money\Currency as MoneyCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function testMakeTransactionBasic()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'USD'
        ]);
        $accountFrom = factory(Account::class)->create([
            'balance' => '1000',
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'balance' => '0',
            'currency_id' => $currency1->id
        ]);

        $amount = Currency::toCents(rand(5, 500000));
        $money = new Money(
            $amount,
            new MoneyCurrency($currency1->iso_code)
        );

        $t = Transaction::makeTransaction($money, $accountFrom, $accountTo);
        $this->assertNotNull($t);

        $this->assertEquals($amount, $t->amount->getAmount());
        $this->assertEquals($amount, $t->to_amount->getAmount());
    }

    /**
     * If something happens during the transaction creation the whole operation should be aborted
     */
    public function testMakeTransactionShouldBeAtomic()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'USD'
        ]);
        $fromOriginalBalance = '1000';
        $accountFrom = factory(Account::class)->create([
            'balance' => $fromOriginalBalance,
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'balance' => '0',
            'currency_id' => $currency1->id
        ]);

        $money = new Money(
            Currency::toCents($fromOriginalBalance),
            new MoneyCurrency($currency1->iso_code)
        );

        /**
         * A bit hacky, but it works.
         * Basically we are forcing the balance addition to explode.
         */
        $accountToNew = new class($accountTo->toArray()) extends Account {
            protected $fillable = [
                'id',
                'balance',
                'currency_id',
                'name',
                'currency_id'
            ];
            public function save(array $options = [])
            {
                throw new \Exception('fail');
            }
        };

        $failed = false;
        try {
            Transaction::makeTransaction($money, $accountFrom, $accountToNew);
        } catch (\Exception $e) {
            $failed = true;
        }
        $this->assertTrue($failed, 'Should have thrown an exception');

        $this->assertEquals(0, Transaction::count(), 'No transaction should have been created');
        $this->assertEquals(
            Currency::toCents($fromOriginalBalance),
            $accountFrom->fresh()->balance->getAmount(),
            'The acc from balance should have remain unaletered'
        );
        $this->assertEquals(
            0,
            $accountTo->fresh()->balance->getAmount(),
            'The acc to balance should have remain unaletered'
        );
    }
}
