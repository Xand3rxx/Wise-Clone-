<?php

namespace App\Models;

use App\Models\CurrencyBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Transaction as TransactionService;

class Transaction extends Model
{
    use SoftDeletes;

    const STATUS = [
        'Success'   => 'Success',
        'Pending'   => 'Pending',
        'Failed' => 'Failed',
    ];

    const TYPE = [
        'Debit'   => 'Debit',
        'Credit'  => 'Credit',
    ];

    protected $guarded = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'meta_data' => 'array',
    ];

    /**
     * Model event to trigger action on creating
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            // Generate unique uuid for a new transaction.
            $transaction->uuid = (string) \Illuminate\Support\Str::uuid();

            // Generate a random unique transaction reference.
            $transaction->reference = \App\Traits\GenerateUniqueIdentity::generateReference('transactions');
        });
    }

    /**
     * Store the newly created debit transaction
     *
     * @param  array  $validated
     * @param  float  $amount
     *
     * @return \App\Model\Transaction|Null
     */
    public static function firstEntryRecord(array $validated, float $amount)
    {
        // Set `transactionCreated` to false before DB transaction
        (bool) $transactionCreated = false;

        DB::transaction(function () use ($validated, $amount, &$transactionCreated) {

            $currencyBalance = auth()->user()->latestCurrencyBalance;
            (int) $sourceCurrency = $validated['source_currency_id'];

            $transaction = Transaction::create([
                'user_id'               => $validated['user_id'],
                'recipient_id'          => $validated['recipient_id'],
                'source_currency_id'    => $validated['source_currency_id'],
                'target_currency_id'    => $validated['target_currency_id'],
                'amount'                => self::removeComma($amount),
                'rate'                  => $validated['rate'],
                'transfer_fee'          => $validated['transferFee'],
                'variable_fee'          => $validated['variableFee'],
                'fixed_fee'             => $validated['fixedFee'],
                'type'                  => self::TYPE[$validated['type']],
                'status'                => self::STATUS['Success'],
                'meta_data'             => $validated,
            ]);

            CurrencyBalance::create([
                'user_id'           => $validated['user_id'],
                'transaction_id'    => $transaction['id'],
                'USD'               => ($sourceCurrency == 3) ? ($currencyBalance->USD - $amount) : $currencyBalance->USD,
                'EUR'               => ($sourceCurrency == 1) ? ($currencyBalance->EUR - $amount) : $currencyBalance->EUR,
                'NGN'               => ($sourceCurrency == 2) ? ($currencyBalance->NGN - $amount) : $currencyBalance->NGN,
            ]);

            $transactionCreated = true;

        }, 3);

        return $transactionCreated;
    }

    /**
     * Store the newly created credit transaction
     *
     * @param  array  $validated
     * @param  float  $amount
     *
     * @return \App\Model\Transaction|Null
     */
    public static function secondEntryRecord(array $validated, float $amount)
    {
        // Set `transactionCreated` to false before DB transaction
        (bool)$transactionCreated = false;

        DB::transaction(function () use ($validated, $amount, &$transactionCreated) {
            $currencyBalance = User::where('id', $validated['recipient_id'])->firstOrFail()->latestCurrencyBalance;
            (int) $targetCurrency = $validated['target_currency_id'];

            $transaction = Transaction::create([
                'user_id'               => $validated['recipient_id'],
                'recipient_id'          => $validated['user_id'],
                'source_currency_id'    => $validated['source_currency_id'],
                'target_currency_id'    => $validated['target_currency_id'],
                'amount'                => self::removeComma($amount),
                'rate'                  => $validated['rate'],
                'transfer_fee'          => $validated['transferFee'],
                'variable_fee'          => $validated['variableFee'],
                'fixed_fee'             => $validated['fixedFee'],
                'type'                  => self::TYPE[$validated['type']],
                'status'                => self::STATUS['Success'],
                'meta_data'             => $validated,
            ]);

            CurrencyBalance::create([
                'user_id'           => $validated['recipient_id'],
                'transaction_id'    => $transaction['id'],
                'USD'               => ($targetCurrency == 3) ? ($currencyBalance->USD + $amount) : $currencyBalance->USD,
                'EUR'               => ($targetCurrency == 1) ? ($currencyBalance->EUR + $amount) : $currencyBalance->EUR,
                'NGN'               => ($targetCurrency == 2) ? ($currencyBalance->NGN + $amount) : $currencyBalance->NGN,
            ]);

            $transactionCreated = true;

        }, 3);

        return $transactionCreated;
    }

    /**
     * Record a failed transaction
     *
     * @param  array  $validated
     * @param  float  $amount
     * @param  string  $type
     * @param  int  $user_id
     * @param  int  $recipient_id
     *
     * @return \App\Model\Transaction|Null
     */
    public static function failedTransaction(array $validated, float $amount, string $type, int $user_id, int $recipient_id)
    {
        Transaction::create([
            'user_id'               => $user_id,
            'recipient_id'          => $recipient_id,
            'source_currency_id'    => $validated['source_currency_id'],
            'target_currency_id'    => $validated['target_currency_id'],
            'amount'                => self::removeComma($amount),
            'rate'                  => $validated['rate'],
            'transfer_fee'          => $validated['transferFee'],
            'variable_fee'          => $validated['variableFee'],
            'fixed_fee'             => $validated['fixedFee'],
            'type'                  => $type,
            'status'                => self::STATUS['Failed'],
            'meta_data'             => $validated,
        ]);
    }

    /**
     * Remove comma from number format without removing decimal point
     */
    public static function removeComma($value)
    {
        return floatval(preg_replace('/[^\d.]/', '', $value));
    }

    /**
     * Format the amount value
     */
    public function amount()
    {
        return number_format($this->amount, 2);
    }

    /**
     * Format the opening balance value
     */
    public function openingBalance()
    {
        return number_format($this->opening_balance);
    }

    /**
     * Format the closing balance value
     */
    public function closingBalance()
    {
        return number_format($this->closing_balance);
    }

    /**
     * Get the status of of a single transaction
     */
    public function status()
    {
        return (new TransactionService)->status($this->status);
    }

    /**
     * Get the type of transaction executed
     */
    public function type()
    {
        return (new TransactionService)->type($this->type);
    }

    /**
     * Get the sender associated with the transaction
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Get the recipient associated with the transaction
     */
    public function recipient()
    {
        return $this->hasOne(User::class, 'id', 'recipient_id');
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

    /**
     * Get the sequivalent currency transaction
     */
    public function CurrencyBalance()
    {
        return $this->hasOne(CurrencyBalance::class, 'transaction_id');
    }

     /**
     * Scope a query to get the transactions associated with the authenticated user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTransactions($query, $id)
    {
        return $query->select('*')
            ->where('user_id', $id)
             ->orWhere('recipient_id', $id);
    }
}
