<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public static function toCents($money): int
    {
        return (int) ($money * 100);
    }

    public static function fromCents($money): float
    {
        return $money / 100;
    }
}
