<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the authenticated user dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get authenticated user information.
        $user = auth()->user();

        return view('application.index', [
            'transactions'      => ($user->role_id == 2) ? $user['transactions'] : Transaction::take(500)->latest()->get(),
            'user'              => $user,
        ]);
    }


    /**
     * Refund dollar account
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function fundAccount()
    {
        // Default currency ID
        (int) $currency = \App\Models\Currency::where('code', 'USD')->first()->id;

        // Default amount
        (int) $amount = 1000;

        // Last currency balance
        $latestCurrencyBalance = auth()->user()->latestCurrencyBalance;

        // Credit a new user with $1000.
        $transaction = Transaction::create([
            'user_id'               => auth()->id(),
            'recipient_id'          => 1,
            'source_currency_id'    => $currency,
            'target_currency_id'    => $currency,
            'amount'                => $amount,
            'rate'                  => 0.0,
            'transfer_fee'          => 4.86,
            'variable_fee'          => 0,
            'fixed_fee'             => 4.86,
            'type'                  => Transaction::TYPE['Credit'],
            'status'                => Transaction::STATUS['Success'],
        ]);

        \App\Models\CurrencyBalance::create([
            'user_id'           => auth()->id(),
            'transaction_id'    => $transaction['id'],
            'USD'               => $amount,
            'EUR'               => $latestCurrencyBalance->EUR,
            'NGN'               => $latestCurrencyBalance->NGN,
        ]);

        return redirect()->route('home')->with('success', 'Your dollar account has been credited with $1,000');
    }
}
