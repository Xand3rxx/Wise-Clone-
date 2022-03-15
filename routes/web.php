<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'login'    => true,
    'register' => true,
    'logout'   => true,
    'reset'    => false,  // For resetting passwords
    'confirm'  => false,  // For additional password confirmations
    'verify'   => false,  // For email verification
]);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('fund-account', [App\Http\Controllers\HomeController::class, 'fundAccount'])->name('fund_account');
    
    Route::post('transaction/source-converter',  [TransactionController::class, 'sourceConverter'])->name('transaction.source_converter');
    Route::post('transaction/currency-balance',  [TransactionController::class, 'currencyBalance'])->name('transaction.currency_balance');
    Route::resource('transaction',  TransactionController::class);
});
