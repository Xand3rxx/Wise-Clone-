<?php

namespace App\Services;

class Currency
{
    public function flag($flag)
    {
        // data-kt-flag="{{ $currency->flag()->url }}" 

        switch ($flag) {
            case '3':
                $flag = asset('assets/media/flasgs/united-states.svg');
                break;
            case '1':
                $flag = asset('assets/media/flasgs/european-union.svg');
                break;
            case '2':
                $flag = asset('assets/media/flasgs/nigeria.svg');
                break;
            default:
                $flag = asset('assets/media/flasgs/uk.svg');
        }

        return (object)[
            'url'  => $flag
        ];
    }
}
