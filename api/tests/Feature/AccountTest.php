<?php

namespace Tests\Feature;

use Tests\TestCase;
use \App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testCannotTransferMoneyFromSelf()
    {
        $accountFrom = factory(Account::class)->create([
            'balance' => Currency::toCents(100)
        ]);

        $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
            'to' => $accountFrom->id,
            'amount' => (string) ($accountFrom->balance->getAmount() - 1)
        ]);
        $this->assertEquals(400, $response->status(), substr($response->getContent(), 0, 250));
    }

    public function testCannotMakeTransactionIfNotEnoughBalance()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'BRL'
        ]);
        $currency2 = factory(Currency::class)->create([
            'exchange_rate' => 0.5,
            'iso_code' => 'EUR'
        ]);
        $accountFrom = factory(Account::class)->create([
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'currency_id' => $currency2->id
        ]);

        foreach ([1, 100, 1000, 10000] as $i) {
            $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
                'to' => $accountTo->id,
                'amount' => (string) ($accountFrom->balance->getAmount() + $i)
            ]);
            $this->assertEquals(400, $response->status(), substr($response->getContent(), 0, 250));
        }
    }

    public function testCanMakeTransactionIfHasEnoughBalance()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'BRL'
        ]);
        $currency2 = factory(Currency::class)->create([
            'exchange_rate' => 0.5,
            'iso_code' => 'EUR'
        ]);
        foreach ([0, 1, 100, 1000, 10000] as $i) {
            $accountFrom = factory(Account::class)->create([
                'currency_id' => $currency1->id
            ]);
            $accountTo = factory(Account::class)->create([
                'currency_id' => $currency2->id
            ]);
            $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
                'to' => $accountTo->id,
                'amount' => (string) (Currency::fromCents($accountFrom->balance->getAmount()) - $i)
            ]);
            $this->assertEquals(201, $response->status());
        }
    }

    public function testLeadingZeroesCaseOne()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'BRL'
        ]);
        $accountFrom = factory(Account::class)->create([
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'currency_id' => $currency1->id
        ]);
        $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
            'to' => $accountTo->id,
            'amount' => '0.11'
        ]);
        $this->assertEquals(201, $response->status(), substr($response->getContent(), 0, 250));
    }

    public function testInvalidAmounts()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'BRL'
        ]);
        $accountFrom = factory(Account::class)->create([
            'currency_id' => $currency1->id,
            'balance' => '300000.00'
        ]);
        $accountTo = factory(Account::class)->create([
            'currency_id' => $currency1->id
        ]);
        foreach(['0', '0.00', '-1', '-10.00', '$100.00', '$50', 'qqqqqqqqq'] as $invalidAmount)  {
            $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
                'to' => $accountTo->id,
                'amount' => $invalidAmount
            ]);
            $this->assertEquals(422, $response->status(), substr($response->getContent(), 0, 250));
        }
    }

    /**
     * Valid transaction using same currency
     */
    public function testValidTransactionShouldChangeBalanceOfAccountsOne()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1.5,
            'iso_code' => 'KMF'
        ]);

        $accountFrom = factory(Account::class)->create([
            'balance' => '500.00',
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'balance' => '100.00',
            'currency_id' => $currency1->id
        ]);

        $amount = '255';

        $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
            'to' => $accountTo->id,
            'amount' => $amount
        ]);

        $this->assertEquals(201, $response->status(), substr($response->getContent(), 0, 250));
        $data = json_decode($response->getContent(), true)['data'];

        $accountFrom->refresh();
        $accountTo->refresh();

        $this->assertEquals('24500', $accountFrom->balance->getAmount());
        $this->assertEquals('35500', $accountTo->balance->getAmount());

        $this->assertEquals('KMF25,500', $data['amount']);
        $this->assertEquals('KMF25,500', $data['to_amount']);
        $this->assertEquals($accountFrom->name, $data['from']);
        $this->assertEquals($accountTo->name, $data['to']);
    }

    /**
     * Transfering to account with currency that has half value of the origin one.
     */
    public function testValidTransactionShouldChangeBalanceOfAccountsTwo()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'BRL'
        ]);
        $currency2 = factory(Currency::class)->create([
            'exchange_rate' => 0.5,
            'iso_code' => 'EUR'
        ]);
        $accountFrom = factory(Account::class)->create([
            'balance' => '10000.56',
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'balance' => '3389360.25',
            'currency_id' => $currency2->id
        ]);

        $amount = '5555.11';

        $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
            'to' => $accountTo->id,
            'amount' => $amount
        ]);

        $this->assertEquals(201, $response->status(), substr($response->getContent(), 0, 250));
        $data = json_decode($response->getContent(), true)['data'];

        $accountFrom->refresh();
        $accountTo->refresh();

        $this->assertEquals(444545, $accountFrom->balance->getAmount());
        $this->assertEquals(340047047, $accountTo->balance->getAmount());

        $this->assertEquals('R$5,555.11', $data['amount']);
        $this->assertEquals('€11,110.22', $data['to_amount']);
        $this->assertEquals($accountFrom->name, $data['from']);
        $this->assertEquals($accountTo->name, $data['to']);
    }

    /**
     * Target currency has double value of the origin
     */
    public function testValidTransactionShouldChangeBalanceOfAccountsThree()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 1,
            'iso_code' => 'USD'
        ]);
        $currency2 = factory(Currency::class)->create([
            'exchange_rate' => 2,
            'iso_code' => 'ILS'
        ]);

        $accountFrom = factory(Account::class)->create([
            'balance' => '8456734.55',
            'currency_id' => $currency1->id
        ]);
        $accountTo = factory(Account::class)->create([
            'balance' => '9128231.61',
            'currency_id' => $currency2->id
        ]);

        $amount = '9922.38';

        $response = $this->json('POST', "/api/accounts/{$accountFrom->id}/transactions", [
            'to' => $accountTo->id,
            'amount' => $amount
        ]);

        $this->assertEquals(201, $response->status(), substr($response->getContent(), 0, 250));
        $data = json_decode($response->getContent(), true)['data'];

        $accountFrom->refresh();
        $accountTo->refresh();
        $this->assertEquals('844681217', $accountFrom->balance->getAmount());
        $this->assertEquals('913319280', $accountTo->balance->getAmount());

        $this->assertEquals('$9,922.38', $data['amount']);
        $this->assertEquals('₪4,961.19', $data['to_amount']);
        $this->assertEquals($accountFrom->name, $data['from']);
        $this->assertEquals($accountTo->name, $data['to']);
    }

    public function testListTransactions()
    {
        $currency1 = factory(Currency::class)->create([
            'exchange_rate' => 2,
            'iso_code' => 'USD'
        ]);
        $currency2 = factory(Currency::class)->create([
            'exchange_rate' => 2,
            'iso_code' => 'BRL'
        ]);

        $accountFrom = factory(Account::class)->create([
            'currency_id' => $currency1->id,
            'balance' => '500000'
        ]);
        $accountTo = factory(Account::class)->create([
            'currency_id' => $currency2->id
        ]);

        factory(Transaction::class, 21)->create([
            'from_id' => $accountFrom->id,
            'to_id' => $accountTo->id,
            'from_currency_id' => $accountFrom->currency->id,
            'to_currency_id' => $accountTo->currency->id,
        ]);

        $response = $this->json('GET', "/api/accounts/{$accountFrom->id}/transactions");
        $this->assertEquals(200, $response->status(), substr($response->getContent(), 0, 250));
        $res = json_decode($response->getContent(), true);
        $this->assertEquals(20, count($res['data']));
        $this->assertEquals(21, $res['meta']['total']);

        $response = $this->json('GET', "/api/accounts/{$accountFrom->id}/transactions?page=2");
        $this->assertEquals(200, $response->status(), substr($response->getContent(), 0, 250));
        $res = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($res['data']));
    }

    public function testFind()
    {
        $account = factory(Account::class)->create();
        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $this->assertEquals(200, $response->status(), substr($response->getContent(), 0, 250));
        $res = json_decode($response->getContent(), true);
    }
}
