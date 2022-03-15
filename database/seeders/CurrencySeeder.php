<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\User;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $currencies = self::getCurrency();
        foreach ($currencies as $currency) {
            Currency::create([
                'name'      => $currency['name'],
                'symbol'    => $currency['symbol'],
                'code'      => $currency['code'],
            ]);
        }
    }

    public static function getCurrency()
    {
        return [
            ['code' =>'EUR' , 'name' => 'Euro', 'symbol' => '€' ],
            ['code' =>'NGN' , 'name' => 'Naira', 'symbol' => '₦' ],
            ['code' =>'USD' , 'name' => 'US Dollar', 'symbol' => '$' ],
        ];
    }
}
