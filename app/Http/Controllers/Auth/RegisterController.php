<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Default currency ID
        (int) $currency = \App\Models\Currency::where('code', 'USD')->first()->id;

        // Default amount
        (int) $amount = 1000;

        $user = User::create([
            'role_id'   => \App\Models\Role::where('name', 'customer')->first()->id,
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
        ]);

        // Credit a new user with $1000.
        $transaction = Transaction::create([
            'user_id'             => 1,
            'recipient_id'          => $user->id,
            'source_currency_id'    => $currency,
            'target_currency_id'    => $currency,
            'amount'                => $amount,
            'target_currency_id'    => $currency,
            'rate'                  => 0.0,
            'type'                  => Transaction::TYPE['Credit'],
            'status'                => Transaction::STATUS['Success'],
            'opening_balance'       => $amount,
            'closing_balance'       => $amount,
        ]);

        \App\Models\CurrencyBalance::create([
            'transaction_id'    => $transaction['id'],
            'USD'               => $amount,
            'EUR'               => 0,
            'NGN'               => 0,
        ]);

        return $user;
    }
}
