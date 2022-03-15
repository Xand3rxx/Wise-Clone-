<?php

namespace App\Traits;


trait ExchangeRate
{
    /**
     * Get current exchange using `https://www.currencyconverterapi.com/`
     * @param string $sourceCurrency
     * @param string $targetCurrency
     * @param float $sourceAmount
     *
     * @return float|NULL
     */
    public static function currentExchangeRate($sourceCurrency, $targetCurrency, $sourceAmount)
    {
        try{
            // Set URL payload paramaters
            $apikey = 'b3736959599e6bb9b7d5';
            $from_Currency = urlencode($sourceCurrency);
            $to_Currency = urlencode($targetCurrency);
            $query =  "{$from_Currency}_{$to_Currency}";

            // API URL
            $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");

            $obj = json_decode($json, true);
            $val = floatval($obj["$query"]);
            // $total = $val * $sourceAmount;
            // return number_format($total, 2, '.', '');
            return number_format($val, 2, '.', '');
        }catch(\Exception $ex)
        {
            return null;
        }
    }

    /**
     * Determine and verify if transactions can be made
     * Return true if amount is greater than the available currency balance
     * Return true if amount is equal to zero
     * Return true if available currency balance is equal to zero
     *
     * @param string $code
     * @param float $amount
     *
     * @return bool true|false
     */
    public function canMakePayment($code, $amount)
    {
        $latestCurrencyBalance = auth()->user()->latestCurrencyBalance;

        return(($amount > $latestCurrencyBalance[$code] || $amount == 0.0 || $latestCurrencyBalance[$code] == 0.0) ? true : false);
    }
}
