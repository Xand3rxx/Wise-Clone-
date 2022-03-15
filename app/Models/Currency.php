<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\Currency as CurrencyService;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    // public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the type of currency to be rendered
     */
    public function flag()
    {
        return (new CurrencyService)->flag($this->flag);
    }
}
