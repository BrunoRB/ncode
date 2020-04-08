<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Money;
use Illuminate\Support\Facades\DB;

/**
 * Class Transaction
 *
 *
 * @property Account $from account that originated the transaction
 * @property Account $to account to where moeny will be moved
 *
 * @property string $amount amount of money (with the origin account currency) that was/will be transafered
 * @property string $to_amount amount of money (with the target account currency) that was/will be transafered.
 *  This exists because the exchange rates change with time, and we want historical record of how much was the value
 *  moved at the transaction time.
 *
 * @property string $fromCurrency origin currency. In case the account change its currency, we keep this as historical data.
 * @property string $toCurrency target currency. In case the account change its currency, we keep this as historical data.
 */
class Transaction extends Model implements FetchIsoCodeInterface
{
    use SoftDeletes;

    protected $casts = [
        'amount' => MoneyCast::class,
        'to_amount' => MoneyCast::class,
    ];

    public function getIsoCodeForKey($key): string
    {
        return $key === 'amount' ? $this->fromCurrency->iso_code : $this->fromCurrency->iso_code;
    }

    public function from()
    {
        return $this->belongsTo(Account::class);
    }

    public function to()
    {
        return $this->belongsTo(Account::class);
    }

    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }
}
