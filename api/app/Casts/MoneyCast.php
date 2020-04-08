<?php

namespace App\Casts;

use App\Models\Currency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Money\Money;
use Money\Currency as MoneyCurrency;

class MoneyCast implements CastsAttributes
{
    public function get($model, $key, $v, $attributes)
    {
        return new Money(
            $v,
            new MoneyCurrency($model->getIsoCodeForKey($key))
        );
    }

    public function set($model, $key, $v, $attributes)
    {
        return $v instanceof Money ? $v->getAmount() : Currency::toCents($v);
    }
}
