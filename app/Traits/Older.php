<?php

namespace App\Traits;

trait Older
{
    public static function currentExchangeRate($sourceCurrency, $targetCurrency, $sourceAmount)
    {
        try{
            $apikey = 'b3736959599e6bb9b7d5';
            $from_Currency = urlencode($sourceCurrency);
            $to_Currency = urlencode($targetCurrency);
            $query =  "{$from_Currency}_{$to_Currency}";

            // "https://free.currconv.com/api/v7/convert?q=USD_PHP&compact=ultra&apiKey=b3736959599e6bb9b7d5"
            // "https://api.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}"

            // change to the free URL if you're using the free version
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

    public static function ForeignExchangeRatesApi($sourceCurrency, $targetCurrency, $sourceAmount)
    // public static function currentExchangeRate()
    {
        // set API Endpoint, access key, required parameters
        $endpoint = 'convert';
        $access_key = 'rfeLRMWaoPnKUF9gtS2LcsN';

        $from = $sourceCurrency;
        $to = $targetCurrency;
        $amount = $sourceAmount;

        // initialize CURL:
        $ch = curl_init('https://fcsapi.com/api-v3/forex/latest?id=1&access_key='.$access_key.'');
        // $ch = curl_init('https://fcsapi.com/api-v3/forex/converter?pair1='.$from.'&pair2='.$to.'&amount='.$amount.'&access_key='.$access_key.'');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // get the JSON data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $conversionResult = json_decode($json, true);

        // access the conversion result
        // return $conversionResult['result'];
        dd( $conversionResult);
        return null;
    }

    public static function exchangeRatesApi($sourceCurrency, $targetCurrency, $sourceAmount)
    // public static function currentExchangeRate()
    {
        // set API Endpoint, access key, required parameters
        $endpoint = 'convert';
        $access_key = '2a2a0e9cea2678f9e26a7f666b8ce4bc';

        $from = $sourceCurrency;
        $to = $targetCurrency;
        $amount = $sourceAmount;

        // initialize CURL:
        $ch = curl_init('https://api.exchangeratesapi.io/v1/'.$endpoint.'?access_key='.$access_key.'&from='.$from.'&to='.$to.'&amount='.$amount.'');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // get the JSON data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $conversionResult = json_decode($json, true);

        // access the conversion result
        // return $conversionResult['result'];
        dd( $conversionResult);
        return null;
    }
}
