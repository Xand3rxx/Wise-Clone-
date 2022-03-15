<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;
use App\Models\CurrencyBalance;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default currency ID
        (int) $currency = \App\Models\Currency::where('code', 'USD')->first()->id;

        // Default amount
        (float) $sourceAmount = 1000.51; // Exact amount deducted from the source with exchange rate applied
        (float) $targetAmount = 1000; // Exact amount recieved from source with exchange rate applied



        /**
         * Following the principle of double-entry accounting.
         * This seeder will default the user_id as the receipeint to have a credit balance.
         * It will waive the debit entry for the wise(clone)default account.
         */
        foreach (\App\Models\User::where('role_id', 2)->get() as $user) {
            $transaction = Transaction::create([
                'user_id'               => $user['id'],
                'recipient_id'          => 1,
                'source_currency_id'    => $currency,
                'target_currency_id'    => $currency,
                'amount'                => $targetAmount,
                'rate'                  => 1,
                'transfer_fee'          => 4.86,
                'variable_fee'          => 0,
                'fixed_fee'             => 4.86,
                'type'                  => Transaction::TYPE['Credit'],
                'status'                => Transaction::STATUS['Success'],
            ]);

            CurrencyBalance::create([
                'user_id'           => $user['id'],
                'transaction_id'    => $transaction['id'],
                'USD'               => $targetAmount,
                'EUR'               => 0,
                'NGN'               => 0,
            ]);
        }

        // Double-entry record for client with id 2 and 5
        // $transaction = Transaction::create([
        //     'user_id'               => 2,
        //     'recipient_id'          => 5,
        //     'source_currency_id'    => $currency,
        //     'target_currency_id'    => $currency,
        //     'amount'                => 200.51,
        //     'rate'                  => 1,
        //     'transfer_fee'          => 4.86,
        //     'variable_fee'          => 0,
        //     'fixed_fee'             => 4.86,
        //     'type'                  => Transaction::TYPE['Debit'],
        //     'status'                => Transaction::STATUS['Success'],
        //     'created_at'            => now()->addMinutes(1),
        // ]);

        // $transaction = Transaction::create([
        //     'user_id'               => 5,
        //     'recipient_id'          => 2,
        //     'source_currency_id'    => $currency,
        //     'target_currency_id'    => $currency,
        //     'amount'                => 200,
        //     'rate'                  => 1,
        //     'transfer_fee'          => 4.86,
        //     'variable_fee'          => 0,
        //     'fixed_fee'             => 4.86,
        //     'type'                  => Transaction::TYPE['Credit'],
        //     'status'                => Transaction::STATUS['Success'],
        //     'created_at'            => now()->addMinutes(1),
        // ]);

        // CurrencyBalance::create([
        //     'user_id'           => 2,
        //     'transaction_id'    => $transaction['id'],
        //     'USD'               => 799.49,
        //     'EUR'               => 0,
        //     'NGN'               => 0,
        //     'created_at'        => now()->addMinutes(1),
        // ]);

        // CurrencyBalance::create([
        //     'user_id'           => 5,
        //     'transaction_id'    => $transaction['id'],
        //     'USD'               => 1200,
        //     'EUR'               => 0,
        //     'NGN'               => 0,
        //     'created_at'        => now()->addMinutes(1),
        // ]);
    }
}
