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
        // Last currency balance
        $latestCurrencyBalance = auth()->user()->latestCurrencyBalance;

        // If the current dollar currency balance is not equal to zero back
        if($latestCurrencyBalance->USD != 0){
            return back();
        }

        // Default currency ID
        (int) $currency = \App\Models\Currency::where('code', 'USD')->first()->id;

        // Default amount
        (int) $amount = 1000;

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

        return redirect()->route('transaction.create')->with('success', 'Your dollar account has been credited with $1,000');
    }
}
