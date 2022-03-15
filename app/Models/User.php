<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Create uuid when a new user is to be created
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            // Generate unique uuid for a new user.
            $user->uuid = (string) \Illuminate\Support\Str::uuid();

            // Default new user currency to USD.
            $user->currency_id = \App\Models\Currency::where('code', 'USD')->first()->id;

            // Default role to customer.
            // $user->role_id = \App\Models\Role::where('name', 'customer')->first()->id;
        });
    }

    /**
     * Get the full name of the authenticated user.
     */
    public function getFullNameAttribute()
    {
        return ucfirst($this->name);
    }

    /**
     * Get the Role associated with the authenticated user.
     */
    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    /**
     * Get the latest currency transaction of the authenticated user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class)->take(200)->latest('created_at');
    }

    /**
     * Get the latest currency transaction of the authenticated user.
     */
    public function latestCurrencyBalance()
    {
        return $this->hasOne(CurrencyBalance::class)->latest('created_at');
    }

    /**
     * Get the default currecny of the authenticated user.
     */
    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }
}
