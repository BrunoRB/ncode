<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions_from()
    {
        return $this->hasMany(Transaction::class, 'from_id');
    }

    public function transactions_to()
    {
        return $this->hasMany(Transaction::class, 'to_id');
    }

    public function all_transactions()
    {
        return Transaction::where('from_id', $this->id)
            ->orWhere('to_id', $this->id);
    }
}
