<?php

namespace App\Services;

class Transaction
{
    public function status($status)
    {
        switch ($status) {
            case 'Pending':
                $status = 'Pending';
                $class = 'light-warning';
                break;
            case 'Success':
                $status = 'Success';
                $class = 'light-primary';
                break;
            case 'Failed':
                $status = 'Failed';
                $class = 'light-danger';
                break;
            default:
                $status = 'No Status';
                $class = 'light-secondary';
        }

        return (object)[
            'name'  => $status,
            'class' => $class
        ];
    }

    public function type($type)
    {
        switch ($type) {
            case 'Credit':
                $type = 'Credit';
                $class = 'light-primary';
                $sign = '+';
                $signClass = 'primary';
                break;
            case 'Debit':
                $type = 'Debit';
                $class = 'light-danger';
                $sign = '-';
                $signClass = 'danger';
                break;
            default:
                $type = 'No Type';
                $class = 'light-secondary';
                $sign = '';
                $signClass = 'secondary';
        }

        return (object)[
            'name'      => $type,
            'class'     => $class,
            'sign'      => $sign,
            'signClass'  => $signClass
        ];
    }
}
