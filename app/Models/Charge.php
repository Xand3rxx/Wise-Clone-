<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    /**
     * Model event to trigger action on creating
     */
    protected static function booted()
    {
        static::creating(function ($charge) {
            // Generate unique uuid for a new charge.
            $charge->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Get the sender associated with the transaction
     */
    public function sourceCurrency()
    {
        return $this->hasOne(Currency::class, 'id', 'source_currency_id');
    }

    /**
     * Get the recipient associated with the transaction
     */
    public function targetCurrency()
    {
        return $this->hasOne(Currency::class, 'id', 'target_currency_id');
    }
}
