<?php

namespace App\Traits;

trait CanPay
{
    /**
     * Determine and verify if transactions can be made
     * Return true if amount is greater than the available currency balance
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

        return(($amount > $latestCurrencyBalance[$code] || $latestCurrencyBalance[$code] == 0.0) ? true : false);
    }
}
