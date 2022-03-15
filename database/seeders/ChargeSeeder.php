<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = self::charges();

        foreach ($currencies as $currency) {
            \App\Models\Charge::create([
                'source_currency_id'    => $currency['source_currency_id'],
                'target_currency_id'    => $currency['target_currency_id'],
                'rate'                  => $currency['rate'],
                'variable_percentage'   => $currency['variable_percentage'],
                'fixed_fee'            => $currency['fixed_fee'],
            ]);
        }
    }

    public static function charges()
    {
        return [
            [
                'source_currency_id'    => 1,  // EUR to EUR charge
                'target_currency_id'    => 1,
                'rate'                  => 1,
                'variable_percentage'   => 0,
                'fixed_fee'             => 0.41,
            ],
            [
                'source_currency_id'    => 1, // EUR to NIG charge
                'target_currency_id'    => 2,
                'rate'                  => 424.472,
                'variable_percentage'   => 0.57,
                'fixed_fee'             => 0.71,
            ],
            [
                'source_currency_id'    => 1, // EUR to USD charge
                'target_currency_id'    => 3,
                'rate'                  => 1.09415,
                'variable_percentage'   => 0.41,
                'fixed_fee'             => 0.58,
            ],
            [
                'source_currency_id'    => 2, // NIG to EUR charge
                'target_currency_id'    => 1,
                'rate'                  => 0.00235501,
                'variable_percentage'   => 0.55,
                'fixed_fee'             => 118.56,
            ],
            [
                'source_currency_id'    => 2, // NIG to NIG charge
                'target_currency_id'    => 2,
                'rate'                  => 1,
                'variable_percentage'   => 0,
                'fixed_fee'             => 231.44,
            ],
            [
                'source_currency_id'    => 2, // NIG to USD charge
                'target_currency_id'    => 3,
                'rate'                  => 0.00257732,
                'variable_percentage'   => 0.55,
                'fixed_fee'             => 97.88,
            ],
            [
                'source_currency_id'    => 3, // USD to EUR charge
                'target_currency_id'    => 1,
                'rate'                  => 0.91405,
                'variable_percentage'   => 0.42,
                'fixed_fee'             => 4.67,
            ],
            [
                'source_currency_id'    => 3, // USD to NIG charge
                'target_currency_id'    => 2,
                'rate'                  => 388,
                'variable_percentage'   => 0.59,
                'fixed_fee'             => 5.01,
            ],
            [
                'source_currency_id'    => 3, // USD to USD charge
                'target_currency_id'    => 3,
                'rate'                  => 1,
                'variable_percentage'   => 0,
                'fixed_fee'             => 4.86,
            ],
        ];
    }
}
