<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyBalance extends Model
{
    use HasFactory;

    protected $guarded = ['created_at', 'updated_at'];

    /**
     * Get the sender associated with the currency transaction
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }


    /**
     * Get the sender associated transaction
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
